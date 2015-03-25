<?php

function msp_editor_init() {
	if(is_admin() and msp_is_editor_page()) {
		add_action('admin_enqueue_scripts', 'msp_enqueue_editor_resources');
		add_filter('parent_file', 'msp_override_editor_parent_file');
		add_action('admin_notices', 'msp_admin_header', 998);
		add_action('admin_notices', 'msp_editor_page_header', 999);
		add_action('add_meta_boxes', 'msp_add_editor_metaboxes', 9);

		if(msp_get_setting('saved_page') and current_user_can('manage_options')) {
			if(!msp_get_setting('allow_tracking')) {
				add_action('admin_print_footer_scripts', 'msp_admin_allow_tracking_notice');
			} else if(msp_get_setting('email_subscribe_notice') !== 'done') {
				add_action('admin_print_footer_scripts', 'msp_email_subscribe_notice');
			}
		}
	}
	add_action('save_post', 'msp_save_editor_metaboxes');
	add_filter('update_user_metadata', 'msp_override_save_metabox_order', 100, 5);
	add_filter('get_user_option_meta-box-order_page', 'msp_override_get_metabox_order', 100, 3);
}
add_action('msp_init', 'msp_editor_init');



/*---------------------------------------------------------*/
/* Editor Page Customizations                              */
/*---------------------------------------------------------*/

function msp_enqueue_editor_resources() {
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-widget');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('msp-editor-script', plugins_url('js/editor.js', dirname(__FILE__)), array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-sortable'));
	wp_enqueue_style('msp-editor-style', plugins_url('css/editor.css', dirname(__FILE__)));
}

function msp_override_editor_parent_file() {
	global $pagenow, $parent_file, $submenu_file;
	$parent_file = "myspeakingpage";
	$submenu_file = "myspeakingpage";
	return $parent_file;
}

function msp_editor_page_header() {
	?> <p>Tip: You can drag and drop the boxes on this page to change the order in which they appear on your speaking page.</p> <?php
}



/*---------------------------------------------------------*/
/* Metabox Order                                           */
/*---------------------------------------------------------*/

function msp_override_get_metabox_order($result, $option, $user) {
	global $pagenow;
	if(msp_is_editor_page() and msp_get_setting('meta_box_order')) {
		return msp_get_setting('meta_box_order');
	}
	return $result;
}

function msp_override_save_metabox_order($check, $object_id, $meta_key, $meta_value, $prev_value) {
	if($meta_key === 'meta-box-order_page' and strpos($meta_value['normal'], 'msp_action_image_metabox') !== false) {
		msp_update_setting('meta_box_order', $meta_value);
		return false;
	}
	return $check;
}



/*---------------------------------------------------------*/
/* Metaboxes                                               */
/*---------------------------------------------------------*/

function msp_add_editor_metaboxes() {
	global $pagenow, $_wp_post_type_features;
	if(msp_is_editor_page()) {
		unset($_wp_post_type_features['page']['editor']);
		add_action('post_submitbox_misc_actions', 'msp_pubish_metabox');
		add_meta_box('msp_information_metabox', 'Information', 'msp_information_metabox', null, 'side', 'high');
		add_meta_box('msp_book_to_speak_metabox', 'Book To Speak Button', 'msp_book_to_speak_metabox', null, 'side', 'high');
		add_meta_box('msp_action_image_metabox', 'Action Image / Promo Video', 'msp_action_image_metabox', null, 'normal', 'high');
		add_meta_box('msp_blurb_metabox', 'Blurb', 'msp_blurb_metabox', null, 'normal', 'high');
		add_meta_box('msp_headshots_metabox', 'Headshots', 'msp_headshots_metabox', null, 'normal', 'high');
		add_meta_box('msp_about_you_metabox', 'About You', 'msp_about_you_metabox', null, 'normal', 'high');
		add_meta_box('msp_videos_metabox', 'Videos', 'msp_videos_metabox', null, 'normal', 'high');
		add_meta_box('msp_testimonials_metabox', 'Testimonials', 'msp_testimonials_metabox', null, 'normal', 'high');
		add_meta_box('msp_topics_metabox', 'Speaking Topics', 'msp_topics_metabox', null, 'normal', 'high');
		add_meta_box('msp_audios_metabox', 'Audio Samples', 'msp_audios_metabox', null, 'normal', 'high');
		add_meta_box('msp_seo_metabox', 'Search Engine Optimization', 'msp_seo_metabox', null, 'normal', 'high');
		if(defined("MSE_VERSION")) {
			add_meta_box('msp_upcoming_events_metabox', 'Upcoming Events', 'msp_upcoming_events_metabox', null, 'normal', 'high');
			add_meta_box('msp_past_events_metabox', 'Past Events', 'msp_past_events_metabox', null, 'normal', 'high');
		}
	}
}

function msp_add_title_field($id, $default, $table_style = false) {
	global $post;
	$value = get_post_meta($post->ID, $id, true);
	if($table_style) {
		?>
		<table class="form-table" class="msp-section-title">
			<tr>
				<th><label for="<?php echo($id); ?>"><span>Section Title</span></label></th>
				<td><input type="text" name="<?php echo($id); ?>" id="<?php echo($id); ?>" value="<?php echo(empty($value) ? $default : $value); ?>" /></td>
			</tr>
		</table>
		<?php
	} else {
		?>
		<div class="msp-section-title">
			<label for="<?php echo($id); ?>"><span>Section Title:</span>
			<input type="text" name="<?php echo($id); ?>" id="<?php echo($id); ?>" value="<?php echo(empty($value) ? $default : $value); ?>" />
			</label>
		</div>
		<?php
	}
}

function msp_add_call_to_action_button($id) {
	global $post;
?>
	<div class="msp-call-to-action">
		<label><input type="checkbox" name="<?php echo($id); ?>" id="<?php echo($id); ?>" <?php checked(get_post_meta($post->ID, $id, true), true); ?> >
		Show Book to Speak button after this section?</label>
	</div>
<?php
}

function msp_pubish_metabox() {
	global $post;
	$value = get_post_meta($post->ID, 'msp_show_breadcrumbs', true);
	echo('<div class="misc-pub-section misc-pub-section-last"><label><input type="checkbox" '.checked($value, '1', false).' value="1" name="msp_show_breadcrumbs" /> Show breadcrumbs</label></div>');
}

function msp_information_metabox($post) {
?>
	<div class="msp-section-field">
		<label class="msp-section-field-title" for="msp_speaking_packet">Speaking Packet</label>
		<input type="text" id="msp_speaking_packet" name="msp_speaking_packet" value="<?php echo(get_post_meta($post->ID, "msp_speaking_packet", true)); ?>" />
		<input class="msp_upload_button button" data-upload-field="#msp_speaking_packet" type="button" value="Choose" />
		<p class="description">If you have a pdf speaker packet, upload it here.</p>
	</div>
<?php
}

function msp_book_to_speak_metabox($post) {
?>
	<div class="msp-section-field">
		<label class="msp-section-field-title" for="msp_book_to_speak_url">Button URL</label>
		<input type="text" name="msp_book_to_speak_url" id="msp_book_to_speak_url" value="<?php echo(get_post_meta($post->ID, "msp_book_to_speak_url", true)); ?>" style="width: 100%" />
		<p class="description">This is the URL for the contact form where people can book you to speak.</p>
	</div>
	<div class="msp-section-field">
		<label class="msp-section-field-title">Button Text</label>
		<?php $button_text = get_post_meta($post->ID, "msp_button_text", true); ?>
		<label><input type="radio" name="msp_button_text" value="0" <?php checked($button_text, 0); ?>>Invite Me To Speak</label><br>
		<label><input type="radio" name="msp_button_text" value="1" <?php checked($button_text, 1); ?>>Book Me To Speak</label><br>
		<label><input type="radio" name="msp_button_text" value="2" <?php checked($button_text, 2); ?>>Tell Us About Your Event</label><br>
		<input id="msp_button_text_custom_radio" type="radio" name="msp_button_text" value="3" <?php checked($button_text, 3); ?>><input type="text" name="msp_button_text_custom" id="msp_button_text_custom" value="<?php echo(get_post_meta($post->ID, "msp_button_text_custom", true)); ?>" size="15" onclick="jQuery('#msp_button_text_custom_radio').prop('checked', true);" />
	</div>
<?php
}

function msp_action_image_metabox($post) {
?>
	<table class="form-table">
		<tr>
			<th><label for="msp_action_type">Display Type</label></th>
			<td colspan="2">
				<?php $msp_action_type = get_post_meta($post->ID, "msp_action_type", true); ?>
				<?php if(empty($msp_action_type)) { $msp_action_type = 0; } ?>
				<input type="radio" name="msp_action_type" value="0" <?php checked($msp_action_type, 0); ?>>None&nbsp;&nbsp;&nbsp;
				<input type="radio" name="msp_action_type" value="1" <?php checked($msp_action_type, 1); ?>>Action Image&nbsp;&nbsp;&nbsp;
				<input type="radio" name="msp_action_type" value="2" <?php checked($msp_action_type, 2); ?>>Promo Video
			</td>
		</tr>
		<tr id="msp_action_image_container" <?php if($msp_action_type !== '1') { echo('style="display:none"'); } ?> >
			<th><label for="msp_action_image">Action Image</label></th>
			<td style="width:100px">
				<div class="msp-preview-area" id="msp_action_image_preview">
				<?php $action_image = get_post_meta($post->ID, "msp_action_image", true); ?>
				<?php if(!empty($action_image)) { echo('<img src="'.$action_image.'">'); } ?>
				</div>
			</td>
			<td>
				<input type="hidden" name="msp_action_image" id="msp_action_image" value="<?php echo($action_image); ?>" />
				<input class="msp_upload_button button" data-upload-field="#msp_action_image" data-preview-area="#msp_action_image_preview" type="button" value="Choose" />
				<p class="description">Ideally this is a photo of you speaking in front of an audience.</p>
			</td>
		</tr>
		<tr id="msp_action_video_container" <?php if($msp_action_type !== '2') { echo('style="display:none"'); } ?>>
			<th><label for="msp_action_image">Promo Video</label></th>
			<td colspan="2">
				<input type="text" id="msp_action_video" name="msp_action_video" value="<?php echo(get_post_meta($post->ID, "msp_action_video", true)); ?>" />
				<p class="description">Paste video url here.</p>
			</td>
		</tr>
	</table>
<?php
}

function msp_blurb_metabox($post) {
?>
	<table class="form-table">
		<tr>
			<th><label for="excerpt">Blurb</label></th>
			<td>
				<textarea rows="4" cols="60" name="excerpt"><?php echo($post->post_excerpt); ?></textarea><br>
				<p class="description">This is where you can share what is unique about you as a speaker and why they should pick you. Shoot for around 50 words for your blurb.</p>
			</td>
		</tr>
	</table>
<?php
	msp_add_call_to_action_button('msp_blurb_call_to_action');
}

function msp_headshots_metabox($post) {
	msp_add_title_field('msp_headshots_title', 'Headshots');
	$headshots = get_post_meta($post->ID, "msp_headshots", true);
	?>
		<div class="msp_headshots_metabox msp_list">
			<div class="button button-primary msp_list_item_adder">Add Headshot</div>
			<input type="hidden" name="msp_headshots" class="msp_list_data" value='<?php echo(str_replace('\'', '&#39;', json_encode($headshots))); ?>'>
			<div class="msp_list_items"></div>
			<p class="description">These should be high resolution images suitable for event promotional materials.</p>
		</div>
	<?php
	msp_add_call_to_action_button('msp_headshots_call_to_action');
}

function msp_about_you_metabox($post) {
	msp_add_title_field('msp_about_you_title', 'About You');
	wp_editor($post->post_content, 'content');
	echo('<p>This is where you post your bio and speaking introduction.</p>');
	msp_add_call_to_action_button('msp_about_you_call_to_action');
}

function msp_videos_metabox($post) {
	msp_add_title_field('msp_videos_title', 'Videos');
	$videos = get_post_meta($post->ID, "msp_videos", true);
	?>
		<div class="msp_videos_metabox msp_list">
			<div class="button button-primary msp_list_item_adder">Add Video</div>
			<input type="hidden" name="msp_videos" class="msp_list_data" value='<?php echo(str_replace('\'', '&#39;', json_encode($videos))); ?>'>
			<div class="msp_list_items"></div>
			<p class="description">Once you have uploaded your video to YouTube or Vimeo, paste the URL here</p>
		</div>
	<?php
	msp_add_call_to_action_button('msp_videos_call_to_action');
}

function msp_testimonials_metabox($post) {
	msp_add_title_field('msp_testimonials_title', 'What People Are Saying About You');
	$testimonials = get_post_meta($post->ID, "msp_testimonials", true);
	?>
		<div class="msp_testimonials_metabox msp_list">
			<div class="button button-primary msp_list_item_adder">Add Testimonial</div>
			<input type="hidden" name="msp_testimonials" class="msp_list_data" value='<?php echo(str_replace('\'', '&#39;', json_encode($testimonials))); ?>'>
			<div class="msp_list_items"></div>
			<p class="description">This is where you post endorsements from folks who have heard you speak in the past.</p>
		</div>
	<?php
	msp_add_call_to_action_button('msp_testimonials_call_to_action');
}

function msp_topics_metabox($post) {
	msp_add_title_field('msp_topics_title', 'Frequently Requested Topics');
	$topics = get_post_meta($post->ID, "msp_topics", true);
	?>
		<div class="msp_topics_metabox msp_list">
			<div class="button button-primary msp_list_item_adder">Add Topic</div>
			<input type="hidden" name="msp_topics" class="msp_list_data" value='<?php echo(str_replace('\'', '&#39;', json_encode($topics))); ?>'>
			<div class="msp_list_items"></div>
			<p class="description">Enter the topics that you typically speak about here.</p>
		</div>
	<?php
	msp_add_call_to_action_button('msp_topics_call_to_action');
}

function msp_audios_metabox($post) {
	msp_add_title_field('msp_audios_title', 'Audio Samples');
	$audios = get_post_meta($post->ID, "msp_audios", true);
	?>
		<div class="msp_audios_metabox msp_list">
			<div class="button button-primary msp_list_item_adder">Add Audio Sample</div>
			<input type="hidden" name="msp_audios" class="msp_list_data" value='<?php echo(str_replace('\'', '&#39;', json_encode($audios))); ?>'>
			<div class="msp_list_items"></div>
			<p class="description">Upload an mp3 of a talk or selection from a talk. You may also want to upload a radio or podcast interview.</p>
		</div>
	<?php
	msp_add_call_to_action_button('msp_audios_call_to_action');
}

function msp_upcoming_events_metabox($post) {
	msp_add_title_field('msp_upcoming_events_title', 'Upcoming Events', true);
?>
	<table class="form-table">
		<tr>
			<th><label for="msp_num_upcoming_events">Number of events to show</label></th>
			<td><input type="number" min="0" name="msp_num_upcoming_events" id="msp_num_upcoming_events" value="<?php echo(get_post_meta($post->ID, "msp_num_upcoming_events", true)); ?>" /></td>
		</tr>
	</table>
	<p class="description">Since you have MySpeakingEvents, you can easily show off your future events here. The purpose of these events is to build credibility by showing where you are speaking in the future.</p>
<?php
	msp_add_call_to_action_button('msp_upcoming_events_call_to_action');
}

function msp_past_events_metabox($post) {
	msp_add_title_field('msp_past_events_title', 'Past Events', true);
?>
	<table class="form-table">
		<tr>
			<th><label for="msp_num_past_events">Number of events to show</label></th>
			<td><input type="number" min="0" name="msp_num_past_events" id="msp_num_past_events" value="<?php echo(get_post_meta($post->ID, "msp_num_past_events", true)); ?>" /></td>
		</tr>
	</table>
	<p class="description">Since you have MySpeakingEvents, you can easily show off your past events here. The purpose of these events is to build credibility by showing where you have spoken in the past.</p>
<?php
	msp_add_call_to_action_button('msp_past_events_call_to_action');
}

function msp_seo_metabox($post) {
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			update_title = function() {
				left = 70-jQuery("#msp_seo_title").val().length;
				jQuery("#msp_seo_title-length").text(left);
				if(left < 0) {
					jQuery("#msp_seo_title-length").addClass("bad");
				} else {
					jQuery("#msp_seo_title-length").removeClass("bad");
				}
			}
			jQuery("#msp_seo_title").keydown(update_title).keyup(update_title).change(update_title);
			update_title();

			update_metadesc = function() {
				left = 156-jQuery("#msp_seo_metadesc").val().length;
				jQuery("#msp_seo_metadesc-length").text(left);
				if(left < 0) {
					jQuery("#msp_seo_metadesc-length").addClass("bad");
				} else {
					jQuery("#msp_seo_metadesc-length").removeClass("bad");
				}
			}
			jQuery("#msp_seo_metadesc").keydown(update_metadesc).keyup(update_metadesc).change(update_metadesc);
			update_metadesc();
		});
	</script>

	<table class="form-table msp_seo_metabox">
		<tbody>
			<tr>
				<th scope="row">
					<label for="msp_seo_title"><?php _e('SEO Title:', 'myspeakingpage'); ?></label>
				</th>
				<td>
					<input type="text" placeholder="" id="msp_seo_title" name="msp_seo_title" value="<?php echo(get_post_meta($post->ID, 'msp_seo_title', true)); ?>" class="large-text"><br>
					<p><?php _e('Title display in search engines is limited to 70 chars, <span id="msp_seo_title-length">70</span> chars left.', 'myspeakingpage'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="msp_seo_metadesc"><?php _e('Meta Description:', 'myspeakingpage'); ?></label></th>
				<td>
					<textarea class="large-text" rows="3" id="msp_seo_metadesc" name="msp_seo_metadesc"><?php echo(get_post_meta($post->ID, 'msp_seo_metadesc', true)); ?></textarea>
					<p><?php _e('The <code>meta</code> description will be limited to 156 chars, <span id="msp_seo_metadesc-length">156</span> chars left.', 'myspeakingpage'); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
<?php
}

function msp_save_editor_metaboxes($post_id) {
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }

	if($post_id == msp_get_setting('speaking_page')) {
		//Publish Box
		update_post_meta($post_id, 'msp_show_breadcrumbs', isset($_REQUEST['msp_show_breadcrumbs']));

		// Information Metabox
		if(isset($_REQUEST['msp_speaking_packet'])) { update_post_meta($post_id, 'msp_speaking_packet', $_REQUEST['msp_speaking_packet']); }

		// Book To Speak Info Metabox
		if(isset($_REQUEST['msp_book_to_speak_url'])) { update_post_meta($post_id, 'msp_book_to_speak_url', $_REQUEST['msp_book_to_speak_url']); }
		if(isset($_REQUEST['msp_button_text'])) { update_post_meta($post_id, 'msp_button_text', $_REQUEST['msp_button_text']); }
		if(isset($_REQUEST['msp_button_text_custom'])) { update_post_meta($post_id, 'msp_button_text_custom', $_REQUEST['msp_button_text_custom']); }

		// Action Image Metabox
		if(isset($_REQUEST['msp_action_type'])) { update_post_meta($post_id, 'msp_action_type', $_REQUEST['msp_action_type']); }
		if(isset($_REQUEST['msp_action_image'])) { update_post_meta($post_id, 'msp_action_image', $_REQUEST['msp_action_image']); }
		if(isset($_REQUEST['msp_action_video'])) { update_post_meta($post_id, 'msp_action_video', $_REQUEST['msp_action_video']); }

		// Headshots Metabox
		if(isset($_REQUEST['msp_headshots'])) { update_post_meta($post_id, 'msp_headshots', json_decode(str_replace('\\\\', '\\', str_replace('\"', '"', str_replace('\\\'', '\'', $_REQUEST['msp_headshots']))))); }

		// Video Metabox
		if(isset($_REQUEST['msp_videos'])) { update_post_meta($post_id, 'msp_videos', json_decode(str_replace('\\\\', '\\', str_replace('\"', '"', str_replace('\\\'', '\'', $_REQUEST['msp_videos']))))); }

		// Testimonials Metabox
		if(isset($_REQUEST['msp_testimonials'])) { update_post_meta($post_id, 'msp_testimonials', json_decode(str_replace('\\\\', '\\', str_replace('\"', '"', str_replace('\\\'', '\'', $_REQUEST['msp_testimonials']))))); }

		// Topics Metabox
		if(isset($_REQUEST['msp_topics'])) { update_post_meta($post_id, 'msp_topics', json_decode(str_replace('\\\\', '\\', str_replace('\"', '"', str_replace('\\\'', '\'', $_REQUEST['msp_topics']))))); }

		// Audios Metabox
		if(isset($_REQUEST['msp_audios'])) { update_post_meta($post_id, 'msp_audios', json_decode(str_replace('\\\\', '\\', str_replace('\"', '"', str_replace('\\\'', '\'', $_REQUEST['msp_audios']))))); }

		// Past Events Metabox
		if(isset($_REQUEST['msp_num_past_events'])) { update_post_meta($post_id, 'msp_num_past_events', max(0, $_REQUEST['msp_num_past_events'])); }
		if(isset($_REQUEST['msp_promote_past_events'])) { update_post_meta($post_id, 'msp_promote_past_events', $_REQUEST['msp_promote_past_events']); }

		// Upcoming Events Metabox
		if(isset($_REQUEST['msp_num_upcoming_events'])) { update_post_meta($post_id, 'msp_num_upcoming_events', max(0, $_REQUEST['msp_num_upcoming_events'])); }
		if(isset($_REQUEST['msp_promote_upcoming_events'])) { update_post_meta($post_id, 'msp_promote_upcoming_events', $_REQUEST['msp_promote_upcoming_events']); }

		// SEO Metabox
		if(isset($_REQUEST['msp_seo_title'])) { update_post_meta($post_id, "msp_seo_title", $_REQUEST['msp_seo_title']); }
		if(isset($_REQUEST['msp_seo_metadesc'])) { update_post_meta($post_id, "msp_seo_metadesc", $_REQUEST['msp_seo_metadesc']); }

		// Titles
		if(isset($_REQUEST['msp_headshots_title'])) { update_post_meta($post_id, 'msp_headshots_title', $_REQUEST['msp_headshots_title']); }
		if(isset($_REQUEST['msp_about_you_title'])) { update_post_meta($post_id, 'msp_about_you_title', $_REQUEST['msp_about_you_title']); }
		if(isset($_REQUEST['msp_videos_title'])) { update_post_meta($post_id, 'msp_videos_title', $_REQUEST['msp_videos_title']); }
		if(isset($_REQUEST['msp_testimonials_title'])) { update_post_meta($post_id, 'msp_testimonials_title', $_REQUEST['msp_testimonials_title']); }
		if(isset($_REQUEST['msp_topics_title'])) { update_post_meta($post_id, 'msp_topics_title', $_REQUEST['msp_topics_title']); }
		if(isset($_REQUEST['msp_audios_title'])) { update_post_meta($post_id, 'msp_audios_title', $_REQUEST['msp_audios_title']); }
		if(isset($_REQUEST['msp_past_events_title'])) { update_post_meta($post_id, 'msp_past_events_title', $_REQUEST['msp_past_events_title']); }
		if(isset($_REQUEST['msp_upcoming_events_title'])) { update_post_meta($post_id, 'msp_upcoming_events_title', $_REQUEST['msp_upcoming_events_title']); }

		// Call to Action buttons
		update_post_meta($post_id, 'msp_blurb_call_to_action', isset($_REQUEST['msp_blurb_call_to_action']));
		update_post_meta($post_id, 'msp_headshots_call_to_action', isset($_REQUEST['msp_headshots_call_to_action']));
		update_post_meta($post_id, 'msp_about_you_call_to_action', isset($_REQUEST['msp_about_you_call_to_action']));
		update_post_meta($post_id, 'msp_videos_call_to_action', isset($_REQUEST['msp_videos_call_to_action']));
		update_post_meta($post_id, 'msp_testimonials_call_to_action', isset($_REQUEST['msp_testimonials_call_to_action']));
		update_post_meta($post_id, 'msp_topics_call_to_action', isset($_REQUEST['msp_topics_call_to_action']));
		update_post_meta($post_id, 'msp_audios_call_to_action', isset($_REQUEST['msp_audios_call_to_action']));
		update_post_meta($post_id, 'msp_past_events_call_to_action', isset($_REQUEST['msp_past_events_call_to_action']));
		update_post_meta($post_id, 'msp_upcoming_events_call_to_action', isset($_REQUEST['msp_upcoming_events_call_to_action']));

		msp_update_setting('saved_page', true);
	}
}
