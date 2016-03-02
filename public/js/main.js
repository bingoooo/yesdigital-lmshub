$(document).ready(function() {
	/* CKEDITOR */
	if (typeof CKEDITOR != 'undefined' && $('.ckeditor').length > 0){
		if ($('#field_message').length > 0)
		CKEDITOR.replace('field_message',
			{
				uiColor : '#EAEDFF',
				allowedContent : true,
				width : '98.3%',
				height : '350px',
				toolbar: 'Custom',
				toolbar_Custom:[
	                { name: 'document', items : [ 'Preview' ] },
					{ name: 'fixbug1', items : [ 'Preview' ] },
					{ name: 'print', items : [ 'Print' ] },
					{ name: 'clipboard', items : [ 'Undo','Redo' ] },
					{ name: 'tools', items : [ 'Maximize', 'ShowBlocks' ] },
					{ name: 'paragraph', items : [ 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
					{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript' ] },
					{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
					{ name: 'colors', items : [ 'TextColor','BGColor' ] },
					{ name: 'insert', items : [ 'Image','Smiley','SpecialChar' ] },
					{ name: 'links', items : [ 'Link','Unlink' ] },
					{ name: 'editing', items : [ 'SpellChecker', 'Scayt' ] },
				],
			}
		);
	}
});