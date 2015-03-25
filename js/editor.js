// API Key Feedback
jQuery(document).ready(function() {
	jQuery('#msp_api_key_refresh').click(function(e) {
		if(!jQuery('#msp_api_key_refresh').attr('disabled')) {
			jQuery('#msp_api_key_refresh').attr('disabled', 'disabled');
			jQuery.post(ajaxurl,
				{
					action: 'msp_api_key_refresh',
					api_key: jQuery('#msp_api_key').val(),
				},
				function(response) {
					jQuery('#msp_api_key_refresh').removeAttr('disabled');
					jQuery(".msp_api_key_feedback").html(response);
				}
			);
		}
		return false;
	});

	jQuery("#msp_button_color").spectrum();
});

function msp_make_uploader(button, upload_field, desired_data, preview_area, title) {
	var upload_dialog;

	var title = typeof title === 'undefined' ? (typeof button.attr('data-title') === 'undefined' ? 'Choose File' : button.attr('data-title')) : title;
	var desired_data = typeof desired_data === 'undefined' ? (typeof button.attr('data-desired-data') === 'undefined' ? 'url' : button.attr('data-desired-data')) : desired_data;
	var upload_field = typeof upload_field === 'undefined' ? jQuery(button.attr('data-upload-field')) : upload_field;
	var preview_area = typeof preview_area === 'undefined' ? (typeof button.attr('data-preview-area') === 'undefined' ? null : button.attr('data-preview-area')) : preview_area;

	button.click(function(event) {
		event.preventDefault();

		if(upload_dialog) { upload_dialog.open(); return; }

		upload_dialog = wp.media.frames.file_frame = wp.media({ title: title, button: { text: "Select" }, multiple: false });

		upload_dialog.on('select', function() {
			attachment = upload_dialog.state().get('selection').first().toJSON();
			upload_field.val(attachment[desired_data]).trigger('change');
			if(preview_area !== null) {
				if(attachment.type == 'image') {
					var url = attachment.url;
					if(attachment.sizes['thumbnail']) {
						url = attachment.sizes['thumbnail'].url;
					}
					jQuery(preview_area).html('<img src="'+url+'">');
				}
			}
		});

		upload_dialog.open();
	});
};

jQuery(document).ready(function() {

	jQuery('.msp_upload_button').each(function(i, e) {
		msp_make_uploader(jQuery(e));
	});

	//List Functions

	function msp_new_list_item(list) {
		src = '';
		src += '<div class="msp_list_item">';
		src += '	<div class="msp_list_item_header">';
		src += '		<button class="msp_list_item_remover button">Remove</button>';
		if(list.options.default_title !== '') { src += '<input type="text" class="msp_list_item_title" value="'+list.options.default_title+'" autocomplete="off">'; }
		src += '		<div style="clear:both"></div>';
		src += '	</div>';
		src += '	<div class="msp_list_item_content">';

		for(field in list.fields) {
			src += '<div class="msp_list_item_field">';
			field_data = list.fields[field];
			if(field_data.type == 'text') {
				src += '<label>'+field_data.title+': <input type="text" class="msp_list_field_'+field+'" value="" autocomplete="off"></label>';
			} else if(field_data.type == 'textarea') {
				src += '<label>'+field_data.title+'<br><textarea class="msp_list_field_'+field+'"></textarea>';
			} else if(field_data.type == 'uploader') {
				if(field_data.title) { src += '<label>'+field_data.title+':</label>'; }
				src += '<input type="text" class="msp_list_field_'+field+'" value="" />';
				src += '<input class="msp_upload_button button" type="button" value="Choose" />';
			} else if(field_data.type == 'image_uploader') {
				if(field_data.title) { src += '<label>'+field_data.title+':</label>'; }
				src += '<div class="msp-preview-area msp_list_field_'+field+'_preview"></div>';
				src += '<input type="hidden" class="msp_list_field_'+field+'" value="" />';
				src += '<input class="msp_upload_button button" type="button" value="Choose" />';
			}
			src += '</div>';
		}

		src += '	</div>';
		src += '</div>';
		new_item = jQuery(src);

		for(field in list.fields) {
			field_data = list.fields[field];
			if(field_data.type == 'uploader') {
				upload_field = new_item.find('.msp_list_field_'+field);
				msp_make_uploader(upload_field.parent().find('.msp_upload_button'), upload_field, 'url');
			} else if(field_data.type == 'image_uploader') {
				upload_field = new_item.find('.msp_list_field_'+field);
				preview_area = new_item.find('.msp_list_field_'+field+'_preview');
				msp_make_uploader(upload_field.parent().find('.msp_upload_button'), upload_field, 'id', preview_area);
			}
		}

		return new_item;
	}

	function msp_load_list(list) {
		var items = JSON.parse(list.element.find(".msp_list_data").val());
		for(var i = items.length - 1; i >= 0; i--) {
			var element = msp_new_list_item(list);

			for(field in list.fields) {
				field_data = list.fields[field];
				if(!items[i][field]) { continue; }
				if(field_data.type === 'image_uploader') {
					if(items[i][field].url) {
						element.find('.msp_list_field_'+field+'_preview').html('<img src="'+items[i][field].url+'">');
					}
					element.find('.msp_list_field_'+field).val(items[i][field].id);
				} else {
					element.find('.msp_list_field_'+field).val(items[i][field]);
				}
			}

			if(typeof items[i]['title'] !== 'undefined') { element.find('.msp_list_item_title').val(items[i]['title']); }

			list.element.find(".msp_list_items").prepend(element);
		};
	}

	function msp_save_list(list) {
		var items = [];
		list.element.find(".msp_list_items .msp_list_item").each(function(i, e) {
			var element = jQuery(e);
			var new_item = {}

			for(field in list.fields) {
				field_data = list.fields[field];
				if(field_data.type === 'image_uploader') {
					var new_subitem = {}
					if(element.find('.msp_list_field_'+field+'_preview img').length > 0) {
						new_subitem.url = element.find('.msp_list_field_'+field+'_preview img').attr('src');
					}
					new_subitem.id = element.find('.msp_list_field_'+field).val();
					new_item[field] = new_subitem;
				} else {
					new_item[field] = element.find('.msp_list_field_'+field).val();
				}
			}

			title_element = element.find('.msp_list_item_title');
			if(title_element.length > 0) { new_item['title'] = title_element.val(); }

			items.push(new_item);
		});
		list.element.find(".msp_list_data").val(JSON.stringify(items));
	}

	function msp_listify(list) {
		if(typeof list.element === 'undefined' || list.element.length < 1) { return; }

		list.options = jQuery.extend({}, {default_title: ''}, list.options);

		list.element.find(".msp_list_item_adder").click(function() { list.element.find('.msp_list_items').prepend(msp_new_list_item(list)); });

		msp_load_list(list);

		jQuery('input[type="submit"]').click(function() { msp_save_list(list); });
	}

	jQuery(".msp_list_items").sortable({cancel: ".msp_list_item_content,.msp_list_item_title,.msp_list_item_remover"});

	jQuery(".msp_list").on("click", ".msp_list_item_remover", function() {
		jQuery(this).parents('.msp_list_item').remove();
	});

	// Lists

	msp_listify({
		element: jQuery('.msp_headshots_metabox'),
		fields: {
			'image' : { 'type': 'image_uploader' }
		}
	});

	msp_listify({
		element: jQuery('.msp_testimonials_metabox'),
		fields: {
			'image' : { 'type': 'image_uploader', 'title' : 'Image' },
			'testimonial' : { 'type': 'textarea', 'title' : 'Testimonial' },
			'name' : { 'type': 'text', 'title' : 'Name' },
			'name_url' : { 'type': 'text', 'title' : 'Name URL' },
			'title' : { 'type': 'text', 'title' : 'Title' },
			'organization' : { 'type': 'text', 'title' : 'Organization' },
			'organization_url' : { 'type': 'text', 'title' : 'Organization URL' },
			'location' : { 'type': 'text', 'title' : 'Location' }
		}
	});

	msp_listify({
		element: jQuery('.msp_topics_metabox'),
		fields: {
			'image' : { 'type': 'image_uploader', 'title' : 'Image' },
			'blurb' : { 'type': 'textarea', 'title' : 'Blurb' }
		},
		options: { 'default_title': 'New Topic' }
	});

	msp_listify({
		element: jQuery('.msp_audios_metabox'),
		fields: {
			'audio' : { 'type': 'uploader', 'title' : 'Audio' }
		},
		options: { 'default_title': 'New Audio' }
	});

	msp_listify({
		element: jQuery('.msp_videos_metabox'),
		fields: {
			'video' : { 'type': 'text', 'title' : 'Video URL' }
		},
		options: { 'default_title': 'New Video' }
	});

	jQuery("input[type='radio'][name='msp_action_type']").change(function() {
		var type = jQuery("input[type='radio'][name='msp_action_type']:checked").val();
		if(type == 1) {
			jQuery("#msp_action_image_container").show();
			jQuery("#msp_action_video_container").hide();
		} else if(type == 2) {
			jQuery("#msp_action_image_container").hide();
			jQuery("#msp_action_video_container").show();
		} else {
			jQuery("#msp_action_image_container").hide();
			jQuery("#msp_action_video_container").hide();
		}
	});
});