<?php defined('ABSPATH') || die(); ?>
<?php if (!empty($params['invisible'])) : ?>
<div class="g-recaptcha" data-size="invisible" data-sitekey="<?=esc_attr($params['sitekey']); ?>" data-callback="WWLoginReCaptcha_enableSubmit" data-expred-callback="WWLoginReCaptcha_disableSubmit"></div>
<?php else : ?>
<div class="g-recaptcha" data-sitekey="<?=esc_attr($params['sitekey']); ?>" data-callback="WWLoginReCaptcha_enableSubmit" data-expred-callback="WWLoginReCaptcha_disableSubmit"></div>
<?php endif; ?>