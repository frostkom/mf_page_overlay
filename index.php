<?php
/*
Plugin Name: MF Page Overlay
Plugin URI: https://github.com/frostkom/mf_page_overlay
Description: Wordpress plugin to add page overlay
Version: 1.0.5
Licence: GPLv2 or later
Author: Martin Fors
Author URI: https://martinfors.se
Text Domain: lang_page_overlay
Domain Path: /lang

Depends: Meta Box, MF Base
GitHub Plugin URI: frostkom/mf_page_overlay
*/

if(!function_exists('is_plugin_active') || function_exists('is_plugin_active') && is_plugin_active("mf_base/index.php"))
{
	include_once("include/classes.php");

	$obj_page_overlay = new mf_page_overlay();

	add_action('cron_base', array($obj_page_overlay, 'cron_base'), mt_rand(1, 10));

	if(is_admin())
	{
		register_activation_hook(__FILE__, 'activate_page_overlay');

		add_action('rwmb_meta_boxes', array($obj_page_overlay, 'rwmb_meta_boxes'));
	}

	else
	{
		add_action('wp_head', array($obj_page_overlay, 'wp_head'), 0);
		add_filter('body_class', array($obj_page_overlay, 'body_class'));
		add_action('wp_footer', array($obj_page_overlay, 'wp_footer'));
	}

	load_plugin_textdomain('lang_page_overlay', false, dirname(plugin_basename(__FILE__))."/lang/");

	function activate_page_overlay()
	{
		require_plugin("meta-box/meta-box.php", "Meta Box");
	}
}