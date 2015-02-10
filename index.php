---
layout: default
---

{% raw %}
<?php
require 'vendor/autoload.php';
$config = require 'config.php';

$show_form = true;

if( $_POST ) {
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
    $show_form = false;
?>
<div class="form-result success" id="feedback">Awesome! We will be in touch!</div>
<?php if(isset($_POST['email'])): ?><div class="form-result feedback"><?php echo htmlspecialchars($_POST['email']); ?></div><?php endif; ?>
<?php
  } catch (Mailchimp_Error $e) {
    if (empty($_POST['email'])) {
?>
<div class="form-result error" id="feedback">An email is required.</div>
<?php
    } elseif ($e->getMessage()) {
?>
<div class="form-result error" id="feedback"><?php echo $e->getMessage(); ?></div>
<?php
    } else {
?>
<div class="form-result error" id="feedback">Could not subscribe. Please try again.</div>
<?php
    }
  }
}

if( $show_form ): ?>
<form method="POST" action="<?php echo $PHP_SELF; ?>#feedback" id="teaser-subscribe"  class="collapsed">
  <label for="teaser-email" class="a11y-only">Email</label>
  <input type="text" id="teaser-email" name="email" <?php if(isset($_POST['email'])): ?>value="<?php echo htmlspecialchars($_POST['email']); ?>"<?php endif; ?> placeholder="Your e-mail address">
  <button type="submit" class="btn-primary">Get notified <span>when tickets go on sale</span></button>
</form>
<?php
endif; ?>
{% endraw %}

<div class="secondary-buttons">
	<!-- <a href="#" class="btn-secondary">Apply to Speak</a> -->
	<a href="mailto:sponsor@nejsconf.com" class="btn-secondary">Sponsor Us</a>
</div>
