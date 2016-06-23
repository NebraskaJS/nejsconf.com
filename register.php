---
layout: page
---

{% raw %}
<?php
  define('VALID_ENTRY_POINT', true);
  require 'vendor/autoload.php';
  require 'util.php';
  $config = require 'config.php';

  use SparkPost\SparkPost;
	use Ivory\HttpAdapter\CurlHttpAdapter;

  $mysql = new mysqli($config['mysql']['hostname'], $config['mysql']['username'], $config['mysql']['password'], $config['mysql']['database']);
 
  if($mysql->connect_errno > 0) {
    // TODO: More graceful death.
    die('Unable to connect to database [' . $mysql->connect_error . ']');
  }

  $query = $mysql->query('SELECT COUNT(*) FROM `tickets`');
  $result = $query->fetch_row();

  $tickets_already_sold = $result[0];

  if(! $_POST && $tickets_already_sold >= $config['checkout']['tickets_available']) {
    // Sold out!
?>
<div class="content skinny-content" id="post" data-role="main">
  <h1>Sold Out!</h1>
  <p>
    We seem to have sold out of tickets for this round. Please watch our Twitter for announcements!
  </p>
  <p>
    If you have questions, please don't hesitate to contact us at <a href="mailto:tickets@nejsconf.com" style="color: #FFF; text-shadow: none;">tickets@nejsconf.com</a>
  </p>
</div>
<?php
  }
  else {
    \Stripe\Stripe::setApiKey($config['stripe']['secret_key']);

    $available_tickets = min($config['checkout']['tickets_available'] - $tickets_already_sold, $config['checkout']['max_tickets']);
    $ticket_price = $config['checkout']['ticket_price'];
    $number_of_tickets = 1;
    $coupon_code = '';
    $form_errors = array();
    $attendee_data = array();
    $stripe_error = false;
    $receipt_email = '';
    $show_purchase_success = false;

    if($_POST) {
      $number_of_tickets = min(intval($_POST['number_of_tickets']), $available_tickets);

      // Validate Coupon Code
      if(($coupon_code = arr_get($_POST, 'coupon_code')) != null) {
        $coupon_price = arr_get($config['checkout']['coupons'], $coupon_code);
        if(null == $coupon_price) {
          $coupon_code = null;
        }
        else {
          $ticket_price = $coupon_price;
        }
      }

      // Attendee validation
      for($i = 1; $i <= $number_of_tickets; $i++) {
        $attendee = array(
          'first_name' => trim(arr_get($_POST, 'first_name_' . $i, '')),
          'last_name'  => trim(arr_get($_POST, 'last_name_' . $i, '')),
          'email'      => trim(arr_get($_POST, 'email_' . $i, '')),
          'twitter'    => trim(arr_get($_POST, 'twitter_' . $i, '')),
          'company'    => trim(arr_get($_POST, 'company_' . $i, '')),
          'job_title'  => trim(arr_get($_POST, 'job_title_' . $i, '')),
          'shirt_size' => trim(arr_get($_POST, 'shirt_size_' . $i, '')),
          'dietary'    => trim(arr_get($_POST, 'dietary_' . $i, '')),
        );
        $attendee_data[$i] = $attendee;

        $errors = array();

        if(empty($attendee['first_name'])) {
          $errors['first_name'] = 'This field is required.';
        }
        if(empty($attendee['last_name'])) {
          $errors['last_name'] = 'This field is required.';
        }
        if(! (filter_var($attendee['email'], FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $attendee['email'])) ) {
          $errors['email'] = 'An email is required.';
        }

        if(0 != count($errors)) {
          $form_errors[$i] = $errors;
        }
      }

      $receipt_email = trim(arr_get($_POST, 'receipt_email', ''));
      if(! (filter_var($receipt_email, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $receipt_email)) ) {
        $form_errors['receipt_email'] = 'An email is required.';
      }

      if(0 == count($form_errors)) {
        $stripe_token = $_POST['stripeToken'];

        $ticket_price_as_pennies = $ticket_price * 100;
        $total_as_pennies = $ticket_price_as_pennies * $number_of_tickets;

        try {
          $charge = \Stripe\Charge::create(array(
            "amount"        => $total_as_pennies,
            "currency"      => "usd",
            "source"        => $stripe_token,
            "description"   => "$number_of_tickets NEJSCONF 2016 Tickets",
            "receipt_email" => $receipt_email,
            "metadata"      => array(
              "coupon_code"       => $coupon_code,
              "ticket_price"      => $ticket_price,
              "number_of_tickets" => $number_of_tickets,
            ),
          ));

					$httpAdapter = new CurlHttpAdapter();
					$sparkpost = new SparkPost($httpAdapter, ['key' => $config['sparkpost']['api_key']]);

					foreach($attendee_data as $attendee) {
						try {
							$results = $sparkpost->transmission->send([
								'from'=>[
									'name' => 'NEJS Conf',
									'email' => 'tickets@nejsconf.com'
								],
								'html' => file_get_contents('register.template'),
								'substitutionData' => $attendee,
								'subject' => 'NEJS Conf 2016 Registration',
								'recipients' => [
									[
										'address' => [
											'name' => arr_get($attendee, 'first_name'),
											'email'=> arr_get($attendee, 'email')
										]
									]
								]
							]);
						}
						catch (\Exception $err) {
							error_log("Mandrill error sending results to " . arr_get($attendee, 'email', '?') . ": " . $err);
						}
					}

          $statement = $mysql->prepare("INSERT INTO `sales` (`charge_id`, `email`, `tickets`, `coupon_code`, `price`, `total`) VALUES (?, ?, ?, ?, ?, ?)");
          if(!$statement) {
            die($mysql->error);
          }
          $charge_id = $charge['id'];
          $statement->bind_param('ssisii', $charge_id, $receipt_email, $number_of_tickets, $coupon_code, $ticket_price_as_pennies, $total_as_pennies);
          $statement->execute();
          $sale_id = $statement->insert_id;
          $statement->close();

          $statement = $mysql->prepare("INSERT INTO `tickets` (`sale_id`, `first_name`, `last_name`, `email`, `twitter`, `company`, `job_title`, `shirt_size`, `dietary`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
          foreach($attendee_data as $attendee) {
            $statement->bind_param('isssssss', $sale_id, $attendee['first_name'], $attendee['last_name'], $attendee['email'], $attendee['twitter'], $attendee['company'], $attendee['job_title'], $attendee['shirt_size'], $attendee['dietary']);
            $statement->execute();
          }
          $statement->close();

          $show_purchase_success = true;
        }
        catch(Stripe\Error\Card $e) {
          $error_json = $e->getJsonBody();
          $stripe_error = $error_json['error']['message'];
        }
        catch(Stripe\Error\InvalidRequest $e) {
          $stripe_error = "An error occurred charging your card. Please try again.";
        }
        catch(Stripe\Error $e) {
          $stripe_error = "An error occurred charging your card. Please try again.";
        }
      }

    }

    if($show_purchase_success) {
  ?>
    <div class="content skinny-content" id="post" data-role="main">
      <h1>Success!</h1>
      <p>
        You should receive confirmation emails shortly.
      </p>
      <p>
        If you have questions, please don't hesitate to contact us at <a href="mailto:tickets@nejsconf.com" style="color: #FFF; text-shadow: none;">tickets@nejsconf.com</a>
      </p>
    </div>
  <?php
    }
    else {
  ?>

  <form method="POST" id="register_form">
    <div class="content skinny-content" id="post" data-role="main">
      <h1>Buy Tickets</h1>

      <fieldset>
        <label for="number_of_tickets">Quantity</label>
        <div class="select-css-button select-css">
          <select id="number_of_tickets" name="number_of_tickets">
            <?php for($i = 1; $i <= $config['checkout']['max_tickets']; $i++): ?>
            <option<?php if($i == $number_of_tickets):?> selected="selected"<?php endif; ?>><?php echo $i; ?></option>
            <?php endfor; ?>
          </select>
        </div>
      </fieldset>

      <div id="ticket_blocks">
      <?php for($i = 1; $i <= $number_of_tickets; $i++): ?>
        <fieldset id="ticket_block_<?php echo $i; ?>">
          <legend>Attendee #<?php echo $i; ?></legend>

          <div class="form_field<?php if(arr_get(arr_get($form_errors, $i, array()), 'first_name')): ?> error<?php endif; ?>">
            <label for="first_name_<?php echo $i; ?>">First Name <span class="required">Required</span></label>
            <input id="first_name_<?php echo $i; ?>" name="first_name_<?php echo $i; ?>" data-validate="required" type="text" value="<?php echo htmlspecialchars(arr_get($_POST, "first_name_" . $i)); ?>" />
            <div class="form_error" id="error_first_name_<?php echo $i; ?>"><?php echo arr_get(arr_get($form_errors, $i, array()), 'first_name'); ?></div>
          </div>

          <div class="form_field<?php if(arr_get(arr_get($form_errors, $i, array()), 'last_name')): ?> error<?php endif; ?>">
            <label for="last_name_<?php echo $i; ?>">Last Name <span class="required">Required</span></label>
            <input id="last_name_<?php echo $i; ?>" name="last_name_<?php echo $i; ?>" data-validate="required" type="text" value="<?php echo htmlspecialchars(arr_get($_POST, "last_name_" . $i)); ?>" />
            <div class="form_error" id="error_last_name_<?php echo $i; ?>"><?php echo arr_get(arr_get($form_errors, $i, array()), 'last_name'); ?></div>
          </div>

          <div class="form_field <?php if(arr_get(arr_get($form_errors, $i, array()), 'email')): ?> error<?php endif; ?>">
            <label for="email_<?php echo $i; ?>">Email Address <span class="required">Required</span></label>
            <input id="email_<?php echo $i; ?>" name="email_<?php echo $i; ?>" data-validate="email" type="text" value="<?php echo arr_get($_POST, "email_" . $i); ?>" />
            <div class="form_error" id="error_email_<?php echo $i; ?>"><?php echo arr_get(arr_get($form_errors, $i, array()), 'email'); ?></div>
          </div>

          <div class="form_field">
            <label for="twitter_<?php echo $i; ?>">Twitter Username</label>
            <input id="twitter_<?php echo $i; ?>" name="twitter_<?php echo $i; ?>" type="text" value="<?php echo htmlspecialchars(arr_get($_POST, "twitter_" . $i)); ?>" placeholder="@nejsconf" />
          </div>

          <div class="form_field">
            <label for="company_<?php echo $i; ?>">Company</label>
            <input id="company_<?php echo $i; ?>" name="company_<?php echo $i; ?>" type="text" value="<?php echo htmlspecialchars(arr_get($_POST, "company_" . $i)); ?>" />
          </div>

          <div class="form_field">
            <label for="job_title_<?php echo $i; ?>">Job Title</label>
            <input id="job_title_<?php echo $i; ?>" name="job_title_<?php echo $i; ?>" type="text" value="<?php echo htmlspecialchars(arr_get($_POST, "job_title_" . $i)); ?>" />
          </div>

          <label for="shirt_size_<?php echo $i; ?>">T-Shirt Size</label>
          <div class="select-css-button select-css">
            <select id="shirt_size_<?php echo $i; ?>" name="shirt_size_<?php echo $i; ?>">
              <?php foreach(array('Extra Small (Unisex/Mens)', 'Small (Unisex/Mens)', 'Medium (Unisex/Mens)', 'Large (Unisex/Mens)', 'Extra Large (Unisex/Mens)', 'XXL (Unisex/Mens)', 'Extra Small (Womens)', 'Small (Womens)', 'Medium (Womens)', 'Large (Womens)', 'Extra Large (Womens)', 'XXL (Womens)') as $size): ?>
              <option<?php if(arr_get($_POST, 'shirt_size_' . $i, 'Medium') == $size):?> selected="selected"<?php endif; ?>><?php echo $size; ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form_field">
            <label for="dietary_<?php echo $i; ?>">Food Restrictions</label>
            <input id="dietary_<?php echo $i; ?>" name="dietary_<?php echo $i; ?>" type="text" value="<?php echo htmlspecialchars(arr_get($_POST, "dietary_" . $i)); ?>" maxlength="255" />
          </div>
        </fieldset>
      <?php endfor; ?>
      </div>

      <fieldset>
        <legend>Coupon</legend>
        <div class="form_field">
          <label for="coupon_code">Coupon Code</label>
          <input type="text" name="coupon_code" id="coupon_code" value="<?php echo $coupon_code; ?>" />
          <button type="button" id="update_coupon" class="btn-tertiary">Apply Code</button>
        </div>
      </fieldset>
      <div class="total">
        <p class="note">Are you a student? We do offer a heavily discounted coupon code for students (valid Student ID required upon admission). <a href="https://twitter.com/intent/tweet?text=%40nejsconf%20Hook%20me%20up%20with%20the%20Student%20coupon%20code%20!">Send us a tweet</a> for the code!</p>
      </div>

      <div class="total">
        <h3>Total</h3>
        $<span id="current_price"><?php echo $ticket_price; ?></span> &times; <span id="ticket_count"><?php echo $number_of_tickets; ?></span> = <span id="ticket_total">$<?php echo $ticket_price * $number_of_tickets; ?></span>
        <p class="note">Early Bird Pricing is now in effect. A limited number of Early Bird Tickets are available and will be sold on a first come first serve basis! Once they’re gone, the full ticket price ($240) will be applied.</p>
      </div>

      <fieldset>
        <legend>Payment</legend>

        <div class="payment-errors"><?php if($stripe_error) { echo htmlspecialchars($stripe_error); } ?></div>
        
        <div class="form_field<?php if(arr_get($form_errors, 'receipt_email', false)): ?> error<?php endif; ?>">
          <label for="receipt_email">
            Receipt Email Address
            <span class="required">Required</span>
          </label>
          <input type="text" data-validate="email" id="receipt_email" name="receipt_email" value="<?php echo htmlspecialchars($receipt_email); ?>"/>
          <div class="form_error"><?php echo arr_get($form_errors, 'receipt_email', ''); ?></div>
        </div>

        <div class="form_field">
          <label for="creditcard">
            Card Number
            <span class="required">Required</span>
          </label>
          <input id="creditcard" type="text" size="20" data-stripe="number" data-validate="creditcard" />
          <div class="form_error"></div>
        </div>

        <div class="form_field">
          <label for="cvc">
            CVC
            <span class="required">Required</span>
          </label>
          <input id="cvc" type="text" size="4" data-stripe="cvc" data-validate="cvc"/>
          <div class="form_error"></div>
        </div>

        <div class="form_field">
          <label for="exp-month">Expiration (MM/YYYY) <span class="required">Required</span></label>
          <input id="exp-month" type="text" class="short" size="2" data-stripe="exp-month" data-validate="required" style="width: 20%"/> 
          <input type="text" class="short" size="4" data-stripe="exp-year" data-validate="required" style="width: 40%"/>
          <div class="form_error"></div>
        </div>

      </fieldset>
    </div><!-- /.content -->

    <button type="submit" class="btn-primary">Purchase Tickets</button>

  </form>

<script type="text/html" id="ticket_block_template">
<legend>Attendee #{{block_number}}</legend>
<div class="form_field">
  <label for="first_name_{{block_number}}">First Name <span class="required">Required</span></label>
  <input id="first_name_{{block_number}}" name="first_name_{{block_number}}" data-validate="required" type="text" />
  <div class="form_error" id="error_first_name_{{block_number}}"></div>
</div>

<div class="form_field">
  <label for="last_name_{{block_number}}">Last Name <span class="required">Required</span></label>
  <input id="last_name_{{block_number}}" name="last_name_{{block_number}}" data-validate="required" type="text" />
  <div class="form_error" id="error_last_name_{{block_number}}"></div>
</div>

<div class="form_field">
  <label for="email_{{block_number}}">Email Address <span class="required">Required</span></label>
  <input id="email_{{block_number}}" name="email_{{block_number}}" data-validate="email" type="text" />
  <div class="form_error" id="error_email_{{block_number}}"></div>
</div>

<div class="form_field">
  <label for="twitter_{{block_number}}">Twitter Username</label>
  <input id="twitter_{{block_number}}" name="twitter_{{block_number}}" type="text" />
</div>

<div class="form_field">
  <label for="company_{{block_number}}">Company</label>
  <input id="company_{{block_number}}" name="company_{{block_number}}" type="text" />
</div>

<div class="form_field">
  <label for="job_title_{{block_number}}">Job Title</label>
  <input id="job_title_{{block_number}}" name="job_title_{{block_number}}" type="text" />
</div>

<div class="form_field">
  <label for="dietary_{{block_number}}">Food Restrictions</label>
  <input id="dietary_{{block_number}}" name="dietary_{{block_number}}" type="text" maxlength="255" />
</div>

<label for="shirt_size_{{block_number}}">T-Shirt Size</label>
<div class="select-css-button select-css">
  <select id="shirt_size_{{block_number}}" name="shirt_size_{{block_number}}">
    <?php foreach(array('Extra Small (Unisex/Mens)', 'Small (Unisex/Mens)', 'Medium (Unisex/Mens)', 'Large (Unisex/Mens)', 'Extra Large (Unisex/Mens)', 'XXL (Unisex/Mens)', 'Extra Small (Womens)', 'Small (Womens)', 'Medium (Womens)', 'Large (Womens)', 'Extra Large (Womens)', 'XXL (Womens)') as $size): ?>
    <option><?php echo $size; ?></option>
    <?php endforeach; ?>
  </select>
</div>
</script>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/zepto/1.1.4/zepto.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/validator/3.12.0/validator.min.js"></script>
<script>

  Stripe.setPublishableKey('<?php echo $config['stripe']['public_key']; ?>');

  $(function () {

    var original_ticket_price = <?php echo $config['checkout']['ticket_price']; ?>,
         current_ticket_price = <?php echo $ticket_price; ?>,
               $ticket_select = document.getElementById("number_of_tickets"),
        $ticket_block_wrapper = document.getElementById('ticket_blocks'),
        ticket_block_template = document.getElementById('ticket_block_template').innerText,
                $ticket_total = document.getElementById('ticket_total'),
                $ticket_count = document.getElementById('ticket_count'),
               $current_price = document.getElementById('current_price'),
                 $coupon_code = document.getElementById('coupon_code');

    $ticket_select.onchange = updateForm;

    document.getElementById('update_coupon').onclick = function (e) {
      e.preventDefault();
      $.getJSON("/coupon.php", {'coupon_code': $coupon_code.value}, function (data) {
        if(data['code'] === false) {
          $coupon_code.value = '';
          alert("Sorry, that coupon code does not exist.");
        }
        if( data['price'] < 0 ) {
          current_ticket_price = original_ticket_price + data['price'];
        } else {
          current_ticket_price = data['price'];
        }

        updatePrice();
      });
    };

    $("#register_form").on('submit', function () {
      
      var errors = false,
           $form = $(this);

      $form.find('input').each(function (i, e) {
        
        var $input = $(e),
          $wrapper = $input.parent(),
     $errorMessage = $wrapper.find(".form_error"),
         validates = $input.data('validate'),
             value = e.value.replace(/^\s+|\s+$/g, '');

        $wrapper.removeClass("error");
        $errorMessage.text('');

        if( validates === 'required') {
          if(! validator.isLength(value, 1)) {
            $wrapper.addClass('error');
            $errorMessage.text('This field is required.');
            errors = true;
          }
        }
        else if ( validates === 'email' ) {
          if(! validator.isEmail(value)) {
            $wrapper.addClass('error');
            $errorMessage.text('An email is required.');
            errors = true;
          }
        }
        else if ( validates == 'creditcard' ) {
          if( ! Stripe.card.validateCardNumber(value) ) {
            $wrapper.addClass('error');
            $errorMessage.text('A valid credit card number is required.');
            errors = true;
          }
        }
        else if ( validates == 'cvc' ) {
          if( ! Stripe.card.validateCVC(value) ) {
            $wrapper.addClass('error');
            $errorMessage.text('A valid CVC is required.');
            errors = true;
          }
        }
      });

      if( ! errors ) {
        Stripe.card.createToken(this, stripeResponseHandler);
        $form.find('button').prop('disabled', true);
      }

      return false;
    });

    function stripeResponseHandler(status, response) {
      var $form = $('#register_form');

      if (response.error) {
        // Show the errors on the form
        $form.find('.payment-errors').text(response.error.message);
        $form.find('button').prop('disabled', false);
      }
      else {
        var token = response.id;
        $form.append($('<input type="hidden" name="stripeToken" />').val(token));
        $form.get(0).submit();
      }
    };

    function updateForm () {
        var i, block, ticket_blocks = parseInt($ticket_select.value, 10);
        for(i = 1; i <= <?php echo $available_tickets; ?>; i++) {
          block = document.getElementById("ticket_block_" + i);
          // Delete old blocks
          if(i > ticket_blocks) { 
            if(null !== block) {
              block.parentNode.removeChild(block);
            }
          }
          // Inject new blocks
          else {
            if(null === block) {
              var ticketBlock = document.createElement("fieldset");
              ticketBlock.innerHTML = ticket_block_template.replace(/{{block_number}}/g, i);
              ticketBlock.id = "ticket_block_" + i;
              $ticket_block_wrapper.appendChild(ticketBlock);
            }
          }
        }

        updatePrice();
      }

    function updatePrice () {
      $current_price.innerText = current_ticket_price;
      $ticket_count.innerText = parseInt($ticket_select.value, 10);
      $ticket_total.innerText = "$" + (current_ticket_price * parseInt($ticket_select.value, 10));
    }
  });
</script>
  <?php } ?>
<?php } ?>
{% endraw %}
