  <a href="#" id="back-to-top" title="Back to top">&uarr;</a>
  <script type="text/javascript">
    $(document).ready(function() {
      //Set up "Bloodhound" Options 
      var source = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('id'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
          url: "/search.php?query=%QUERY",
          filter: function(x) {
            return $.map(x, function(item) {
              if (item.type == "ESX") {
                return {id: item.name, imgid: "vc-host.gif", urlid: item.urlid, lastseen: item.lastseen.split(" ")[0]};
              } else if (item.type == "VM") {
                return {id: item.name, imgid: "vc-vm.gif", urlid: item.urlid, lastseen: item.lastseen.split(" ")[0]};
              } else if (item.type == "DS") {
                return {id: item.name, imgid: "vc-datastore.gif", urlid: item.urlid, lastseen: item.lastseen.split(" ")[0]};
              }
            });
          },
          wildcard: "%QUERY"
        }
      });

      // Initialize Typeahead with Parameters
      source.initialize();
      var typeahead_elem = $('.typeahead');
      typeahead_elem.typeahead({
        hint: false,
        highlight: true,
        minLength: 2
      },
      {
        // `ttAdapter` wraps the suggestion engine in an adapter that
        // is compatible with the typeahead jQuery plugin
        name: 'id',
        displayKey: 'id',
        limit: 15,
        source: source.ttAdapter(),
        templates: {
          suggestion: Handlebars.compile('<a href="{{urlid}}" rel="modal"><p class="suggestionList"><img src="images/{{imgid}}" class="glyphicon-custom" /> {{id}} <span class="lastseen">lastseen: {{lastseen}}</span></p></a>')
        }
      });

      //Get the Typeahead Value on Following Events
      $('input').on([
        'typeahead:initialized',
        'typeahead:initialized:err',
        'typeahead:selected',
        'typeahead:autocompleted',
        'typeahead:cursorchanged',
        'typeahead:opened',
        'typeahead:closed'
      ].join(' '), function(x) {
        //console.log(this.value); 
      });
    });
    
    $('#sexisearch, #modal').on('click', 'a[rel=modal]', function(evt) {
      evt.preventDefault();
      var modal = $('#modal').modal();
      modal.find('.modal-body').load($(this).attr('href'), function (responseText, textStatus) {
        if ( textStatus === 'success' || textStatus === 'notmodified') {
          modal.show();
        }
      });
    });
    
    $("#modal").on("shown.bs.modal",function(){
       $(this).hide().show(); 
    });
      
    $(function() {
      $(".toclink").click(function() {
        if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
          var target = $(this.hash);
          target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
          if (target.length) {
            $('html, body').animate({
              scrollTop: target.offset().top
            }, 1000);
            return false;
          }
        }
      });
    });

    $('h2').each(function() {
      var onlytext = $(this).clone().children().remove().end().text();
      $('div#toc>ul').append('<li><a class="toclink" href="#' + $(this).attr('id') + '"><i class="glyphicon glyphicon-link"></i> ' + onlytext + '</a></li>');
    });

    if ($('#back-to-top').length) {
    var scrollTrigger = 100, // px
    backToTop = function () {
      var scrollTop = $(window).scrollTop();
      if (scrollTop > scrollTrigger) {
          $('#back-to-top').show();
      } else {
          $('#back-to-top').hide();
      }
    };
    backToTop();
    $(window).on('scroll', function () {
        backToTop();
    });
    $('#back-to-top').on('click', function (e) {
        e.preventDefault();
        $('html,body').animate({
            scrollTop: 0
        }, 700);
    });
  }
  </script>
</body>
</html>
