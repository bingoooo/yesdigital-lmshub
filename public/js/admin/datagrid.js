var CriteriasCollection = {
	order:	'',
	desc:	0,
	count:	$('#count').val(),
	page:	$('#page').val(),
	init: function(){
		var searches = document.location.search.toString().substr(1).split('&');
		for (var i in searches){
			var exp = searches[i].split('=');
			var key = exp[0];
			if (key in CriteriasCollection)
				CriteriasCollection[key] = typeof exp[1] != 'undefined' ? exp[1] : '';
		}
	},
	getSearch: function(){
		var search = '?';
		for (var key in CriteriasCollection){
			if (typeof CriteriasCollection[key] != 'function' && CriteriasCollection[key]){
				search += key + '=' + CriteriasCollection[key] + '&';
			}
		}
		return search;
	},
	getLocation: function(){
		return document.location.protocol  + '//' + document.location.host.trim('/') + '/' + document.location.pathname.trim('/') + '/' + this.getSearch();
	}
};
$(function(){
	$('.datagrid').each(function(){
		//On click row = go to edit page
		$(this).find('tr.trigger').click(function(e){
			if (!$(this).attr('object')) return false;
			var exp = this.id.split('_');
			var key_name= exp[0];
			var obj_id	= exp[1];
			var target_object = $(this).attr('object');
			document.location = ADMIN_WEB_ROOT.trim('/') + '/' + target_object.trim('/') + '/edit?' + key_name + '=' + obj_id;
		});
		
		/**	Criterias **/
		CriteriasCollection.init();
		//On click event datagrid th = sort result
		$(this).find('th a').click(function(e){
			e.preventDefault();
			CriteriasCollection.order = this.id.replace('field_', '');
			CriteriasCollection.desc = $(this).hasClass('desc') ? 1 : 0;
			document.location = CriteriasCollection.getLocation();
		}).each(function(){
			if (!CriteriasCollection.desc)
				$(this).addClass('desc');
		});
		
	});
	$('.gridcount').change(function(){
		BoN.Cookie.set('gridcount', this.value);
		CriteriasCollection.count = this.value;
		document.location = CriteriasCollection.getLocation();
	});
	$('.gridpage').change(function(){
		CriteriasCollection.page = this.value;
		document.location = CriteriasCollection.getLocation();
	});
	/*if (!document.location.href.getSearch('count')){
		$('.gridcount').val(BoN.Cookie.get('gridcount'));
	}*/
});
