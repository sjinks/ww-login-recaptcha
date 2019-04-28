<?php
defined('ABSPATH') || die();
ob_start();
$suffix = \wp_scripts_get_suffix();
require __DIR__ . "/../assets/login{$suffix}.js";
return ob_get_clean();
