<?php
namespace WildWolf\LoginRecaptcha;

abstract class WPUtils
{
	public static function render(string $view, array $params = [])
	{
		return require __DIR__ . '/../views/' . $view . '.php';
	}

	public static function assetsUrl(string $file) : string
	{
		return \plugins_url('assets/' . $file, \dirname(__DIR__) . '/plugin.php');
	}

	public static function input_field(array $args)
	{
		$name    = Plugin::OPTIONS_KEY;
		$options = empty($args['sitewide']) ? \get_option($name) : \get_site_option($name);
		$id      = \esc_attr($args['label_for']);
		$type    = \esc_attr($args['type'] ?? 'text');
		$value   = \esc_attr($options[$id] ?? '');
		$help    = $args['help'] ?? '';
		echo <<< EOT
<input type="{$type}" name="{$name}[{$id}]" id="{$id}" value="{$value}" size="45"/>
EOT;
		if ($help) {
			echo <<< EOT
<p class="help">{$help}</p>
EOT;
		}
	}

	public static function checkbox(array $args)
	{
		$name    = Plugin::OPTIONS_KEY;
		$options = empty($args['sitewide']) ? \get_option($name) : \get_site_option($name);
		$id      = \esc_attr($args['label_for']);
		$checked = \checked($options[$id] ?? '', 1, false);
		$help    = $args['help'] ?? '';
		echo <<< EOT
<input type="checkbox" name="{$name}[{$id}]" id="{$id}" value="1"{$checked}/>
EOT;
		if ($help) {
			echo <<< EOT
<p class="help">{$help}</p>
EOT;
		}
	}
}
