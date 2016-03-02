/**
 * A trim function similar to the PHP one
 * @param toRemove string pattern to remove
 * @returns string
 */
String.prototype.trim = function(toRemove) {
	if (typeof toRemove == 'undefined' || toRemove === null) toRemove = ' ';
	var replaced = this.toString();
	var thisLen = this.length;
	var trimLen = toRemove.length;
	if (replaced.indexOf(toRemove)===0){
		replaced = replaced.substring(trimLen, thisLen);
	}
	if (replaced.substring(replaced.length-trimLen)===toRemove){
		replaced = replaced.substring(0, replaced.length-trimLen);
	}
	if (replaced.indexOf(toRemove)===0 || replaced.substring(replaced.length-trimLen)===toRemove)
		replaced = replaced.trim(toRemove);
	return replaced;
};
/**
 * A common check for e-mail validity
 * @returns boolean
 */
String.prototype.isEmail = function() {
    var pattern = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return pattern.test(this);
};
/**
 * @returns string	The value set to an arg
 */
String.prototype.getSearch = function(key) {
	var searches = document.location.search.toString().substr(1).split('&');
	for (var i in searches){
		var exp = searches[i].split('=');
		if(key.trim() == exp[0].trim()){
			return exp[1];
		}
	}
	return '';
};
/**
 * @returns boolean
 */
navigator.isMobile = function(){
	return /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(this.userAgent.toLowerCase());
};

if (typeof FragTale == 'undefined'){
	var FragTale = {};
}
/**
 * Got from the W3C web site
 */
FragTale.Cookie = {
	set: function(c_name, value, exdays){
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=c_name + "=" + c_value;
	},
	get: function(c_name){
		var c_value = document.cookie;
		var c_start = c_value.indexOf(" " + c_name + "=");
		if (c_start == -1){
			c_start = c_value.indexOf(c_name + "=");
		}
		if (c_start == -1){
			c_value = null;
		}
		else{
			c_start = c_value.indexOf("=", c_start) + 1;
			var c_end = c_value.indexOf(";", c_start);
			if (c_end == -1){
				c_end = c_value.length;
			}
			c_value = unescape(c_value.substring(c_start,c_end));
		}
		return c_value;
	},
	del: function(c_name) {
		var path = ";path=" + "/";
		var hostname = document.location.hostname;
		if (hostname.indexOf("www.") === 0)
			hostname = hostname.substring(4);
		var domain = ";domain=" + "." + hostname;
		var expiration = "Thu, 01-Jan-1970 00:00:01 GMT";
		document.cookie = c_name + "=" + path + domain + ";expires=" + expiration;
	}
};
/**
 * This custom method is used sometimes to fill in the selects that are submitted into a form and intending to generate random values
 * matched by the server and decrease the rate of spams
 */
FragTale.initSelects = function(){
	if (typeof FragTale.SelectOptions == 'undefined') return false;
	jQuery('select').each(function(){
		var key = jQuery(this).attr('id').replace('field_', '');
		if (typeof FragTale.SelectOptions[key] == 'undefined') return;
		var items = FragTale.SelectOptions[key];
		for (var key in items){
			jQuery(this).append('<option value="' + key + '">' + items[key] + '</option>');
		}
	});
};
/**
 * A custom expand/collapse toggler, allow horizontal or vertical animation.
 * !!Important!!: this toggler is not compatible with target container that have a scrollbar
 * and a defined height in CSS (if you choose "vertical" toggle, same for width if you choose "horizontal" toggle)
 */
FragTale.Toggler = {
	toggleSpeed: 150,
	elts:{},
	init: function(){
		jQuery('.bon_toggler').each(function(i){
			var targetId = jQuery(this).attr('target').indexOf('#') == 0 ? jQuery(this).attr('target') : '#'+jQuery(this).attr('target');
			if (typeof targetId == 'undefined' || !targetId) return false;
			FragTale.Toggler.elts[i] = {};
			FragTale.Toggler.elts[i].trigger	= jQuery(this);
			FragTale.Toggler.elts[i].trigger.css('cursor', 'pointer');
			FragTale.Toggler.elts[i].target	= jQuery(targetId);
			FragTale.Toggler.elts[i].target.css('overflow', 'hidden');
			FragTale.Toggler.elts[i].width = FragTale.Toggler.elts[i].target.width() +
				//parseInt(FragTale.Toggler.elts[i].target.css('padding-left'), 10) +
				parseInt(FragTale.Toggler.elts[i].target.css('padding-right'), 10);
			FragTale.Toggler.elts[i].height = FragTale.Toggler.elts[i].target.height() +
				//parseInt(FragTale.Toggler.elts[i].target.css('padding-top'), 10) +
				parseInt(FragTale.Toggler.elts[i].target.css('padding-bottom'), 10);
			if (FragTale.Toggler.elts[i].target.hasClass('collapsed')){
				if (jQuery(this).hasClass('vertical') || !jQuery(this).hasClass('horizontal'))
					FragTale.Toggler.elts[i].target.height(0).hide();
				if (jQuery(this).hasClass('horizontal') || !jQuery(this).hasClass('vertical'))
					FragTale.Toggler.elts[i].target.width(0).hide();
			}
			FragTale.Toggler.elts[i].trigger.click(function(e){
				e.preventDefault();
				var elt = FragTale.Toggler.elts[i];
				if (elt.target.hasClass('collapsed')){
					if (jQuery(this).hasClass('exclusive'))
						FragTale.Toggler.collapseAll(true);
					elt.target.show();
					var height = 0;
					if (FragTale.Toggler.elts[i].height){
						 height = FragTale.Toggler.elts[i].height;
					}
					else{
						if (jQuery(this).hasClass('vertical') || !jQuery(this).hasClass('horizontal')){
							elt.target.children().each(function(){
								height += jQuery(this).height();
							});
						}
					}
					if ((jQuery(this).hasClass('vertical') && jQuery(this).hasClass('horizontal')) || (!jQuery(this).hasClass('vertical') && !jQuery(this).hasClass('horizontal')))
						elt.target.animate({height: height+'px', width: elt.width+'px'}, FragTale.Toggler.toggleSpeed, function(){elt.target.css('height', 'auto');});
					else if (jQuery(this).hasClass('horizontal'))
						elt.target.animate({width: elt.width+'px'}, FragTale.Toggler.toggleSpeed);
					else
						elt.target.animate({height: height+'px'}, FragTale.Toggler.toggleSpeed, function(){elt.target.css('height', 'auto');});
					elt.target.removeClass('collapsed');
					elt.trigger.addClass('open');
				}
				else{
					if ((jQuery(this).hasClass('vertical') && jQuery(this).hasClass('horizontal')) || (!jQuery(this).hasClass('vertical') && !jQuery(this).hasClass('horizontal')))
						elt.target.animate({height: 0, width: 0}, FragTale.Toggler.toggleSpeed, function(){jQuery(this).hide();});
					else if (jQuery(this).hasClass('vertical'))
						elt.target.animate({height: 0}, FragTale.Toggler.toggleSpeed, function(){jQuery(this).hide();});
					else
						elt.target.animate({width: 0}, FragTale.Toggler.toggleSpeed, function(){jQuery(this).hide();});
					elt.target.addClass('collapsed');
					elt.trigger.removeClass('open');
				}
				return false;
			});
		});
	},
	collapseAll: function(exclusive){
		for (i in FragTale.Toggler.elts){
			var elt = FragTale.Toggler.elts[i];
			if (elt.target.hasClass('collapsed')) continue;
			if (exclusive && !elt.trigger.hasClass('exclusive')) continue;
			elt.trigger.click();
		}
	}
};
/**
 * 
 */
FragTale.SlideShow = {
	/**
	 * @param jQueryObject	string|DOM Object|jQuery Object		Same way to get an instance of jQuery (Mandatory)
	 * @param interval		int									Milliseconds: for setInterval
	 * @param fadeSpeed		int									Milliseconds: elapsed time to execute the fade in/out
	 */
	Fade: function(jQueryObject, interval, fadeSpeed){
		this.object		= null;
		this.i = this.n = 0;
		this.interval	= 5000;
		this.fadeSpeed	= 1000;
		var $this = this;
		/**
		 * @return int	The interval thread ID (killable by "clearTimeout(intervalThreadID)")
		 */
		$this.autorun = function(){
			if ($this.object == null || $this.object.length < 1){
				console.log('FragTale.SlideShow.Fade: Could not run "' + jQueryObject.toString() +'" because object does not exist or is null');
				return false;
			}
			return setInterval($this.next, $this.interval);
		};
		/**
		 * This should be bound to any DOM element on click event
		 * @param event	DOMEvent	Not mandatory
		 */
		$this.next = function(event){
			try{
				if (event) event.preventDefault();
				jQuery($this.object.get($this.i)).fadeOut($this.fadeSpeed);
				$this.i++;
				if ($this.i >= $this.n) $this.i = 0;
				jQuery($this.object.get($this.i)).fadeIn($this.fadeSpeed);
				if (event) return false;
			}
			catch(e){
				console.log('FragTale.SlideShow.Fade.next "' + e.name + '": ' + e.message);
			}
		};
		/**
		 * This should be bound to any DOM element on click event
		 * @param event	DOMEvent	Not mandatory
		 */
		$this.previous = function(e){
			try{
				if (event) event.preventDefault();
				jQuery($this.object.get($this.i)).fadeOut($this.fadeSpeed);
				$this.i--;
				if ($this.i < 0) $this.i = ($this.n - 1);
				jQuery($this.object.get($this.i)).fadeIn($this.fadeSpeed);
				if (event) return false;
			}
			catch(e){
				console.log('FragTale.SlideShow.Fade.previous "' + e.name + '": ' + e.message);
			}
		};
		/* init */
		try{
			if (jQueryObject && typeof jQueryObject != 'undefined'){
				$this.object = jQuery(jQueryObject);
				$this.n = $this.object.length;
				if (interval)	$this.interval	= interval;
				if (fadeSpeed)	$this.fadeSpeed	= fadeSpeed;
				$this.object.hide().css('position', 'absolute').parent().css('position', 'relative');
				jQuery($this.object.get(0)).show();
			}
		}
		catch(e){
			console.log('FragTale.SlideShow.Fade "' + e.name + '": ' + e.message);
		}
		return $this;
	}
};
FragTale.CustomImageUpload = {
	count: 0,
	tpl: null,
	
	init: function(){
		if (typeof FileReader == 'undefined'){
			console.log('The function "FileReader" is not supported by this browser');
			return false;
		}
		jQuery('.vignette input[type=file]').each(function(){
			FragTale.CustomImageUpload.customize(this);
		});
		/* Enable multiple file upload (by appending new empty vignette) */
		if (jQuery('form.multiple_file_upload').length){
			FragTale.CustomImageUpload.tpl = jQuery('form.multiple_file_upload').find('.vignette').first().clone();
		}
	},
	customize: function(input){
		FragTale.CustomImageUpload.count++;
		var This = jQuery(input).css({
			opacity : 0,
			width : '100%',
			height : '100%',
			position : 'absolute',
			top : '0',
			left : '0'
		});
		var Parent = This.parent().css('position', 'relative');
		This.change(function(e){
			var wasEmpty = !jQuery(This).hasClass('loaded');
			var files = this.files;
			for (var i = 0; i<files.length; i++) {
				var file = files[i];
				var reader = new FileReader();
				var msg = '';
				if (file.size > 10000000){
					//File is > 10Mo: too high --> warnings
					msg += '<b>WARNING: This file might be too heavy!</b><br>';
				}
				if (file.type.indexOf('image')>-1){
					reader.onload = function(e){
						//@todo: match if image already exists into the database
						var upimg = e.target;
						Parent.find('img, object').remove();
						var img = jQuery('<img>');
						img.attr('src', upimg.result).css({
							'max-width': '100%',
							'max-height': '100%'
						});
						Parent.prepend(img);
		            };
		            reader.readAsDataURL(file);
					jQuery(This).addClass('loaded')
		            if (wasEmpty){
		            	FragTale.CustomImageUpload.createNew($(This).closest('form'));
		            }
		            continue;
				}
				else if (file.type.indexOf('pdf')>-1 ||
						file.type.indexOf('audio')>-1 ||
						file.type.indexOf('video')>-1 ||
						file.type.indexOf('text')>-1 ||
						file.type.indexOf('flash')>-1){
					reader.onload = function(e){
						//@todo: match if file already exists into the database
						var upimg = e.target;
						Parent.find('img, object').remove();
						var obj = jQuery('<object>');
						obj.attr('data', upimg.result).css({
							width: '100%',
							height:'100%',
							overflow: 'hidden'
						});
						Parent.prepend(obj);
		            };
					jQuery(This).addClass('loaded')
		            reader.readAsDataURL(file);
		            if (wasEmpty){
		            	FragTale.CustomImageUpload.createNew($(This).closest('form'));
		            }
		            continue;
				}
				var ftype = file.type.split('/');
				msg += 'Filename: <b>' + file.name + '</b><br>';
				msg += 'Size: ' + file.size + ' bytes<br>';
				msg += 'Type: ' + file.type + '<br>';
				Parent.find('label').html(msg);
				jQuery(This).addClass('loaded')
	            if (wasEmpty){
	            	FragTale.CustomImageUpload.createNew($(This).closest('form'));
	            }
			}
		});
	},
	createNew: function(inForm){
		if (!jQuery(inForm).hasClass('multiple_file_upload') ||
			!FragTale.CustomImageUpload.tpl) return;
		var newOne = FragTale.CustomImageUpload.tpl.clone();
		FragTale.CustomImageUpload.customize(newOne.find('input[type=file]'));
		jQuery(inForm).find('.vignette').parent().append(newOne);
	}
};
FragTale.ImgHighlights = {
	init: function(){
		$('body').append('<div id="bonImgHighlighter" class="bonHighlighter"><img id="imgHighlight"><div id="bonCarousel"></div></div>');
	},
	launch: function(img){
		if (!img) return false;
		if (typeof img.src == 'undefined') return false;//Only image
		if ($(img).parent('a,button').length) return true;//Disable the highlight if the image parent trigger is a <a> or <button> tag
		$('#bonCarousel').find('img').remove();
		$('#imgHighlight').css({'margin-top': 0}).attr('src', img.src).show();
		$('#bonImgHighlighter').fadeIn('fast', function(){
			var imgHeight = $('#imgHighlight').height();
			var winHeight = $(window).height();
			var marginTop = (winHeight-imgHeight)/2;
			if (marginTop<0) marginTop = 0;
			$('#imgHighlight').animate({'margin-top': marginTop+'px'}, 100);
		});
		$('#bonImgHighlighter').unbind('click').click(function(){
			$(this).fadeOut('fast');
		});
	}
};
if (typeof jQuery == 'undefined'){
	throw new Error('fragtale.js: jQuery is required');
}
else{
	jQuery(function(){
		FragTale.initSelects();
		FragTale.Toggler.init();
		FragTale.CustomImageUpload.init();
		FragTale.ImgHighlights.init();
	});
}