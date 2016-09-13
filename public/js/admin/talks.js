$(function(){
	//Mark as read
	$('.letter.inbox div.unread').click(function(){
		var $this = $(this);
		var uid = $this.parent().parent().attr('id').replace('uid_', '');
		var mid = $this.attr('target').replace('mid_', '');
		$.post(
			WEB_ROOT+'/ajax/json',
			{
				method: 'set',
				model: 'cms',
				'class': 'message',
				mid: mid,
				opened: 1
			},
			function(json){
				if (typeof json.result != 'undefined' && json.result){
					$this.removeClass('unread');
					var spanCounter = $('#with_'+uid+' .unread_count');
					var count = parseInt(spanCounter.text(), 10);
					count--;
					if (count<1){
						$('#with_'+uid+' .unread_message').html('');
					}
					else{
						spanCounter.text(count);
					}
				}
			}
		);
	});
	//Reply to
	$('.reply_to').click(function(){
		$('#recipient').val($(this).find('.user_id').val());
	});
});