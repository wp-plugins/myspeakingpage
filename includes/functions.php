<?php

/*---------------------------------------------------------*/
/* Utility Functions                                       */
/*---------------------------------------------------------*/

function msp_speaking_page_status() {
	$page_id = msp_get_setting('speaking_page');
	if(empty($page_id)) { return 'new'; }
	$page = get_post($page_id);
	if(empty($page)) { return 'error'; }
	if($page->post_status == 'trash') { return 'error'; }
	return 'ok';
}

function msp_is_admin_page() {
	global $pagenow;
	return ($pagenow == "admin.php" and isset($_GET['page']) and $_GET['page'] == "myspeakingpage");
}

function msp_is_editor_page() {
	global $pagenow;
	return ($pagenow == "post.php" and $_GET['action'] == 'edit' and isset($_GET['post']) and $_GET['post'] === strval(msp_get_setting('speaking_page')));
}

function msp_is_speaking_page() {
	global $post;
	return (!empty($post) and $post->ID == msp_get_setting('speaking_page'));
}



/*---------------------------------------------------------*/
/* Settings Functions                                      */
/*---------------------------------------------------------*/

function msp_load_settings() {
	global $msp_settings;
	$msp_settings = apply_filters('msp_settings', get_option('msp_settings'));
	if(empty($msp_settings)) { msp_reset_settings(); }
}

function msp_reset_settings() {
	global $msp_settings;
	$msp_settings = array(
		'version' => MSP_VERSION,
		'speaking_page' => 0
	);
	$msp_settings = apply_filters('msp_default_settings', $msp_settings);
	update_option('msp_settings', apply_filters('msp_update_settings', $msp_settings));
}

function msp_get_setting($name) {
	global $msp_settings;
	return isset($msp_settings[$name]) ? $msp_settings[$name] : NULL;
}

function msp_update_setting($name, $value) {
	global $msp_settings;
	$msp_settings[$name] = $value;
	update_option('msp_settings', apply_filters('msp_update_settings', $msp_settings));
}



/*---------------------------------------------------------*/
/* Update Check                                            */
/*---------------------------------------------------------*/

function msp_update_check() {
	$version = msp_get_setting("version");

	// if(version_compare($version, '1.0.0') < 0) { msp_upgrade_1_0_0(); }

	if($version !== MSP_VERSION) {
		msp_update_setting('version', MSP_VERSION);
		msp_track_event('plugin_updated', array('version' => MSP_VERSION));
		msp_send_tracking_data();
	}
}



/*---------------------------------------------------------*/
/* Tracking                                                */
/*---------------------------------------------------------*/

function msp_init_tracking() {
	if(msp_get_setting('allow_tracking') !== 'yes') { return; }

	if(!wp_next_scheduled('msp_periodic_tracking')) { wp_schedule_event(time(), 'daily', 'msp_periodic_tracking'); }
	add_action('msp_periodic_tracking', 'msp_send_tracking_data');
}
add_action('msp_init', 'msp_init_tracking');

function msp_load_tracking_data() {
	global $msp_tracking_data;
	if(empty($msp_tracking_data)) {
		mt_srand(time());
		$msp_tracking_data = get_option('msp_tracking_data');
		if(empty($msp_tracking_data)) {
			$payload = strval(get_bloginfo('url')).strval(time()).strval(rand());
			if(function_exists('hash')) {
				$id = hash('sha256', $payload);
			} else {
				$id = sha1($payload);
			}

			$msp_tracking_data = array(
				'id' => $id,
				'events' => array(),
				'ab_status' => array(),
			);

			update_option('msp_tracking_data', $msp_tracking_data);
		}
	}
}

function msp_get_tracking_data($name) {
	global $msp_tracking_data;
	msp_load_tracking_data();
	return isset($msp_tracking_data[$name]) ? $msp_tracking_data[$name] : NULL;
}

function msp_update_tracking_data($name, $value) {
	global $msp_tracking_data;
	msp_load_tracking_data();
	$msp_tracking_data[$name] = $value;
	update_option('msp_tracking_data', $msp_tracking_data);
}

function msp_track_event($name, $instance=false) {
	$events = msp_get_tracking_data('events');
	if(!isset($events[$name])) { $events[$name] = array(); }
	if(!isset($events[$name]['count'])) { $events[$name]['count'] = 0; }
	$events[$name]['count'] += 1;
	$events[$name]['last_time'] = time();

	if($instance !== false) {
		if(!is_array($instance)) { $instance = array(); }
		$instance['time'] = time();
		if(!isset($events[$name]['instances'])) { $events[$name]['instances'] = array(); }
		$events[$name]['instances'][] = $instance;
	}

	msp_update_tracking_data('events', $events);
}

function msp_send_tracking_data() {
	if(msp_get_setting('allow_tracking') !== 'yes') { return; }

	$action_type = null;
	$page_data = array(
		'title' => null,
		'has_blurb' => null,
		'has_about_you' => null,
		'action_type' => null,
		'button_text' => null,
		'has_headshots' => null,
		'has_videos' => null,
		'has_speaking_packet' => null,
		'has_book_to_speak_url' => null,
		'has_testimonials' => null,
		'has_topics' => null,
		'has_audios' => null,
		'has_seo_title' => null,
		'has_seo_metadesc' => null,
	);

	$speaking_page = get_post(msp_get_setting('speaking_page'));
	if(!empty($speaking_page)) {
		$page_data['title'] = $speaking_page->post_title;
		$page_data['has_blurb'] = !empty($speaking_page->post_excerpt);
		$page_data['has_about_you'] = !empty($speaking_page->post_content);
		$page_data['action_type'] = get_post_meta($speaking_page->ID, 'msp_action_type', true);
		$page_data['button_text'] = get_post_meta($speaking_page->ID, "msp_button_text", true);
		$headshots = get_post_meta($speaking_page->ID, "msp_headshots", true);
		$page_data['has_headshots'] = !empty($headshots);
		$videos = get_post_meta($speaking_page->ID, "msp_videos", true);
		$page_data['has_videos'] = !empty($videos);
		$speaking_packet = get_post_meta($speaking_page->ID, "msp_speaking_packet", true);
		$page_data['has_speaking_packet'] = !empty($speaking_packet);
		$book_to_speak_url = get_post_meta($speaking_page->ID, "msp_book_to_speak_url", true);
		$page_data['has_book_to_speak_url'] = !empty($book_to_speak_url);
		$testimonials = get_post_meta($speaking_page->ID, "msp_testimonials", true);
		$page_data['has_testimonials'] = !empty($testimonials);
		$topics = get_post_meta($speaking_page->ID, "msp_topics", true);
		$page_data['has_topics'] = !empty($topics);
		$audios = get_post_meta($speaking_page->ID, "msp_audios", true);
		$page_data['has_audios'] = !empty($audios);
		$seo_title = get_post_meta($speaking_page->ID, "msp_seo_title", true);
		$page_data['has_seo_title'] = !empty($seo_title);
		$seo_metadesc = get_post_meta($speaking_page->ID, "msp_seo_metadesc", true);
		$page_data['has_seo_metadesc'] = !empty($seo_metadesc);
	}

	$meta_box_order = msp_get_setting('meta_box_order');
	if(!empty($meta_box_order['normal'])) { $meta_box_order = $meta_box_order['normal']; }

	$data = array(
		'id' => msp_get_tracking_data('id'),
		'time' => time(),
		'version' => MSP_VERSION,
		'page_status' => msp_speaking_page_status(),
		'has_myspeakingevents' => defined('MSE_VERSION'),
		'meta_box_order' => $meta_box_order,
		'page_data' => $page_data,
		'events' => msp_get_tracking_data('events'),
	);

	global $wp_version;
	$options = array(
		'timeout' => ((defined('DOING_CRON') && DOING_CRON) ? 30 : 3),
		'body' => array('data' => serialize($data)),
		'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url')
	);

	$response = wp_remote_post('http://api.authormedia.com/plugins/myspeakingpage/analytics/submit', $options);
}
