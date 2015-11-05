jQuery(document).ready(function($) {

	$(document).on('click', '.hide_me_thumbnail', function(){
		$(this).find('.overlay').fadeOut();
	});

});