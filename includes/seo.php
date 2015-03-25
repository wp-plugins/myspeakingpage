<?php

function msp_seo_init() {
	if(!is_admin()) {
		if(defined('WPSEO_FILE')) {
			//WP SEO Integration
			add_action('wpseo_opengraph', 'msp_add_wpseo_opengraph_image', 15);
		} else {
			//Custom SEO overrides
			add_filter('wp_title', 'msp_seo_wp_title', 999);
			add_action('wp_head', 'msp_seo_add_metadesc');
			add_action('wp_head', 'msp_seo_add_opengraph');
		}
	}
}
add_action('msp_init', 'msp_seo_init');



/*---------------------------------------------------------*/
/* SEO Functions                                           */
/*---------------------------------------------------------*/

function msp_add_wpseo_opengraph_image() {
	global $msp_taxonomy_query, $post;
	if(msp_is_speaking_page()) {
		$image = get_post_meta($post->ID, 'msp_action_image', true);
	}

	if(!empty($image)) {
		echo("<meta property='og:image' content='".esc_url($image)."'/>\n");
		return true;
	}
}

function msp_seo_title($post_id = 0) {
	$title = '';

	$post = get_post($post_id);
	if($post) {
		$seo_title = get_post_meta($post->ID, 'msp_seo_title', true);
		if(empty($seo_title)) {
			$title = get_the_title()." ";
		} else {
			$title = $seo_title." ";
		}
	}

	return $title;
}

function msp_seo_metadesc($post_id = 0) {
	$metadesc = '';

	$post = get_post($post_id);
	if($post) {
		$seo_metadesc = get_post_meta($post->ID, 'msp_seo_metadesc', true);
		if(empty($seo_metadesc)) {
			$metadesc = strip_tags($post->post_excerpt);
		} else {
			$metadesc = $seo_metadesc;
		}
	}

	return htmlentities($metadesc);
}

function msp_seo_wp_title($title) {
	if(msp_is_speaking_page()) {
		$new_title = msp_seo_title();
		if(substr($title, 0, 7) == "<title>") { $new_title = '<title>'.$new_title.'</title>'; }
		$title = $new_title;
	}

	return $title;
}

function msp_seo_add_metadesc() {
	if(msp_is_speaking_page()) {
		$metadesc = msp_seo_metadesc();
		if($metadesc) {
			echo('<meta name="description" content="'.$metadesc."\"/>\n");
		}
	}
}

function msp_seo_add_opengraph() {
	global $post;
	$tags = array();

	if(msp_is_speaking_page()) {
		$tags['og:type'] = 'website';
		$tags['og:title'] = msp_seo_title();
		$tags['og:description'] = msp_seo_metadesc();
		$tags['og:url'] = esc_url(get_permalink());
		$tags['og:site_name'] = get_bloginfo('name');
		$image = get_post_meta($post->ID, 'msp_action_image', true);
		if($image) { $tags['og:image'] = esc_url($image); }
	}

	foreach($tags as $tag => $content) {
		echo('<meta property="'.$tag.'" content="'.$content.'"/>');
	}
}
