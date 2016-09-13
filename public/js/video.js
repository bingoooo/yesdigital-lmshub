$(function(){
	//Append highlighter
	if (!$('#highlight-container').length){
		var highlighter = $(
		'<div id="highlight-container" class="hidden">' +
			'<div id="highlight-flex">' +
				'<div id="close-container">' +
					'<span class="close">&nbsp;</span>' +
				'</div>' +
				'<div id="highlighter"></div>' +
			'</div>' +
		'</div>');
		
		highlighter.find('.close').click(function(){
			highlighter.addClass('hidden');
			$('video').each(function(){this.pause();});
		});
		$('body').append(highlighter);
	}
	
	$('.video-container .play').each(function(){
		var Parent = $(this).closest('.video-container');
		var genid = 'target_' + Math.random().toString().replace('.', '_') + '_' + Math.random().toString().replace('.', '_');
		$(this).prop('data-id', genid);
		var video = Parent.find('video').prop('controls', true).attr('id', genid).click(function(){
			if (this.paused == false)
				this.pause();
			else
				this.play();
		}).addClass('hidden');
		//Check thumbnail
		var src = video.find('source').length ? video.find('source').get(0).src : '';
		var thumbnailsrc = src.substring(0, src.lastIndexOf('.')) + '.jpg';
		//Must use this trick in case of cross domain between .fr and .com
		var img = new Image();
		img.onload = function(){
			$('#highlighter').append(video);
			Parent.prepend($(this).width('100%'));
		};
		img.onerror = function(){
			$('#highlighter').append(video.clone());
		};
		img.src = thumbnailsrc;
		var filmgif = $('<img>').attr('src', WEB_ROOT + '/img/film.gif').css({width: '100%', height: '100%', position: 'absolute', zIndex: '1', top: 0, left: 0});
		$(this).append(filmgif);
		//On click play
		$(this).click(function(){
			$('video').each(function(){this.pause();});
			$('#highlighter object, #highlighter video').addClass('hidden');
			$('#' + $(this).prop('data-id')).removeClass('hidden').get(0).play();
			$('#highlight-container').removeClass('hidden');
		});
	});
});