var ArtLists = {
	animspeed: 200,
	
	init: function(){
		ArtLists.bindTriggerEvts();
	},
	
	enableDisableBtns: function(){
		$('.article_lists_cont .moveup, .article_lists_cont .movedown').removeClass('blur');
		$('.article_lists_cont .category_tree, .article_lists_cont .article_tree').each(function(){
			$('.article_lists_cont .moveup').each(function(){
				var current = $(this).parent().closest('li');
				var previous = current.prev('li').not('.article_create');
				if (!previous.length) $(this).addClass('blur');
			});
			$('.article_lists_cont .movedown').each(function(){
				var current = $(this).parent().closest('li');
				var next = current.next('li').not('.article_create');
				if (!next.length) $(this).addClass('blur');
			});
		});
	},
	
	bindTriggerEvts: function(){
		$('.article_lists_cont .moveup').click(function(){
			var current = $(this).parent().closest('li');
			var previous = current.prev('li').not('.article_create');
			if (!previous.length) return false;
			current.fadeOut(ArtLists.animspeed, function(){
				current.insertBefore(previous);
				ArtLists.resetAllPositions();
				current.fadeIn(ArtLists.animspeed);
			});
		});
		$('.article_lists_cont .movedown').click(function(){
			var current = $(this).parent().closest('li');
			var next = current.next('li').not('.article_create');
			if (!next.length) return false;
			current.fadeOut(ArtLists.animspeed, function(){
				current.insertAfter(next);
				ArtLists.resetAllPositions();
				current.fadeIn(ArtLists.animspeed);
			});
		});
		ArtLists.resetAllPositions();
	},
	
	resetAllPositions: function(){
		var i = 0;
		$('.category_tree').find('.category_branch').each(function(){
			i++;
			$(this).find('.hidden_position').val(i);
		});
		var j = 0;
		$('.article_tree').find('.article_branch').each(function(){
			j++;
			$(this).find('.hidden_position').val(j);
		});
		ArtLists.enableDisableBtns();
	},
};

$(function(){
	ArtLists.init();
});