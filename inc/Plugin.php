<?php
namespace WildWolf\LoginRecaptcha;

final class Plugin
{
	const OPTIONS_KEY = 'ww_login_recaptcha';

	private static $sitewide = false;

	public static function instance()
	{
		static $self = null;

		if (!$self) {
			$self = new self();
		}

		return $self;
	}

	private function __construct()
	{
		$basename = \plugin_basename(\dirname(__DIR__) . '/plugin.php');
		if (\is_multisite()) {
			if (!\function_exists('\\is_plugin_active_for_network')) {
				// @codeCoverageIgnoreStart
				// bootstrap.php includes this file
				require_once(\ABSPATH . '/wp-admin/includes/plugin.php');
				// @codeCoverageIgnoreEnd
			}

			self::$sitewide = \is_plugin_active_for_network($basename);
		}

		\add_action('init', [$this, 'init']);
		\add_action('activate_' . $basename,   [$this, 'activate']);
	}

	public function activate($network_wide)
	{
		self::$sitewide = !!$network_wide;
		if (!$network_wide) {
			$site_key   = \get_option('login_nocaptcha_key');
			$secret_key = \get_option('login_nocaptcha_secret');
			$bwprecapt  = \get_option('bwp_capt_general');
			if ($site_key && $secret_key) {
				$options = [
					'active'    => false,
					'sitekey'   => $site_key,
					'secretkey' => $secret_key,
					'invisible' => false,
				];

				\update_option(self::OPTIONS_KEY, $options);
			}
			elseif (\is_array($bwprecapt) && !empty($bwprecapt['input_pubkey']) && !empty($bwprecapt['input_prikey'])) {
				$options = [
					'active'    => false,
					'sitekey'   => $bwprecapt['input_pubkey'],
					'secretkey' => $bwprecapt['input_prikey'],
					'invisible' => false,
				];

				\update_option(self::OPTIONS_KEY, $options);
			}
		}
	}

	public static function getOptions() : array
	{
		$options = (array)\get_option(self::OPTIONS_KEY, []);
		if (empty($options['active']) && self::$sitewide) {
			$options = (array)\get_site_option(self::OPTIONS_KEY, []);
		}

		if (empty($options['active'])) {
			return ['', '', false];
		}

		$site_key   = $options['sitekey']   ?? '';
		$secret_key = $options['secretkey'] ?? '';
		$invisible  = $options['invisible'] ?? false;
		return [$site_key, $secret_key, $invisible];
	}

	public function init()
	{
		\load_plugin_textdomain('wwlr-front', /** @scrutinizer ignore-type */ false, \plugin_basename(\dirname(__DIR__)) . '/lang/');
		\register_setting('wwlr', self::OPTIONS_KEY, ['default' => []]);

		if (\is_admin()) {
			Admin::instance();
		}

		$keys = self::getOptions();
		if (!empty($keys[0]) && !empty($keys[1])) {
			\add_action('login_init',          [$this, 'login_init']);

			\add_action('login_form',          [$this, 'showRecaptcha']);
			\add_action('register_form',       [$this, 'showRecaptcha']);
			\add_action('lostpassword_form',   [$this, 'showRecaptcha']);

			\add_filter('registration_errors', [$this, 'authenticate']);
			\add_action('lostpassword_post',   [$this, 'authenticate']);
			\add_filter('authenticate',        [$this, 'authenticate'], 90);

			$active_plugins = (array)\get_option('active_plugins', []);
			if (\in_array('woocommerce/woocommerce.php', $active_plugins)) {
				\add_action('wp_enqueue_scripts',            [$this, 'login_enqueue_scripts']);
				\add_action('woocommerce_login_form',        [$this, 'showRecaptcha']);
				\add_action('woocommerce_lostpassword_form', [$this, 'showRecaptcha']);
			}
		}
	}

	public function login_init()
	{
		\add_action('login_enqueue_scripts', [$this, 'login_enqueue_scripts']);
		\add_filter('script_loader_tag',     [$this, 'script_loader_tag'], 10, 2);
	}

	public function login_enqueue_scripts()
	{
		$url = 'https://www.google.com/recaptcha/api.js?hl=' . \get_locale() . '&onload=WWLoginReCaptcha_onLoad';
		\wp_enqueue_script('ww-recaptcha-google', $url, [], null, true);
		\wp_add_inline_script('ww-recaptcha-google', WPUtils::render('recaptcha-script'));
		\wp_add_inline_style('login', 'div#login{width:348px}.g-recaptcha>div:not([class]){padding-bottom:16px}');
	}

	public function script_loader_tag($tag, $handle)
	{
		if ('ww-recaptcha-google' === $handle) {
			if (false === \strpos($tag, 'async')) {
				$tag = \str_replace('></script>', ' async="async"></script>', $tag);
			}

			if (false === \strpos($tag, 'defer')) {
				$tag = \str_replace('></script>', ' defer="defer"></script>', $tag);
			}
		}

		return $tag;
	}

	public function showRecaptcha()
	{
		$keys = self::getOptions();
		if (!empty($keys[0]) && !empty($keys[1])) {
			$params = ['sitekey' => $keys[0], 'invisible' => $keys[2]];
			WPUtils::render('login', $params);
		}
	}

	public function authenticate($retval)
	{
		$rm   = $_SERVER['REQUEST_METHOD'] ?? '';
		$keys = self::getOptions();
		if ((\is_wp_error($retval) && $retval->has_errors()) || $rm !== 'POST' || empty($keys[0]) || empty($keys[1])) {
			return $retval;
		}

		$recaptcha = new \ReCaptcha\ReCaptcha($keys[1]);
		$recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'] ?? null);

		$challenge = \stripslashes((string)($_POST['g-recaptcha-response'] ?? ''));
		$resp = $recaptcha->verify($challenge, $_SERVER['REMOTE_ADDR'] ?? null);
		if ($resp->isSuccess()) {
			return $retval;
		}

		$errors = \array_flip($resp->getErrorCodes());
		if (isset($errors['invalid-input-secret']) || isset($errors['missing-input-secret'])) {
			/// TODO: ask the user to check the settings
			\error_log('reCAPTCHA error: ' . \print_r($errors, 1));
			return $retval;
		}

		if (\is_wp_error($retval)) {
			$retval->add('invalid_captcha', \__('<strong>ERROR:</strong> reCAPTCHA verification failed.', 'wwlr-front'));
			return $retval;
		}

		return new \WP_Error('invalid_captcha', \__('<strong>ERROR:</strong> reCAPTCHA verification failed.', 'wwlr-front'));
	}

	public static function isSitewide() : bool
	{
		return self::$sitewide;
	}
}
