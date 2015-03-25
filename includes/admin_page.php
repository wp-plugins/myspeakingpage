<?php

function msp_admin_init() {
	if(is_admin()) {
		add_action('admin_menu', 'msp_add_admin_pages', 9);
		add_action('admin_enqueue_scripts', 'msp_enqueue_admin_resources');
		add_action('admin_init', 'msp_admin_redirect', 20);
	}
}
add_action('msp_init', 'msp_admin_init');



/*---------------------------------------------------------*/
/* Admin Header                                            */
/*---------------------------------------------------------*/

function msp_admin_header() {
	add_thickbox();
	?>
		<div class="msp-header">
			<div class="msp-title">
				<img src="<?php echo(plugins_url('img/logo.png', dirname(__FILE__))); ?>" class="msp-logo">
				<h2 class="msp-title-text">My<span class="msp-title-speaking">Speaking</span>Page</h2>
				<a class="thickbox msp-get-more-info" href="#TB_inline&inlineId=msp-more-info&width=700&height=550">Video</a>
			</div>

			<div id="msp-more-info">
				<div class="msp-more-info">
					<div class="msp-header">
						<div class="msp-title">
							<img src="<?php echo(plugins_url('img/logo.png', dirname(__FILE__))); ?>" class="msp-logo">
							<h2 class="msp-title-text">My<span class="msp-title-speaking">Speaking</span>Page</h2>
						</div>
					</div>
					<div class="msp-video-container"><iframe width="640" height="360" src="http://player.vimeo.com/video/109852029" frameborder="0" allowfullscreen></iframe></div>
					<h1>MySpeakingPage Features</h1>
					<p>MySpeakingPage helps you get more gigs by showing off your expertise, testimonials, and more.</p>
					<h3>Customizable "Book To Speak" Button</h3>
					<p>Make it easy for event planners to book you with MySpeakingPage's customizable "call to action" button. You pick the text ("Invite Liz to Speak") and where the button goes (www.example.com/form).</p>
					<h3>YouTube &amp; Vimeo Integration</h3>
					<p>Show off samples of your speaking in style. Don't have a promo video for your speaking yet? No problem. You can easily substitute a photo (an action image of you speaking in front of an audience works best).</p>
					<h3>Speakers Bureau Integration</h3>
					<p>Do you have an agency who handles your bookings for you? No problem. MySpeakingPage can integrate with your speakers bureau website.</p>
					<h3>High Resolution Headshots &amp; Bio</h3>
					<p>The two things every event planner asks for you to email them are your photo and your bio. MySpeakingPage puts those often-requested items on a silver platter. No email required. You can even show off high resolution versions of your headshots that may not fit in an email.</p>
					<h3>Great Looking Testimonials</h3>
					<p>MySpeakingPage makes it easy to add testimonials. It also makes those testimonials look great with optional photos, titles and more.</p>
					<h3>All-Around Easy Audio Samples</h3>
					<p>Do you have some recordings of talks you would like to share? Just click "add audio sample" and MySpeakingPage will handle the rest for you. MSP creates a special audio player where  your visitors can listen with a click. Easy for you. Easy for your visitors.</p>
					<h3>MySpeakingEvents Integration</h3>
					<p>If you have MySpeakingEvents installed, you can easily show off both upcoming events and past events right there on your speaking page.</p>
				</div>
			</div>
		</div>
	<?php
}



/*---------------------------------------------------------*/
/* Admin Pages                                             */
/*---------------------------------------------------------*/

function msp_add_admin_pages() {
	add_menu_page('MySpeakingPage', 'MySpeakingPage', 'edit_posts', 'myspeakingpage', 'msp_render_admin_page', 'dashicons-microphone', '10.73');
}

function msp_render_admin_page() {
	global $msp_speaking_slug_used;
	?>
	<div class='wrap msp-admin-page'>
		<?php msp_admin_header(); ?>

		<?php if($msp_speaking_slug_used) { ?>
			<?php $page = get_page_by_path('speaking'); ?>
			<p>It appears you already have a page titled "<?php echo($page->post_title); ?>" that is using the "speaking" url on your site. For SEO reasons, it's highly reccomended that you use the "speaking" url for your MySpeakingPage.</p>
			<div class='msp-setup-options'>
				<div class='msp-i-dont-care'>
					<a href="<?php echo(admin_url('admin.php?page=myspeakingpage&msp_new_page=1&i_dont_care=1')); ?>" id="submit" class="button button-primary">I don't care, just use a different url</a>
				</div>
				<div class='msp-or'>-or-</div>
				<div class='msp-use-speaking-page'>
					<a href="<?php echo(admin_url('admin.php?page=myspeakingpage&msp_new_page=1&use_speaking_page=1')); ?>" id="submit" class="button button-primary">Use the page that currently has the "speaking" url for MySpeakingPage</a>
				</div>
				<div class='msp-or'>-or-</div>
				<div class='msp-use-speaking-page'>
					<form id="msp_settings_form" method="post" action="<?php echo(admin_url('admin.php?page=myspeakingpage&msp_new_page=1')); ?>">
						<input type="text" name="msp_change_slug" id="msp_change_slug" value="speaking">
						<input type="submit" name="save_settings" id="submit" class="button button-primary" value="<?php _e('Change that page\'s url so MySpeakingPage can use the \'speaking\' url', 'myspeakingpage'); ?>">
					</form>
				</div>
			</div>
		<?php } else { ?>
			<?php if(msp_speaking_page_status() == 'new') { ?>
				<p>Welcome to MySpeakingPage! You can get started creating your new speaking information page by creating a new page to use with MySpeakingPage, or choosing an existing page.</p>
			<?php } else if(msp_speaking_page_status() == 'error') { ?>
				<p>There seems to be a problem with your existing speaking information page. You can solve this by creating a new speaking page or choosing an existing page to use as your speaking page.</p>
			<?php } ?>
			<div class='msp-setup-options'>
				<div class='msp-new-page'>
					<a href="<?php echo(admin_url('admin.php?page=myspeakingpage&msp_new_page=1')); ?>" id="submit" class="button button-primary">Click here to create a new speaking page</a>
				</div>
				<div class='msp-or'>-or-</div>
				<div class='msp-choose-page'>
					<form id="msp_settings_form" method="post" action="<?php echo(admin_url('admin.php?page=myspeakingpage')); ?>">
						<select name="msp_use_page" id="msp_use_page">
							<option value="0" <?php selected(msp_get_setting('speaking_page'), 0); ?> > -- Choose One -- </option>
							<?php foreach(get_pages() as $page) { ?>
								<option value="<?php echo($page->ID); ?>" <?php selected(msp_get_setting('speaking_page'), $page->ID); ?> ><?php echo($page->post_title); ?></option>
							<?php } ?>
						</select>
						<input type="submit" name="save_settings" id="submit" class="button button-primary" value="<?php _e('Use this page', 'myspeakingpage'); ?>">
					</form>
				</div>
			</div>
		<?php } ?>
	</div>
	<?php
}

function msp_enqueue_admin_resources() {
	wp_enqueue_style('msp-admin-style', plugins_url('css/admin.css', dirname(__FILE__)));
}

function msp_admin_redirect() {
	global $pagenow, $msp_speaking_slug_used;
	if(msp_is_admin_page()) {
		if(msp_speaking_page_status() == 'ok') {
			wp_redirect(admin_url('post.php?post='.msp_get_setting('speaking_page').'&action=edit')); exit;
		} else {
			if(isset($_REQUEST['msp_new_page'])) {
				$old_page = get_page_by_path('speaking');
				if(!empty($old_page)) {
					if(isset($_REQUEST['i_dont_care'])) {
						$ignore_slug = true;
					} else if(isset($_REQUEST['use_speaking_page'])) {
						msp_update_setting('speaking_page', $old_page->ID);
						wp_update_post(array('ID' => $old_page->ID, 'post_status' => 'publish'));
						$skip_making_page = true;
					} else if(isset($_REQUEST['msp_change_slug'])) {
						$new_slug = sanitize_title($_REQUEST['msp_change_slug']);
						if($new_slug == 'speaking' or empty($new_slug)) {
							$skip_making_page = true;
							$msp_speaking_slug_used = true;
						} else {
							wp_update_post(array('ID' => $old_page->ID, 'post_name' => $new_slug));
						}
					} else {
						$skip_making_page = true;
						$msp_speaking_slug_used = true;
					}
				}

				if(empty($skip_making_page)) {
					$page_array = array(
						'post_title' => get_bloginfo('name').' Speaking Info',
						'post_status' => 'publish',
						'post_type' => 'page',
						'post_author' => get_current_user_id()
					);
					if(empty($ignore_slug)) { $page_array['post_name'] = 'speaking'; }
					$new_page = wp_insert_post($page_array);
					msp_update_setting('speaking_page', $new_page);
				}

				if(msp_speaking_page_status() == 'ok') {
					wp_redirect(admin_url('post.php?post='.msp_get_setting('speaking_page').'&action=edit')); exit;
				}
			} else if(isset($_REQUEST['msp_use_page'])) {
				msp_update_setting('speaking_page', $_REQUEST['msp_use_page']);
				if(msp_speaking_page_status() == 'ok') {
					wp_redirect(admin_url('post.php?post='.msp_get_setting('speaking_page').'&action=edit')); exit;
				}
			}
		}
	} else {
		if(msp_speaking_page_status() == 'new') {
			add_action('admin_notices', 'msp_new_admin_notice');
		} else if(msp_speaking_page_status() == 'error') {
			add_action('admin_notices', 'msp_error_admin_notice');
		}
	}
}



/*---------------------------------------------------------*/
/* Admin Notices                                           */
/*---------------------------------------------------------*/

function msp_new_admin_notice() {
?>
	<div class="msp-admin-notice">
		<h4><strong>Welcome to MySpeakingPage</strong> &#8211; You're ready to start creating your speaking page!</h4>
		<a class="notice-button primary" href="<?php echo(admin_url('admin.php?page=myspeakingpage')); ?>">Get Started</a>
	</div>
<?php
}

function msp_error_admin_notice() {
?>
	<div class="msp-admin-notice">
		<h4><strong>Uh Oh</strong> &#8211; There seems to be something wrong with your speaking page.</h4>
		<a class="notice-button primary" href="<?php echo(admin_url('admin.php?page=myspeakingpage')); ?>">Fix MySpeakingPage</a>
	</div>
<?php
}

function msp_admin_allow_tracking_notice() {
	$content  = '<h3>'.__('Help improve MySpeakingPage', 'myspeakingpage').'</h3>';
	$content .= '<p>'.__('You can help make MySpeakingPage even better and easier to use by allowing it to gather anonymous statistics about how you use the plugin.', 'myspeakingpage').'</p>';
	$content .= '<div class="msp-pointer-buttons wp-pointer-buttons">';
	$content .= '<a id="msp-pointer-yes" class="button-primary" style="float:left">'.htmlspecialchars(__("Let's do it!", 'myspeakingpage'), ENT_QUOTES).'</a>';
	$content .= '<a id="msp-pointer-no" class="button-secondary">'.htmlspecialchars(__("I'd Rather Not", 'myspeakingpage'), ENT_QUOTES).'</a>';
	$content .= '</div>';

	?>
	<script type="text/javascript">
		var msp_email_subscribe_pointer_options = {
			pointerClass: 'msp-allow-tracking-pointer',
			content: '<?php echo($content); ?>',
			position: {edge: 'top', align: 'center'},
			buttons: function() {}
		};

		jQuery(document).ready(function () {
			jQuery('#wpadminbar').pointer(msp_email_subscribe_pointer_options).pointer('open');

			jQuery('#msp-pointer-yes').click(function() {
				jQuery.post(ajaxurl,
					{
						action: 'msp_allow_tracking_notice',
						allow_tracking: 'yes',
					},
					function(response) {
						jQuery('.msp-allow-tracking-pointer .wp-pointer-content').html(response);
					}
				);
			});

			jQuery('#msp-pointer-no').click(function() {
				jQuery.post(ajaxurl, {action: 'msp_allow_tracking_notice', allow_tracking: 'no'});
				jQuery('#wpadminbar').pointer('close');
			});

			jQuery('.msp-allow-tracking-pointer').on('click', '#msp-pointer-close', function() {
				jQuery('#wpadminbar').pointer('close');
			});
		});
	</script>
	<?php
}

function msp_allow_tracking_notice_ajax() {
	if(empty($_REQUEST['allow_tracking'])) { die(); }
	if($_REQUEST['allow_tracking'] === 'yes') {
		msp_update_setting('allow_tracking', 'yes');
		msp_track_event('tracking_allowed', true);
		msp_send_tracking_data();

		$content  = '<h3>'.__('Help improve MySpeakingPage', 'myspeakingpage').'</h3>';
		$content .= '<p>'.__('Thanks! You\'re the best!', 'myspeakingpage').'</p>';
		$content .= '<div class="msp-pointer-buttons wp-pointer-buttons">';
		$content .= '<a id="msp-pointer-close" class="button-secondary">'.__('Close', 'myspeakingpage').'</a>';
		$content .= '</div>';
		echo($content);
	} else {
		msp_update_setting('allow_tracking', 'no');
	}
	die();
}
add_action('wp_ajax_msp_allow_tracking_notice', 'msp_allow_tracking_notice_ajax');

function msp_email_subscribe_notice() {
	$current_user = wp_get_current_user();
	$email = $current_user->user_email;

	$content  = '<h3>'.__('Get Branding Tips, Marketing Advice and Plugin Updates', 'myspeakingpage').'</h3>';
	$content .= '<p>'.__('Join over 7,000 other speakers and authors on the Author Media&#39;s award winning newsletter. AuthorMedia.com has been frequently by Writer&#39;s Digest as one of the most helpful websites for authors. You can unsubscribe at anytime with just one click.', 'myspeakingpage').'</p>';
	$content .= '<p>'.'<input type="text" name="msp-pointer-email" id="msp-pointer-email" autocapitalize="off" autocorrect="off" placeholder="you@example.com" value="'.$email.'" style="width: 100%">'.'</p>';
	$content .= '<div class="msp-pointer-buttons wp-pointer-buttons">';
	$content .= '<a id="msp-pointer-yes" class="button-primary" style="float:left">'.htmlspecialchars(__("Let's do it!", 'myspeakingpage'), ENT_QUOTES).'</a>';
	$content .= '<a id="msp-pointer-no" class="button-secondary">'.__('No, thanks', 'myspeakingpage').'</a>';
	$content .= '</div>';

	?>
	<script type="text/javascript">
		var msp_email_subscribe_pointer_options = {
			pointerClass: 'msp-email-subscribe-notice',
			content: '<?php echo($content); ?>',
			position: {edge: 'top', align: 'center'},
			buttons: function() {}
		};

		function msp_email_subscribe_pointer_subscribe() {
			if(!/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/.test(jQuery('#msp-pointer-email').val())) {
				jQuery('#msp-pointer-email').addClass('error').focus();
			} else {
				jQuery('#msp-pointer-yes').attr('disabled', 'disabled');
				jQuery('#msp-pointer-no').attr('disabled', 'disabled');
				jQuery('#msp-pointer-email').attr('disabled', 'disabled');
				jQuery.post(ajaxurl,
					{
						action: 'msp_email_subscribe_notice',
						subscribe: 'yes',
						email: jQuery('#msp-pointer-email').val()
					},
					function(response) {
						jQuery('.msp-email-subscribe-notice .wp-pointer-content').html(response);
					}
				);
			}
		}

		jQuery(document).ready(function () {
			jQuery('#wpadminbar').pointer(msp_email_subscribe_pointer_options).pointer('open');

			jQuery('#msp-pointer-yes').click(function() {
				msp_email_subscribe_pointer_subscribe();
			});

			jQuery('#msp-pointer-email').keypress(function(event) {
				 if(event.which == 13) {
					msp_email_subscribe_pointer_subscribe();
				 }
			});

			jQuery('#msp-pointer-no').click(function() {
				jQuery.post(ajaxurl, {action: 'msp_email_subscribe_notice', subscribe: 'no'}, function(r) { console.log(r); });
				jQuery('#wpadminbar').pointer('close');
			});

			jQuery('.msp-email-subscribe-notice').on('click', '#msp-pointer-close', function() {
				jQuery('#wpadminbar').pointer('close');
			});
		});
	</script>
	<?php
}

function msp_email_subscribe_notice_ajax() {
	if(empty($_REQUEST['subscribe'])) { die(); }
	if($_REQUEST['subscribe'] === 'yes') {
		$email = $_POST['email'];
		wp_remote_post('http://AuthorMedia.us1.list-manage1.com/subscribe/post', array(
			'method' => 'POST',
			'body' => array(
				'u' => 'b7358f48fe541fe61acdf747b',
				'id' => '6b5a675fcf',
				'MERGE0' => $email,
				'MERGE1' => '',
				'MERGE3' => '',
				'group[3045][4194304]' => 'on',
				'b_b7358f48fe541fe61acdf747b_6b5a675fcf' => ''
			)
		));

		$content  = '<h3>'.__('Get Branding Tips, Marketing Advice and Plugin Updates', 'myspeakingpage').'</h3>';
		$content .= '<p>'.__('Thank you for subscribing! Please check your inbox for a confirmation letter.', 'myspeakingpage').'</p>';
		$content .= '<div class="msp-pointer-buttons wp-pointer-buttons">';

		$email_title = '';
		$email_link = '';
		if(strpos($email , '@yahoo') !== false) {
			$email_title = __('Go to Yahoo! Mail', 'myspeakingpage');
			$email_link = 'https://mail.yahoo.com/';
		} else if(strpos($email, '@hotmail') !== false) {
			$email_title = __('Go to Hotmail', 'myspeakingpage');
			$email_link = 'https://www.hotmail.com/';
		} else if(strpos($email, '@gmail') !== false) {
			$email_title = __('Go to Gmail', 'myspeakingpage');
			$email_link = 'https://mail.google.com/';
		} else if(strpos($email, '@aol') !== false) {
			$email_title = __('Go to AOL Mail', 'myspeakingpage');
			$email_link = 'https://mail.aol.com/';
		}
		if(!empty($email_title)) {
			$content .= '<a class="button-primary" style="float:left" href="'.$email_link.'" target="_blank">'.$email_title.'</a>';
		}

		$content .= '<a id="msp-pointer-close" class="button-secondary">'.__('Close', 'myspeakingpage').'</a>';
		$content .= '</div>';
		echo($content);
	}
	msp_update_setting('email_subscribe_notice', 'done');
	die();
}
add_action('wp_ajax_msp_email_subscribe_notice', 'msp_email_subscribe_notice_ajax');



/*---------------------------------------------------------*/
/* WP101 Help                                              */
/*---------------------------------------------------------*/

add_filter('wp101_get_custom_help_topics', 'msp_add_wp101_help');
function msp_add_wp101_help($videos) {
	$videos["msp-overview"] = array("title" => "MySpeakingPage Overview", "content" => '<iframe width="640" height="360" src="http://player.vimeo.com/video/109852029" frameborder="0" allowfullscreen></iframe>');
	return $videos;
}
