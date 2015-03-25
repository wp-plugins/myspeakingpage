<?php

function msp_frontend_init() {
	if(!is_admin()) {
		add_filter('the_content', 'msp_override_page_content', 100);
		add_action('wp', 'msp_override_page_title', -999);
		add_action('wp_enqueue_scripts', 'msp_wp_enqueue_scripts');
		add_action('add_admin_bar_menus', 'msp_override_admin_bar');
		add_action('msp_do_breadcrumbs', 'msp_do_breadcrumbs');
	}
	add_image_size('msp_headshot', 1000, 100, false);
}
add_action('msp_init', 'msp_frontend_init');

/*---------------------------------------------------------*/
/* Frontend                                                */
/*---------------------------------------------------------*/

function msp_wp_enqueue_scripts() {
	wp_enqueue_style('msp-frontend-style', plugins_url('css/frontend.css', dirname(__FILE__)));
}

function msp_override_admin_bar() {
	global $post;
	if(!empty($post) and $post->ID == msp_get_setting('speaking_page')) {
		remove_action('admin_bar_menu', 'wp_admin_bar_edit_menu', 80);
		add_action('admin_bar_menu', 'msp_admin_bar_edit_menu', 80);
	}
}

function msp_admin_bar_edit_menu($wp_admin_bar) {
	global $post;
	$wp_admin_bar->add_menu(array(
		'id' => 'edit',
		'title' => 'Edit Speaking Page',
		'href' => get_edit_post_link($post->ID)
	));
}

function msp_override_page_title($title) {
	global $wp_query;
	$post = $wp_query->post;

	if(!empty($post) and $post->ID == msp_get_setting('speaking_page')) {
		if(!empty($_REQUEST['msp_page']) and $_REQUEST['msp_page'] == 'testimonials') {
			$new_title = get_post_meta($post->ID, 'msp_testimonials_title', true);
		}
		if(!empty($_REQUEST['msp_page']) and $_REQUEST['msp_page'] == 'topics') {
			$new_title = get_post_meta($post->ID, 'msp_topics_title', true);
		}
	}
	if(!empty($new_title)) {
		$wp_query->post->post_title = $new_title;
		$wp_query->posts = array($wp_query->post);
		$GLOBALS['post'] = $wp_query->post;
		$GLOBALS['posts'] = &$wp_query->posts;
	}
}

function msp_do_breadcrumbs() {
	global $post;
	$delimiter = ' &gt; ';
	$output = '';

	if(get_post_meta($post->ID, 'msp_show_breadcrumbs', true)) {
		$output .= '<a href="'.get_site_url().'">'.__('Home', 'mysp').'</a>';
		$output .= $delimiter.'<a href="'.get_permalink().'">'.$post->post_title.'</a>';
	}

	echo(apply_filters('msp_filter_breadcrumbs', empty($output) ? '' : '<div class="msp-breadcrumbs">'.$output.'</div>'));
}

function msp_override_page_content($content) {
	global $post;
	if(!empty($post) and $post->ID == msp_get_setting('speaking_page')) {
		if(!empty($_REQUEST['msp_page']) and $_REQUEST['msp_page'] == 'testimonials') {
			$output .= '<div class="myspeakingpage">';

			$testimonials = get_post_meta($post->ID, 'msp_testimonials', true);
			if(is_array($testimonials)) {
				$output .= '<section class="msp-testimonials-section">';
				$output .= '<div class="msp-testimonials">';
				foreach($testimonials as $testimonial) {
					$output .= '<div class="msp-testimonial">';
					$image = wp_get_attachment_image_src($testimonial->image->id, 'msp_headshot');
					if(!empty($image)) { $output .= '<img src="'.$image[0].'">'; }
					$output .= '	<div class="msp-testimonial-content">'.$testimonial->testimonial.'</div>';
					$output .= '	<div class="msp-testimonial-meta">';
					$output .= '		<div class="msp-testimonial-name">'.(empty($testimonial->name_url) ? '' : '<a href="'.$testimonial->name_url.'">').$testimonial->name.(empty($testimonial->name_url) ? '' : '</a>').'</div>';
					$output .= '		<div class="msp-testimonial-title">'.$testimonial->title.'</div>';
					$output .= '		<div class="msp-testimonial-organization">'.(empty($testimonial->organization_url) ? '' : '<a href="'.$testimonial->organization_url.'">').$testimonial->organization.(empty($testimonial->organization_url) ? '' : '</a>').'</div>';
					$output .= '		<div class="msp-testimonial-location">'.$testimonial->location.'</div>';
					$output .= '	</div>';
					$output .= '	<div style="clear:both"></div>';
					$output .= '</div>';
				}
				$output .= '</div>';
				$output .= '</section>';
			}

			$output .= '</div>';

			return $output;
		}
		if(!empty($_REQUEST['msp_page']) and $_REQUEST['msp_page'] == 'topics') {
			$output .= '<div class="myspeakingpage">';

			$topics = get_post_meta($post->ID, 'msp_topics', true);
			if(is_array($topics)) {
				$output .= '<section class="msp-topics-section">';
				$output .= '<div class="msp-topics">';
				foreach($topics as $topic) {
					$output .= '<div class="msp-topic">';
					$image = wp_get_attachment_image_src($topic->image->id, 'msp_headshot');
					if(!empty($image)) { $output .= '<img src="'.$image[0].'">'; }
					$output .= '	<div class="msp-topic-title">'.$topic->title.'</div>';
					$output .= '	<div class="msp-topic-content">'.$topic->blurb.'</div>';
					$output .= '</div>';
				}
				$output .= '</div>';
				$output .= '</section>';
			}

			$output .= '</div>';

			return $output;
		}

		$meta_box_order = msp_get_setting('meta_box_order');
		if(!empty($meta_box_order) and !empty($meta_box_order['normal'])) {
			$sections = explode(",", $meta_box_order['normal']);
		} else {
			$sections = array(
				'msp_action_image_metabox',
				'msp_blurb_metabox',
				'msp_headshots_metabox',
				'msp_about_you_metabox',
				'msp_videos_metabox',
				'msp_testimonials_metabox',
				'msp_topics_metabox',
				'msp_audios_metabox',
				'msp_upcoming_events_metabox',
				'msp_past_events_metabox',
			);
		}

		$button_text_num = get_post_meta($post->ID, 'msp_button_text', true);
		if($button_text_num == 1) {
			$button_text = 'Book Me To Speak';
		} else if($button_text_num == 2) {
			$button_text = 'Tell Us About Your Event';
		} else if($button_text_num == 3) {
			$button_text = get_post_meta($post->ID, 'msp_button_text_custom', true);
		} else {
			$button_text = 'Invite Me To Speak';
		}

		$output = '';
		$output .= '<div class="myspeakingpage">';

		do_action('msp_do_breadcrumbs');

		foreach($sections as $section) {
			if($section == 'msp_action_image_metabox') {
				if(get_post_meta($post->ID, 'msp_action_type', true) === '2') {
					if(get_post_meta($post->ID, 'msp_action_video', true)) {
						global $wp_embed;
						$output .= '<div class="msp-action-video">'.$wp_embed->shortcode(array('width'=>500, 'height'=>500), get_post_meta($post->ID, 'msp_action_video', true)).'</div>';
					}
				} else if(get_post_meta($post->ID, 'msp_action_type', true) === '1') {
					if(get_post_meta($post->ID, 'msp_action_image', true)) {
						$output .= '<div class="msp-action-image"><img src="'.get_post_meta($post->ID, 'msp_action_image', true).'"></div>';
					}
				} else { continue; }

				$speaking_packet = get_post_meta($post->ID, 'msp_speaking_packet', true);

				$output .= '<div class="msp-buttons">';
				$output .= '	<a href="'.get_post_meta($post->ID, 'msp_book_to_speak_url', true).'" class="msp-button">'.$button_text.'</a>';
				if(empty($speaking_packet)) {
					$output .= '<a href="http://www.printfriendly.com/print/?source=site&url='.get_permalink().'" class="msp-button">Print this page</a>';
				} else {
					$output .= '<a href="'.$speaking_packet.'" class="msp-button">Download Speaker Packet</a>';
				}
				$output .= '</div>';
			} else if($section == 'msp_blurb_metabox') {
				$output .= '<section class="msp-content-section">';
				remove_filter('the_content', 'msp_override_page_content', 100);
				$output .= get_the_excerpt();
				add_filter('the_content', 'msp_override_page_content', 100);
				$output .= '</section>';

				if(get_post_meta($post->ID, 'msp_blurb_call_to_action', true)) {
					$output .= '<div class="msp-call-to-action">';
					$output .= '	<a href="'.get_post_meta($post->ID, 'msp_book_to_speak_url', true).'" class="msp-button">'.$button_text.'</a>';
					$output .= '</div>';
				}
			} else if($section == 'msp_headshots_metabox') {
				$headshots = get_post_meta($post->ID, 'msp_headshots', true);
				if(is_array($headshots) and count($headshots) > 0) {
					$output .= '<section class="msp-headshots-section">';
					$output .= '<h2>'.get_post_meta($post->ID, 'msp_headshots_title', true).'</h2>';
					$output .= '<div>Click to download a high resolution version.</div>';
					$output .= '<div class="msp-headshots">';
					foreach($headshots as $headshot) {
						$image = wp_get_attachment_image_src($headshot->image->id, 'msp_headshot');
						$image_full = wp_get_attachment_image_src($headshot->image->id, 'full');
						if($image) {
							$output .= '<div class="msp-headshot">';
							$output .= '	<a href="'.$image_full[0].'" download><img src="'.$image[0].'"></a>';
							$output .= '</div>';
						}
					}
					$output .= '<div style="clear:both"></div>';
					$output .= '</div>';
					$output .= '</section>';

					if(get_post_meta($post->ID, 'msp_headshots_call_to_action', true)) {
						$output .= '<div class="msp-call-to-action">';
						$output .= '	<a href="'.get_post_meta($post->ID, 'msp_book_to_speak_url', true).'" class="msp-button">'.$button_text.'</a>';
						$output .= '</div>';
					}
				}
			} else if($section == 'msp_about_you_metabox') {
				$output .= '<section class="msp-content-section">';
				$output .= '<h2>'.get_post_meta($post->ID, 'msp_about_you_title', true).'</h2>';
				remove_filter('the_content', 'msp_override_page_content', 100);
				$output .= apply_filters('the_content', get_the_content());
				add_filter('the_content', 'msp_override_page_content', 100);
				$output .= '</section>';

				if(get_post_meta($post->ID, 'msp_about_you_call_to_action', true)) {
					$output .= '<div class="msp-call-to-action">';
					$output .= '	<a href="'.get_post_meta($post->ID, 'msp_book_to_speak_url', true).'" class="msp-button">'.$button_text.'</a>';
					$output .= '</div>';
				}
			} else if($section == 'msp_videos_metabox') {
				$videos = get_post_meta($post->ID, 'msp_videos', true);
				if(is_array($videos) and count($videos) > 0) {
					$output .= '<section class="msp-videos-section">';
					$output .= '<h2>'.get_post_meta($post->ID, 'msp_videos_title', true).'</h2>';
					$output .= '<div class="msp-videos">';
					foreach($videos as $video) {
						global $wp_embed;
						$output .= '<div class="msp-video">';
						$output .= '<div class="msp-video-title">'.$video->title.'</div>';
						$output .= '<div class="msp-video-content">'.$wp_embed->shortcode(array(), $video->video).'</div>';
						$output .= '</div>';
					}
					$output .= '</div>';
					$output .= '</section>';

					if(get_post_meta($post->ID, 'msp_videos_call_to_action', true)) {
						$output .= '<div class="msp-call-to-action">';
						$output .= '	<a href="'.get_post_meta($post->ID, 'msp_book_to_speak_url', true).'" class="msp-button">'.$button_text.'</a>';
						$output .= '</div>';
					}
				}
			} else if($section == 'msp_testimonials_metabox') {
				$testimonials = get_post_meta($post->ID, 'msp_testimonials', true);
				if(is_array($testimonials) and count($testimonials) > 0) {
					$output .= '<section class="msp-testimonials-section">';
					$output .= '<h2>'.get_post_meta($post->ID, 'msp_testimonials_title', true).'</h2>';
					$output .= '<div class="msp-testimonials">';
					$limit = 0;
					foreach($testimonials as $testimonial) {
						$output .= '<div class="msp-testimonial">';
						$image = wp_get_attachment_image_src($testimonial->image->id, 'msp_headshot');
						if(!empty($image)) { $output .= '<img src="'.$image[0].'">'; }
						$output .= '	<div class="msp-testimonial-content">'.$testimonial->testimonial.'</div>';
						$output .= '	<div class="msp-testimonial-meta">';
						$output .= '		<div class="msp-testimonial-name">'.(empty($testimonial->name_url) ? '' : '<a href="'.$testimonial->name_url.'">').$testimonial->name.(empty($testimonial->name_url) ? '' : '</a>').'</div>';
						$output .= '		<div class="msp-testimonial-title">'.$testimonial->title.'</div>';
						$output .= '		<div class="msp-testimonial-organization">'.(empty($testimonial->organization_url) ? '' : '<a href="'.$testimonial->organization_url.'">').$testimonial->organization.(empty($testimonial->organization_url) ? '' : '</a>').'</div>';
						$output .= '		<div class="msp-testimonial-location">'.$testimonial->location.'</div>';
						$output .= '	</div>';
						$output .= '	<div style="clear:both"></div>';
						$output .= '</div>';
						$limit++; if($limit >= 3) { break; }
					}
					$output .= '</div>';
					if(count($testimonials) > 3) {
						$testimonials_page_url = strpos(get_permalink(), '?') === false ? get_permalink().'?msp_page=testimonials' : get_permalink().'&msp_page=testimonials';
						$output .= '<a href="'.$testimonials_page_url.'" class="msp-more-page">More Testimonials</a>';
					}
					$output .= '</section>';

					if(get_post_meta($post->ID, 'msp_testimonials_call_to_action', true)) {
						$output .= '<div class="msp-call-to-action">';
						$output .= '	<a href="'.get_post_meta($post->ID, 'msp_book_to_speak_url', true).'" class="msp-button">'.$button_text.'</a>';
						$output .= '</div>';
					}
				}
			} else if($section == 'msp_topics_metabox') {
				$topics = get_post_meta($post->ID, 'msp_topics', true);
				if(is_array($topics) and count($topics) > 0) {
					$output .= '<section class="msp-topics-section">';
					$output .= '<h2>'.get_post_meta($post->ID, 'msp_topics_title', true).'</h2>';
					$output .= '<div class="msp-topics">';
					$limit = 0;
					foreach($topics as $topic) {
						$output .= '<div class="msp-topic">';
						$image = wp_get_attachment_image_src($topic->image->id, 'msp_headshot');
						if(!empty($image)) { $output .= '<img src="'.$image[0].'">'; }
						$output .= '	<div class="msp-topic-title">'.$topic->title.'</div>';
						$output .= '	<div class="msp-topic-content">'.$topic->blurb.'</div>';
						$output .= '	<div style="clear:both"></div>';
						$output .= '</div>';
						$limit++; if($limit >= 3) { break; }
					}
					$output .= '</div>';
					if(count($topics) > 3) {
						$topics_page_url = strpos(get_permalink(), '?') === false ? get_permalink().'?msp_page=topics' : get_permalink().'&msp_page=topics';
						$output .= '<a href="'.$topics_page_url.'" class="msp-more-page">More Topics</a>';
					}
					$output .= '</section>';

					if(get_post_meta($post->ID, 'msp_topics_call_to_action', true)) {
						$output .= '<div class="msp-call-to-action">';
						$output .= '	<a href="'.get_post_meta($post->ID, 'msp_book_to_speak_url', true).'" class="msp-button">'.$button_text.'</a>';
						$output .= '</div>';
					}
				}
			} else if($section == 'msp_audios_metabox') {
				$audios = get_post_meta($post->ID, 'msp_audios', true);
				if(is_array($audios) and count($audios) > 0) {
					$output .= '<section class="msp-audios-section">';
					$output .= '<h2>'.get_post_meta($post->ID, 'msp_audios_title', true).'</h2>';
					$output .= '<div class="msp-audios">';
					foreach($audios as $audio) {
						$output .= '<div class="msp-audio">';
						$output .= '<div class="msp-audio-title">'.$audio->title.'</div>';
						$output .= wp_audio_shortcode(array('src' => $audio->audio, 'loop' => '', 'autoplay' => '', 'preload' => 'none'));
						$output .= '</div>';
					}
					$output .= '</div>';
					$output .= '</section>';

					if(get_post_meta($post->ID, 'msp_audios_call_to_action', true)) {
						$output .= '<div class="msp-call-to-action">';
						$output .= '	<a href="'.get_post_meta($post->ID, 'msp_book_to_speak_url', true).'" class="msp-button">'.$button_text.'</a>';
						$output .= '</div>';
					}
				}
			} else if($section == 'msp_upcoming_events_metabox') {
				if(defined("MSE_VERSION")) {
					$num_upcoming_events = get_post_meta($post->ID, 'msp_num_upcoming_events', true);
					if(!empty($num_upcoming_events)) {
						$output .= '<section class="msp-upcoming-events-section">';
						$output .= '<h2>'.get_post_meta($post->ID, 'msp_upcoming_events_title', true).'</h2>';
						$events = new WP_Query(array('post_type' => 'mse_event', 'orderby' => 'meta_value_num', 'posts_per_page' => $num_upcoming_events, 'meta_query' => array(
							array(
								'key' => 'mse_time_start',
								'value' => time()-172800,
								'type' => 'numeric',
								'compare' => '>',
							)
						)));
						if(is_array($events->posts) and count($events->posts) > 0) {
							foreach($events->posts as $event) {
								$output .= '<div class="msp-event">';
								$output .= '<a href="'.get_permalink($event->ID).'" class="msp-event-title">'.$event->post_title.'</a>';
								$output .= ' - '.mse_get_event_location($event->ID).', '.mse_get_event_time_start($event->ID, 'm/d/Y');
								$output .= '</div>';
							}
							if(mse_get_setting('upcoming_events_page') != 0 and get_page(mse_get_setting('upcoming_events_page'))) {
								$output .= '<a href="'.get_permalink(mse_get_setting('upcoming_events_page')).'" class="msp-more-page">All Upcoming Events</a>';
							}
						} else {
							$output .= '<div class="msp-no-events">No events to list</div>';
						}
						$output .= '</section>';

						if(get_post_meta($post->ID, 'msp_upcoming_events_call_to_action', true)) {
							$output .= '<div class="msp-call-to-action">';
							$output .= '	<a href="'.get_post_meta($post->ID, 'msp_book_to_speak_url', true).'" class="msp-button">'.$button_text.'</a>';
							$output .= '</div>';
						}
					}
				}
			} else if($section == 'msp_past_events_metabox') {
				if(defined("MSE_VERSION")) {
					$num_past_events = get_post_meta($post->ID, 'msp_num_past_events', true);
					if(!empty($num_past_events)) {
						$output .= '<section class="msp-past-events-section">';
						$output .= '<h2>'.get_post_meta($post->ID, 'msp_past_events_title', true).'</h2>';
						$events = new WP_Query(array('post_type' => 'mse_event', 'orderby' => 'meta_value_num', 'posts_per_page' => $num_past_events, 'meta_query' => array(
							array(
								'key' => 'mse_time_start',
								'value' => time()-172800,
								'type' => 'numeric',
								'compare' => '<',
							)
						)));
						if(is_array($events->posts) and count($events->posts) > 0) {
							foreach($events->posts as $event) {
								$output .= '<div class="msp-event">';
								$output .= '<a href="'.get_permalink($event->ID).'" class="msp-event-title">'.$event->post_title.'</a>';
								$output .= ' - '.mse_get_event_location($event->ID).', '.mse_get_event_time_start($event->ID, 'm/d/Y');
								$output .= '</div>';
							}
							if(mse_get_setting('past_events_page') != 0 and get_page(mse_get_setting('past_events_page'))) {
								$output .= '<a href="'.get_permalink(mse_get_setting('past_events_page')).'" class="msp-more-page">All Past Events</a>';
							}
						} else {
							$output .= '<div class="msp-no-events">No events to list</div>';
						}
						$output .= '</section>';

						if(get_post_meta($post->ID, 'msp_past_events_call_to_action', true)) {
							$output .= '<div class="msp-call-to-action">';
							$output .= '	<a href="'.get_post_meta($post->ID, 'msp_book_to_speak_url', true).'" class="msp-button">'.$button_text.'</a>';
							$output .= '</div>';
						}
					}
				}
			}
		}

		$output .= '<div class="msp-call-to-action final">';
		$output .= '<a href="'.get_post_meta($post->ID, 'msp_book_to_speak_url', true).'" class="msp-button big">'.$button_text.'</a>';
		$output .= '</div>';

		$output .= '</div>';

		return $output;
	}

	return $content;
}
