<?php
/*
 * Plugin Name: reCAPTCHA Integration
 * Plugin URI:
 * Description: Adds Google reCAPTCHA to the login form, thwarting automated login attempts
 * Version: 1.0.0
 * Author: Volodymyr Kolesnykov
 * License: MIT
 * Domain Path: /lang
 */

defined('ABSPATH') || die();

if (defined('VENDOR_PATH')) {
	require VENDOR_PATH . '/vendor/autoload.php';
}
elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
	require __DIR__ . '/vendor/autoload.php';
}
elseif (file_exists(ABSPATH . 'vendor/autoload.php')) {
	require ABSPATH . 'vendor/autoload.php';
}

WildWolf\LoginRecaptcha\Plugin::instance();
