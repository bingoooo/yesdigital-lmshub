$(function(){
	/* Little Wysiwyg */
	var corpus = document.getElementById('corpus').contentWindow.document;
	corpus.body.contentEditable = true;
	$('#font-bold').click(function(){
		corpus.execCommand('bold');
	});
	$('#font-underline').click(function(){
		corpus.execCommand('underline');
	});
	$('#font-italic').click(function(){
		corpus.execCommand('italic');
	});
	$('#fonts').mouseenter(function(){
		$('#font-options').animate({opacity: 1, width: '160px'}, 200);
	}).mouseleave(function(){
		$('#font-options').animate({opacity: 0, width: 0}, 500);
	});
	/* Form submission for mail sending */
	$('#form-msg').submit(function(){
		var check = true;
		try{
			/* Fist, get corpus content into textarea */
			$('#txt_msg').html(corpus.body.innerHTML.trim());
			if ($('#recipient').val()==''){
				$('#recipient').addClass('error').focus();
				check = false;
			}
			else
				$('#recipient').removeClass('error');
			if ($('#subject').val()==''){
				$('#subject').addClass('error').focus();
				check = false;
			}
			else
				$('#subject').removeClass('error');
			if ($('#txt_msg').html()==''){
				$('#corpus').addClass('error').focus();
				check = false;
			}
			else
				$('#corpus').removeClass('error');
		}
		catch(exc){
			//alert(exc.message);
			return false;
		}
		return check;
	});
	/* File attachment */
	$('input[name=attachment]').change(function(){
		var filename = this.value;
		if (filename.indexOf('/')>-1){
			split = filename.split('/');
			filename = split[split.length-1];
		}
		else if (filename.indexOf('\\')>-1){
			split = filename.split('\\');
			filename = split[split.length-1];
		}
		$('#attachment-filename').html(filename);
	});
});