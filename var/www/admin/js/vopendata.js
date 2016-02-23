$(document).ready(function() {	
	$('#statsgrid').isotope({
      // options
      itemSelector : '.stat',
      layoutMode : 'masonry',
      masonry: {
        columnWidth: 188,
        gutterWidth: 10
      }
    });
    $('#filters a').click(function(){
	  var selector = $(this).attr('data-filter');
	  $('#statsgrid').isotope({ filter: selector });
	  return false;
	});
});