var ImageLibrary = {
	
	storedSearches: {},
	
	position: 0,
	
	$libraryCont: null,
	$images: null,
	$no_results: null,
	$nextArrow: null,
	$prevArrow: null,
	$searchInput: null,
	
	init: function(libraryCont){
		this.$libraryCont = $($(libraryCont)[0]);//Only one instance
		this.$prevArrow = this.$libraryCont.find('.image_library_arrow_previous');
		this.$nextArrow = this.$libraryCont.find('.image_library_arrow_next');
		this.$no_results = this.$libraryCont.find('.image_no_result');
		this.$libraryCont.find('.image_library_cont').removeClass('hidden').hide();
		if (typeof AllPictures == 'undefined' || !AllPictures.length){
			this.$prevArrow.hide();
			this.$nextArrow.hide();
			this.$no_results.show();
			return;
		}
		
		this.$searchInput = this.$libraryCont.find('.img_search_input');
		this.$images = this.$libraryCont.find('.image_library_result');
		if (AllPictures.length <= this.$images.length){
			this.$prevArrow.hide();
			this.$nextArrow.hide();
			this.$searchInput.hide();
		}
		else{
			// Bind Prev Next Clicks
			this.$prevArrow.click(this.prev);
			this.$nextArrow.click(this.next);
			// Bind Search
			this.$searchInput.keyup(this.search);
		}
		//First Load
		this.loadAllFrom(0);
	},
	search: function(){
		var strmatch = this.value.trim().toLowerCase();
		if (typeof ImageLibrary.storedSearches[strmatch] != 'undefined'){
			ImageLibrary.position = 0;
			ImageLibrary.load(ImageLibrary.storedSearches[strmatch]);
			return;
		}
		var results = [];
		for (var i in AllPictures){
			//if (AllPictures[i].filename.toLowerCase().indexOf(strmatch)===0)//Beginning
			if (AllPictures[i].filename.toLowerCase().indexOf(strmatch)>-1)//Containing
				results.push(AllPictures[i]);
		}
		ImageLibrary.storedSearches[strmatch] = results;
		if (results.length){
			ImageLibrary.position = 0;
		}
		ImageLibrary.load(results);
	},
	
	load: function(results, from){
		if (!from) from = ImageLibrary.position;
		ImageLibrary.$images.attr('src', '').attr('title', '')
			.parent().hide()
			.find('.filename').html('');
		ImageLibrary.$no_results.hide();
		if (!results.length){
			ImageLibrary.$no_results.show();
			ImageLibrary.checkPrevNext(results);
			return;
		}
		for (i=from, j=0; j < ImageLibrary.$images.length; i++, j++){
			var img = ImageLibrary.$images[j];
			if (typeof results[i] == "undefined") break;
			$(img).attr('src', WEB_ROOT + results[i].path)
				.attr('title', results[i].filename)
				.parent().find('.filename').html(results[i].filename);
		}
		ImageLibrary.fadeCascade(0, j);
		ImageLibrary.checkPrevNext(results);
	},
	fadeCascade: function(i, lim){
		if (i==lim) return;
		var j = i+1;
		var img = ImageLibrary.$images[i];
		$(img).parent().fadeIn(200, function(){
			ImageLibrary.fadeCascade(j, lim);
		});
	},
	prev: function(e){
		ImageLibrary.position -= ImageLibrary.$images.length;
		var strmatch = ImageLibrary.$searchInput.val().trim().toLowerCase();
		if (typeof ImageLibrary.storedSearches[strmatch] == "undefined")
			ImageLibrary.loadAllFrom(ImageLibrary.position);
		else
			ImageLibrary.load(ImageLibrary.storedSearches[strmatch], ImageLibrary.position);
	},
	next: function(e){
		ImageLibrary.position += ImageLibrary.$images.length;
		var strmatch = ImageLibrary.$searchInput.val().trim().toLowerCase();
		if (typeof ImageLibrary.storedSearches[strmatch] == "undefined")
			ImageLibrary.loadAllFrom(ImageLibrary.position);
		else
			ImageLibrary.load(ImageLibrary.storedSearches[strmatch], ImageLibrary.position);
	},
	loadAllFrom: function(i){
		this.position = i;
		ImageLibrary.load(AllPictures, i);
	},
	checkPrevNext: function(results){
		console.log('position: '+ImageLibrary.position + ' | NB Places: ' + ImageLibrary.$images.length +
				' | Results count: ' + results.length);
		if (results.length <= this.$images.length){
			this.$prevArrow.hide();
			this.$nextArrow.hide();
			return;
		}
		if (typeof ImageLibrary.position == "undefined" ||
			ImageLibrary.position < 1 || !ImageLibrary.position)
			ImageLibrary.position = 0;
		this.$prevArrow.show();
		this.$nextArrow.show();
		if (!ImageLibrary.position){
			this.$prevArrow.hide();
		}
		if ((ImageLibrary.position + ImageLibrary.$images.length) >= results.length){
			this.$nextArrow.hide();
		}
	}
};
$(function(){
	ImageLibrary.init('.image_library');
});