  <!-- <script type="text/javascript" src="js/bootstrap.min.js"></script> -->
  <a href="#" id="back-to-top" title="Back to top">&uarr;</a>
  <script type="text/javascript">
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
