<?php
namespace WildWolf\LoginRecaptcha;

final class Admin
{
	private $settings_hook = "\000";

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
		$this->init();
	}

	public function init()
	{
		\load_plugin_textdomain('wwlr-admin', /** @scrutinizer ignore-type */ false, \plugin_basename(\dirname(__DIR__)) . '/lang/');
		\add_action('admin_init', [$this, 'admin_init']);

		if (Plugin::isSitewide() && \is_network_admin()) {
			\add_action('network_admin_menu', [$this, 'network_admin_menu']);
			$options = (array)\get_site_option(Plugin::OPTIONS_KEY, []);
			if (!empty($options['active'])) {
				return;
			}
		}

		\add_action('admin_menu', [$this, 'admin_menu']);
	}

	public function admin_init()
	{
		$this->registerSettings();

		\add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
		\add_action('admin_post_save_ms_ww_login_recaptcha_options', [$this, 'admin_post_save_ms_options']);
	}

	private function registerSettings()
	{
		\add_settings_section('default', '', '__return_null', 'wwlr');
		\add_settings_field(
			'active',
			\__('Active', 'wwlr-admin'),
			[WPUtils::class, 'checkbox'],
			'wwlr',
			'default',
			['label_for' => 'active']
		);

		\add_settings_field(
			'invisible',
			\__('Invisible', 'wwlr-admin'),
			[WPUtils::class, 'checkbox'],
			'wwlr',
			'default',
			[
				'label_for' => 'invisible',
				'help'      => \__('Check this if you have signed up for "Invisible reCAPTCHA badge"', 'wwlr-admin'),
			]
		);

		\add_settings_field(
			'sitekey',
			\__('Site Key', 'wwlr-admin'),
			[WPUtils::class, 'input_field'],
			'wwlr',
			'default',
			['label_for' => 'sitekey']
		);

		\add_settings_field(
			'secretkey',
			\__('Secret Key', 'wwlr-admin'),
			[WPUtils::class, 'input_field'],
			'wwlr',
			'default',
			['label_for' => 'secretkey']
		);
	}

	public function network_admin_menu()
	{
		$this->settings_hook = \add_menu_page(\__('WW Login reCAPTCHA', 'wwlr-admin'), \__('WW Login reCAPTCHA', 'wwlr-admin'), 'manage_options', 'wwlr-msadmin', [$this, 'network_options_page']);
	}

	public function admin_menu()
	{
		$this->settings_hook = \add_options_page(\__('WW Login reCAPTCHA', 'wwlr-admin'), \__('WW Login reCAPTCHA', 'wwlr-admin'), 'manage_options', 'wwlr', [$this, 'options_page']);
	}

	public function network_options_page()
	{
		$message = \filter_input(\INPUT_GET, 'message', \FILTER_SANITIZE_NUMBER_INT);
		switch ($message) {
			case 1: \add_settings_error('general', 'settings_updated', \__('Settings saved.', 'wwlr-admin'), 'updated'); break;
		}

		WPUtils::render('ms-options');
	}

	public function options_page()
	{
		WPUtils::render('options');
	}

	public function admin_enqueue_scripts($hook)
	{
		if ($this->settings_hook === $hook) {
			$suffix = \wp_scripts_get_suffix();
			\wp_enqueue_script('wwlr-settings', WPUtils::assetsUrl("settings{$suffix}.js"), [], '2019042803', true);
		}
	}

	public function admin_post_save_ms_options()
	{
		\check_admin_referer('save_ms_ww_login_recaptcha_options');
		$data = (array)($_POST['ww_login_recaptcha'] ?? []);

		$options = [
			'active'    => !empty($data['active']),
			'invisible' => !empty($data['invisible']),
			'sitekey'   => $data['sitekey'] ?? '',
			'secretkey' => $data['secretkey'] ?? '',
		];

		\update_site_option(Plugin::OPTIONS_KEY, $options);
		\wp_redirect(\network_admin_url('admin.php?page=wwlr-msadmin&message=1'));
	}
}
