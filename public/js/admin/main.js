/**
 * This class is not coded intending to be re-used in any case. That is particular for the auto-completion of images.
 * @param object trigger	The input type text box on which we'll bind the event onkeyup for auto-completion
 * @param object target		The container that will receive the HTML result (the list of images)
 * @param bool	 completeUrl
 */
BoN.FileAutoComplete = function(trigger, target, completeUrl){
	var randID = 'autores_'+Math.random();
	this.ajaxContainerTpl = '<div id="' + randID + '" class="autocompleteresult" style="position:absolute;z-index:10000;"></div>';
		
	/* This layer is used to enable the user to close the auto complete container when he clicks outside */
	if ($('.autocompletelayer').length < 1){
		this.layer = $('<div class="autocompletelayer" style="position:absolute;display:none;z-index:999;top:0;left:0;"></div>');
		this.layer.css('background', 'rgba(150, 150, 150, .05)');
		$('body').append(this.layer);
	}
	else{
		this.layer = $('.autocompletelayer');
	}
	
	/* Declare properties */
	this.inputObj = $(trigger);
	this.inputObj.attr('autocomplete', 'off');
	this.inputId = this.inputObj.attr('id');
	this.hiddenObj = $('#hidden_'+this.inputId);
	this.autoId = 'auto_' + this.inputId;
	this.resultContainer = $(target);
	this.json = null;
	this.completeUrl = completeUrl;
	
	/* Declare functions */
	//Ajax: get Json data and build HTML list
	BoN.FileAutoComplete.prototype.ajax = function(obj){
		var This = this;
		$.get(WEB_ROOT + '/ajax/json?model=cms&class=Files&method=get&search=' + obj.value.toString().trim(), function(json) {
			if (typeof json.result != 'undefined'){
				This.json = json.result;
				var ul = '<ul class="ul_autocomplete">';
				var list = '';
				var classIter = 'iter';
				for (var i in This.json){
					if (This.json[i].mime_type.indexOf('image') < 0){
						continue;
					}
					classIter = (classIter=='iter') ? '' : 'iter';
					list += '<li class="li_autocomplete ' + classIter + '">' +
						'<a class="a_autocomplete">' +
							'<input type="hidden" value="' + This.json[i].fid + '" />' +
							'<img class="img_autocomplete" src="' + WEB_ROOT + This.json[i].path + '"/>' +
							'<span class="span_autocomplete">' + This.json[i].filename + '</span>' +
						'</a></li>';
				}
				if (!list) return false;
				ul += list + '</ul>';
				
				var newResult = $(This.ajaxContainerTpl);
				newResult.html(ul);
				$(This.resultContainer).html(newResult);
				
				This.show();
				This.bindA();
			}
		});
	};
	//Bind procedures on <a> click (element chosen)
	BoN.FileAutoComplete.prototype.bindA = function(){
		var This = this;
		$('.a_autocomplete').click(function(e){
			e.preventDefault();
			if (This.completeUrl)
				This.inputObj.val($(this).find('img').attr('src'));
			else
				This.inputObj.val($(this).find('span').text());
			This.hiddenObj.val($(this).find('input').val());
			This.hide();
		});
	};
	//Display the containers (the layer too)
	BoN.FileAutoComplete.prototype.show = function(){
		var This = this;
		This.resultContainer.show();
		This.layer.height($('body').height()).width($('body').width()).show();
	};
	//Check if the value in the text box corresponds to a registered value (a file name)
	BoN.FileAutoComplete.prototype.checkFid = function(){
		var This = this;
		var value = This.inputObj.val().toString().trim();
		var isIn = false;
		if (value)
		for (var i in This.json){
			if (This.json[i].filename==value)
				isIn = true;
		}
		if (!isIn)
			This.hiddenObj.val('');
	};
	BoN.FileAutoComplete.prototype.clear = function(){
		var This = this;
		This.hiddenObj.val('');
		This.hide();
		return false;
	};
	
	var This = this;
	//Bind to events
	//Hide the containers (the layer too)
	BoN.FileAutoComplete.prototype.hide = function(){
		$('.autocompleteresult').hide();
		This.layer.hide();
		This.checkFid();
	};
	this.layer.click(This.hide);
	this.inputObj.keyup(function(e){
		e.preventDefault();
		if (typeof this.value == 'undefined' || this.value.toString().trim()==''){
			return This.clear();
		}
		if (e.keyCode!=13 && e.which!=13){
			This.ajax(this);
		}
		else
			return This.hide();
		This.checkFid();
		return false;
	});
	this.inputObj.focus(function(){
		if (typeof this.value == 'undefined' || this.value.toString().trim()==''){
			return This.clear();
		}
		This.ajax(this);
		This.checkFid();
	});
};

BoN.deleteArchive = function(ahid){
	$.post(
		WEB_ROOT + '/ajax/json?model=cms&class=article_history&method=delete',
		{
			ahid:ahid
		},
		function(json){
			if (!json || typeof json.error != 'undefined'){
				if (typeof json.error != 'undefined'){
					return alert(json.error);
				}
			}
			else if (typeof json.result != 'undefined' && json.result == true){
				return $('.ahid_'+json.ahid).fadeOut('slow', function(){$(this).remove();});
			}
			alert('Something wrong happened');
		}
	);
};

$(function(){
	//Init Auto Complete
	if ($('.autocomplete').length>0){
		$('.autocomplete').each(function(){
			new BoN.FileAutoComplete(this, $('.' + this.id));
		});
	}
	
	/* Body content highlights */
	if ($('#article_history tbody tr').length > 0){
		/* On click archive row, this will preview the body content into a highlighter */
		$('body').append('<div id="bonBodyHighlighter" class="bonHighlighter"><span class="close"></span><table></table></div>');
		$('#bonBodyHighlighter table')
			.append('<tr><th>Preview</th><th>HTML</th></tr>')
			.append('<tr class="content"></tr>');
		$('#bonBodyHighlighter table .content')
			.append('<td id="bodyHighlightLeft" class="bodyHighlight"><div class="article-content"></div></td>')
			.append('<td id="bodyHighlightRight" class="bodyHighlight"></td>');
		$('#bonBodyHighlighter .close').click(function(){
			$('#bonBodyHighlighter').fadeOut('fast');
		});
		$('#article_history tbody tr').click(function(){
			var txtContent = $(this).find('.body_text_content').html();
			var htmlContent= $(this).find('.body_html_content').html();
			$('#bodyHighlightLeft .article-content').html(htmlContent);
			$('#bodyHighlightRight').html(txtContent);
			$('#bonBodyHighlighter table').css({'margin-top': 0});
			$('#bonBodyHighlighter').fadeIn('fast', function(){
				var imgHeight = $('#bonBodyHighlighter table').height();
				var winHeight = $(window).height();
				var marginTop = (winHeight-imgHeight)/2;
				if (marginTop<0) marginTop = 0;
				$('#bonBodyHighlighter table').animate({'margin-top': marginTop+'px'}, 100);
			});
		});
		/* Bind delete archive to del buttons */
		$('#article_history button.delete').click(function(e){
			e.preventDefault();
			if (confirm('This will delete Archive ID ' + this.value + ".\nContinue anyway ?")){
				BoN.deleteArchive(this.value);
			}
			return false;
		});
	}
	
	/**
	 * Form validation
	 */
	$('button[type="submit"].validate,input[type="submit"].validate').click(function(e){
		var form = $(this).closest("form");
		//Check mandatory fields
		$(form).find('.mandatory').each(function(i, elt){
			if (!elt.value.trim()){
				$(elt).addClass('error');
				$('label[for="'+ elt.id +'"]').addClass('error');
			}
			else{
				$(elt).removeClass('error');
				$('label[for="'+ elt.id +'"]').removeClass('error');
			}
		});
		//Check passwords match
		if ($('#password').hasClass('mandatory') &&
				(
					$('#password').val().toString().trim() != $('#chk_pwd').val().toString().trim() ||
					(!$('#password').val() || !$('#chk_pwd').val())
				)
			){
			$('#password').	addClass('error');
			$('#chk_pwd').	addClass('error');
			$('label[for="password"]').	addClass('error');
			$('label[for="chk_pwd"]').	addClass('error');
			$('#pwd_error_msg').removeClass('hidden');
		}
		else{
			$('#password').	removeClass('error');
			$('#chk_pwd').	removeClass('error');			
			$('label[for="password"]').	removeClass('error');
			$('label[for="chk_pwd"]').	removeClass('error');
			$('#pwd_error_msg').addClass('hidden');
		}
		if ($('#email').length){
			if (!$('#email').val().isEmail()){
				$('#email').addClass('error');
				$('label[for="email"]').addClass('error');
				$('#email_error_msg').	removeClass('hidden');
			}
			else{
				$('#email').removeClass('error');
				$('label[for="email"]').removeClass('error');
				$('#email_error_msg').	addClass('hidden');
			}
		}
		if ($(form).find('.error').length){
			$(form).find('.error').get(0).focus();
			return false;
		}
		else
			return true;
	});
	
	/* Into "system" admin page: add a new param field for admin page rules */
	$('button.add_new_param_field').click(function(){
		var field = $(this).parent().find('input.text').first().clone();
		field.val('');
		field.attr('name', this.value+'['+$(this).parent().find('input.text').length+']');
		field.insertBefore(this);
	});
	$('button.remove_param_field').click(function(){
		var inputs = $(this).parent().find('input.text');
		if (inputs.length>1)
			inputs.last().remove();
		else
			inputs.val('');
	});
	if (typeof CKEDITOR != 'undefined' && $('.ckeditor').length > 0){
		CKEDITOR.replace('body',
			{
				uiColor : '#EAEDFF',
				allowedContent : true,
				width : '98.3%',
				height : '200px',
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
		
		// For the CKE Editor, we have a custom way to bind this auto complete when the user want to insert an existing image
		// That is a little heavy (for the browser) and should work with only one CKE editor
		setTimeout(function(){
			var Once = false;
			$('.cke_button__image').click(function(){
				if (!Once){
					setTimeout(function(){
						$('td.cke_dialog_ui_hbox_first').each(function(){
							$(this).find('div.cke_dialog_ui_text').each(function(){
								var label = $(this).find('label:first');
								var input = $(this).find('input.cke_dialog_ui_input_text');
								if (label.length && input.length && label.text().toLowerCase().trim()=='url'){
									input.parent().append('<div class="autocompletebox '+input.attr('id')+'"></div>');
									new BoN.FileAutoComplete(input, $('.' + input.attr('id')), true);
								}
							});
						});
					}, 1000);
					Once = true;
				}
			});
		}, 1000);
	}
});