<?php \defined('ABSPATH') || die(); ?>
<div class="wrap">
	<h1><?=\__('Login reCAPTCHA', 'wwlr-admin'); ?></h1>

	<p><?=\__('Please <a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener">click here</a> to view your existing reCAPTCHA keys or sign up for a new one.', 'wwlr-admin'); ?></p>

	<form action="<?=\esc_attr(\admin_url('options.php'));?>" method="post">
	<?php
	\settings_fields('wwlr');
	\do_settings_sections('wwlr');
	\submit_button();
	?>
	</form>

	<p><?=\__('If everything is configured correctly, you should see a reCAPTCHA box below or the logo of the invisible reCAPTCHA in the bottom right corner.', 'wwlr-admin'); ?></p>

	<div id="recaptcha-container"></div>

	<p><strong><?=\__('If you see an error message:', 'wwlr-admin'); ?></strong></p>
	<ul>
		<li><?=\__('Please make sure that the keys entered are correct (<strong>note:</strong> the plugin supports only reCAPTCHA v2, <em>"I\'m not a robot" Checkbox</em> and <em>Invisible reCAPTCHA badge</em> falvors)', 'wwlr-admin'); ?></li>
		<li><?=\__('If you are using the <em>"I\'m not a robot" Checkbox</em> reCAPTCHA, please make sure that "Invisible" checkbox above is turned off.', 'wwlr-admin'); ?></li>
	</ul>

	<p><strong><?=\__('If reCAPTCHA is displayed correctly:', 'wwlr-admin'); ?></strong></p>
	<ol>
		<li><?=\__('Check "Active" checkbox and save settings.', 'wwlr-admin'); ?></li>
		<li><?=\__('Open a different browser or use private browsing window to log into your account.', 'wwlr-admin'); ?></li>
		<li>
			<?=\__('<strong>Do not</strong> close this window or log out until you make sure that you are able to log in.', 'wwlr-admin'); ?>
			<br/>
			<?=\__('If you are unable to log in, uncheck "Active" checkbox and save settings or deactivate this plugin.', 'wwlr-admin'); ?>
		</li>
	</ol>
</div>
