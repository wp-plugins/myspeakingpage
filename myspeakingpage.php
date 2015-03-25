<?php
/*
Plugin Name: MySpeakingPage by Author Media
Plugin URI: http://www.myspeakingpage.com/
Description: A WordPress Plugin to help speakers advertise their skills.
Author: Author Media
Author URI: http://www.authormedia.com
Version: 1.0.0
*/

define('MSP_VERSION', '1.0.0');

require_once("includes/functions.php");
require_once("includes/admin_page.php");
require_once("includes/editor.php");
require_once("includes/frontend.php");
require_once("includes/seo.php");



/*---------------------------------------------------------*/
/* Initialize Plugin                                       */
/*---------------------------------------------------------*/

function msp_init() {
	msp_load_settings();
	msp_update_check();
	msp_customize_plugins_page();
	if(msp_detect_deactivation()) { return; }

	do_action('msp_init');
}
add_action('plugins_loaded', 'msp_init');

function msp_detect_deactivation() {
	if($GLOBALS['pagenow'] == "plugins.php" and current_user_can('install_plugins') and isset($_GET['action']) and $_GET['action'] == 'deactivate' and isset($_GET['plugin']) and $_GET['plugin'] == plugin_basename(dirname(__FILE__)).'/myspeakingpage.php') {
		msp_update_setting('detect_deactivated', 'detected');
		msp_track_event('plugin_deactivated', true);
		msp_send_tracking_data();
		return true;
	} else if(msp_get_setting('detect_deactivated') === 'detected') {
		msp_update_setting('detect_deactivated', false);
		msp_track_event('plugin_activated', true);
	}
	return false;
}

function msp_customize_plugins_page() {
	add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'msp_plugin_action_links');
}

function msp_plugin_action_links($actions) {
	unset($actions['edit']);
	$actions['review'] = '<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/myspeakingpage?filter=5#postform">'.__('Write a Review', 'myspeakingpage').'</a>';
	if(!defined('MSE_VERSION')) { $actions['myspeakingevents'] = '<a href="http://www.myspeakingevents.com/" target="_blank">'.__('Get MySpeakingEvents', 'mybooktable').'</a>'; }
	return $actions;
}
