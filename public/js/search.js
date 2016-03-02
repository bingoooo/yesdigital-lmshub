/* Highlights the search terms found */
$(function(){
	//Highlight each terms of search
	var searches = $('#search').val().split(' ');
	for (i in searches){
		search = searches[i];
		if (search.trim().length<2) continue;
		$('.search_result_elt').each(function(){
			var htmlcontent = $(this).html();
			htmlcontent = htmlcontent.replace(new RegExp(search, 'ig'), '<span class="search_highlight">'+search+'</span>');
			$(this).html(htmlcontent);
		});
	}
});