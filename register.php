---
layout: skinny
---

{% raw %}
<?php
  require 'vendor/autoload.php';
  $config = require 'config.php';

  \Stripe\Stripe::setApiKey($config['stripe']['secret_key']);

  $ticket_price = $config['checkout']['ticket_price'];
?>
<form method="POST" action="<?php echo $PHP_SELF; ?>">

  <label for="number_of_tickets">Number Of Tickets</label>
  <select id="number_of_tickets" name="number_of_tickets">
    <?php for($i = 1; $i <= $config['checkout']['max_tickets']; $i++): ?>
    <option><?php echo $i; ?></option>
    <?php endfor; ?>
  </select>

  <div id="ticket_blocks"></div>

  <h3>Total: $<span id="current_price"><?php echo $ticket_price; ?></span> &times; 1 = <span id="ticket_total">$<?php echo $ticket_price; ?></span></h3>

  <label for="coupon_code">Coupon Code</label>
  <input type="text" name="coupon_code" id="coupon_code" /> <a href="#" id="update_coupon">Update</a>

  <script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
          data-key="<?php echo $config['stripe']['public_key']; ?>"
          data-description="NEJS CONF 2015 Tickets"></script>
</form>

<script type="text/html" id="ticket_block_template">
<h4>Attendee #{{block_number}}</h4>
<label for="first_name{{block_number}}">First Name</label>
<input name="first_name{{block_number}}" type="text" />
<label for="last_name{{block_number}}">Last Name</label>
<input name="last_name{{block_number}}" type="text" />
<label for="email{{block_number}}">Email Address</label>
<input name="email{{block_number}}" type="text" />
</script>


<script src="//cdnjs.cloudflare.com/ajax/libs/zepto/1.1.4/zepto.min.js"></script>
<script>

  $(function () {

    var original_ticket_price = <?php echo $config['checkout']['ticket_price']; ?>,
         current_ticket_price = <?php echo $ticket_price; ?>,
               $ticket_select = document.getElementById("number_of_tickets"),
        $ticket_block_wrapper = document.getElementById('ticket_blocks'),
        ticket_block_template = document.getElementById('ticket_block_template').innerText,
                $ticket_total = document.getElementById('ticket_total'),
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
        current_ticket_price = data['price'];
        updatePrice();
      });
    };

    function updateForm () {
        var i, block, ticket_blocks = parseInt($ticket_select.value, 10);
        for(i = 1; i <= <?php echo $config['checkout']['max_tickets']; ?>; i++) {
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
              var ticketBlock = document.createElement("div");
              ticketBlock.innerHTML = ticket_block_template.replace("{{block_number}}", i);
              ticketBlock.id = "ticket_block_" + i;
              $ticket_block_wrapper.appendChild(ticketBlock);
            }
          }
        }

        updatePrice();
      }

    function updatePrice () {
      $current_price.innerText = current_ticket_price;
      $ticket_total.innerText = "$" + (current_ticket_price * parseInt($ticket_select.value, 10));
    }
  });
</script>
{% endraw %}
