---
layout: default
---

{% raw %}
<?php
  require 'vendor/autoload.php';
  $config = require 'config.php';

  $render_form = true;
  $render_success = false;
  $form_collapsed = true;
  $error = null;

  if( $_POST ) {
    $form_collapsed = false;

    $mc = new Mailchimp($config['api-key']);
    curl_setopt($mc->ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($mc->ch, CURLOPT_SSL_VERIFYPEER, 0);

    try {
      $mc->lists->subscribe($config['list-id'], 
                            array('email' => $_POST['email']),
                            null,
                            'html',
                            false, 
                            true,
                            true,
                            true);
      $render_form = false;
      $render_success = true;
    } 
    catch (Mailchimp_Error $e) {
      if (empty($_POST['email'])) { 
        $error = "An email is required.";
      }
      else {
        $error = $e->getMessage();
        if ( ! $error) {
          $error = 'Could not subscribe. Please try again.';
        }
      }
    }
  }
?>

<?php if( $render_success ): ?>
  <div class="form-result success" id="feedback">Awesome! We will be in touch!</div>
  <div class="form-result feedback"><?php echo htmlspecialchars($_POST['email']); ?></div>
<?php endif; ?>
<?php if( $error ): ?><div class="form-result error" id="feedback"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<?php if( $render_form ): ?>
<form method="POST" action="<?php echo $PHP_SELF; ?>#feedback" id="teaser-subscribe"  class="collapsed">
  <label for="teaser-email" class="a11y-only">Email</label>
  <input type="email" id="teaser-email" name="email" <?php if(isset($_POST['email'])): ?>value="<?php echo htmlspecialchars($_POST['email']); ?>"<?php endif; ?> placeholder="Your e-mail address">
  <button type="submit" class="btn-primary">Get notified <span>when tickets go on sale</span></button>
</form>
<?php endif; ?>
{% endraw %}

<div class="secondary-buttons">
	<!-- <a href="#" class="btn-secondary">Apply to Speak</a> -->
  <a href="mailto:sponsor@nejsconf.com" class="btn-secondary">Sponsor Us</a>
	<a href="/cfp" class="btn-secondary">Propose a Talk</a>
</div>
