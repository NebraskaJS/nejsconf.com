---
layout: default
---

<a href="#" class="btn-primary">Get notified <span>when tickets go on sale</span></a>

{% raw %}
<?php
  require 'vendor/autoload.php';
  $config = require 'config.php';

  $show_form = false;

  if( $_POST ) {
    $show_form = true;
    $mc = new Mailchimp($config['api-key']);

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
<div class="form-result success">Awesome! We will be in touch!</div>
<?php
    } 
    catch (Mailchimp_Error $e) {
      if ($e->getMessage()) {
?>
  <div class="form-result error"><?php echo $e->getMessage(); ?></div>
<?php
      }
      else {
?>
  <div class="form-result error">Could not subscribe. Please try again.</div>
<?php
      }
    }
  }
?>
<form method="POST"<?php if( ! $show_form ): ?> class="collapsed"<?php endif; ?>>
  <label>Email</label>
  <input type="text" name="email" <?php if(isset($_POST['email'])): ?>value="<?php echo htmlspecialchars($_POST['email']); ?>"<?php endif; ?>/>
  <button type="submit" class="btn-secondary">Subscribe</button>
</form>
{% endraw %}

<div class="secondary-buttons">
	<!-- <a href="#" class="btn-secondary">Apply to Speak</a> -->
	<a href="mailto:sponsor@nejsconf.com" class="btn-secondary">Sponsor Us</a>
</div>
