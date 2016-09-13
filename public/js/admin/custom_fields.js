var CustField = {
	input_type: 'text',//default
	animspeed: 200,
	url_target: null,
	
	init: function(){
		CustField.input_type = $('#custom_field_type_selector')[0].value;
		$('#custom_field_type_selector').change(function(){
			CustField.input_type = this.value;
		});
		$('#add_new_custom_field').click(function(){
			if (!CustField.input_type) return false;
			var position = $('#custom_fields_cont').find('fieldset').length+1;
			var prename = 'custom_field[' + position + ']';
			var selectedFieldset = $('.all_custom_field_templates .custom_' + CustField.input_type + '_field_cont').clone();
			
			selectedFieldset.find('input.field_type').attr('name', prename+'[input_type]');
			selectedFieldset.find('input.field_position').attr('name', prename+'[position]');
			selectedFieldset.find('input.field_key').attr('name', prename+'[field_key]').prop("required", true);
			selectedFieldset.find('input.field_name').attr('name', prename+'[field_name]').prop("required", true);
			switch (CustField.input_type){
				case 'text':
				case 'html':
					selectedFieldset.find('.field_value').attr('name', prename+'[field_value]');
					break;
				case 'link':
					selectedFieldset.find('.field_value.link_href').attr('name', prename+'[field_value][href]').prop("required", true);
					selectedFieldset.find('.field_value.link_title').attr('name', prename+'[field_value][title]');
					selectedFieldset.find('.field_value.link_text').attr('name', prename+'[field_value][text]').prop("required", true);
					selectedFieldset.find('.field_value.link_class').attr('name', prename+'[field_value][class]');
					selectedFieldset.find('.field_value.link_target').attr('name', prename+'[field_value][target]');
					break;
				case 'image':
					selectedFieldset.find('.field_value.image_src').attr('name', prename+'[field_value][src]').prop("required", true);
					selectedFieldset.find('.field_value.image_title').attr('name', prename+'[field_value][title]');
					selectedFieldset.find('.field_value.image_alt').attr('name', prename+'[field_value][alt]');
					selectedFieldset.find('.field_value.image_height').attr('name', prename+'[field_value][height]');
					selectedFieldset.find('.field_value.image_width').attr('name', prename+'[field_value][width]');
					selectedFieldset.find('.field_value.image_class').attr('name', prename+'[field_value][class]');
					break;
				case 'media':
					selectedFieldset.find('.field_value.media_src').attr('name', prename+'[field_value][src]').prop("required", true);
					selectedFieldset.find('.field_value.media_type').attr('name', prename+'[field_value][type]').prop("required", true);
					break;
			}
			
			selectedFieldset.css({height:0,opacity:0});
			$('#custom_fields_cont').append(selectedFieldset.animate({height:'100px'}, CustField.animspeed, function(){
				$(this).animate({height:'100%',opacity:1}, CustField.animspeed);
			}));
			CustField.bindTriggerEvts();
			if (CustField.input_type == 'html'){
				var id = 'custom_field_textarea_' + position;
				CustField.bindCKE(selectedFieldset.find('.field_value').attr('id', id)[0]);
			}
		});
		CustField.bindTriggerEvts();
		$('#frm_custom_fields').submit(function(evt){
			// Replace all non alphanumeric characters by underscore for field keys
			$('.field_key').each(function(){
				this.value = this.value.replace(/\W/g, '_');
			});
			// Check if there are identical field keys
			if (CustField.hasDuplicateKeys()){
				$('#duplicate_err_msg').show();
				setTimeout(function(){
					$('#duplicate_err_msg').fadeOut();
				}, 5000);
				evt.preventDefault();
				return false;
			}
		});
		/* Image picker*/
		$('.custom_field_picture_selector .close').click(function(){
			$(this).parent().fadeOut();
		});
		$('.image_library_result').click(function(){
			CustField.url_target.val(this.src);
			$(this).closest('.custom_field_picture_selector').fadeOut();
		});
	},
	
	enableDisableBtns: function(){
		if ($('#custom_fields_cont').find('fieldset').length)
			$('#save_custom_fields').show();
		else
			$('#save_custom_fields').hide();
		
		$('button.moveup, button.movedown').removeClass('blur');
		$('#custom_fields_cont fieldset.custom_fieldset:first button.moveup').addClass('blur');
		$('#custom_fields_cont fieldset.custom_fieldset:last button.movedown').addClass('blur');
	},
	
	bindTriggerEvts: function(){
		$('.remove.custom_field').click(function(){
			if (!confirm(this.value)) return false;
			$(this).closest('fieldset').animate({height:0,opacity:0}, 500, function(){
				$(this).remove();
				CustField.resetAllPositions();
			});
		});
		$('.moveup.custom_field').click(function(){
			var current = $(this).closest('fieldset');
			var previous = current.prev();
			if (!previous.length) return false;
			var textarea = current.find('textarea.field_value');
			current.fadeOut(CustField.animspeed, function(){
				if (textarea.length){
					CustField.removeCKE(textarea[0]);
				}
				current.insertBefore(previous).fadeIn(CustField.animspeed, function(){
					CustField.resetAllPositions();
					if (textarea.length){
						CustField.bindCKE(textarea[0]);
					}
				})
			});
		});
		$('.movedown.custom_field').click(function(){
			var current = $(this).closest('fieldset');
			var next = current.next();
			if (!next.length) return false;
			var textarea = current.find('textarea.field_value');
			current.fadeOut(CustField.animspeed, function(){
				if (textarea.length){
					CustField.removeCKE(textarea[0]);
				}
				current.insertAfter(next).fadeIn(CustField.animspeed, function(){
					CustField.resetAllPositions();
					if (textarea.length){
						CustField.bindCKE(textarea[0]);
					}
				})
			});
		});
		
		/* Image picker*/
		$('#custom_fieldset .image_picker').click(function(){
			var container = $('#custom_fieldset .custom_field_picture_selector');
			CustField.url_target = $(this).parent().find('input.image_src');
			container.fadeIn();
		});
		CustField.resetAllPositions();
	},
	
	resetAllPositions: function(){
		var i = 0;
		$('#custom_fields_cont').find('fieldset').each(function(){
			i++;
			$(this).find('input.field_position').val(i);
		});
		CustField.enableDisableBtns();
	},
	
	hasDuplicateKeys: function(){
		var duplicate = false;
		var matched = current = '';
		$('#custom_fields_cont .field_key').each(function(){
			matched = this.value;
			current = this.name;
			$('#custom_fields_cont .field_key').each(function(){
				if (current == this.name) return;
				if (this.value == matched){
					duplicate = true;
					$(this).focus();
				}
			});			
		});
		return duplicate;
	},
	/**
	 * The following functions try to fix a CKE bug when a HTML node is moved usin "insertBefore" or "after"
	 * We need to reinitialize the textarea with CKE (i.e. removing from CKE and then, rebound)
	 */
	bindCKE: function(obj){
		try{
			CKEDITOR.replace(obj.id,
				{
					uiColor : '#EAEDFF',
					allowedContent : true,
					width : '98%',
					height : '150px',
					toolbarGroups : [
		             	{ name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },
		             	{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		             	{ name: 'insert' },
		             	{ name: 'tools' },
		             	{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		             	{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
		             	{ name: 'links' },
		             	{ name: 'colors' },
		             	{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		             	{ name: 'styles' },
		             	{ name: 'others' }
		             ],
				}
			);
		}
		catch(exc){
			//alert(exc.message);
		}
	},
	
	removeCKE: function(obj){
		if (typeof CKEDITOR.instances[obj.id] != 'undefined'){
			//Get the Wysiwyg data and put it in the textarea
			$('#'+obj.id).html(CKEDITOR.instances[obj.id].getData());
			//Remove CKE instance and its HTML object
			CKEDITOR.remove(CKEDITOR.instances[obj.id]);
			$('#cke_' + obj.id).remove();
		}
	}
};

$(function(){
	CustField.init();
	/* CKE */
	$('#custom_fields_cont .ckeditor').each(function(){
		CustField.bindCKE(this);
	});
});