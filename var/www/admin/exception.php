  <div class="vertical-center">
    <div class="container">
      <div class="alert alert-danger" style="width:60%" role="alert">
        <strong>Attenshion !</strong><br /><br />
        You've reach this page due to an unexpected exsheption, but shtay calm, I'm here to help.<br />
        Pleashe find below more details about this exsheption:<br /><br />
        <div class="alert alert-warning" style="font-family: monospace;">
<?php echo $e->getMessage(); ?>
        </div>
        <!-- <button type="button" class="btn btn-success">Please take me back to safety</button> -->
      </div>
    </div>
  </div>
  <div id="footerSean">
    <a href="https://en.wikipedia.org/wiki/Zardoz"><img src="../images/zardoz2.png" /></a>
  </div>
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
                return {id: item.name, imgid: "vc-host.gif", urlid: item.urlid};
              } else if (item.type == "VM") {
                return {id: item.name, imgid: "vc-vm.gif", urlid: item.urlid};
              } else if (item.type == "DS") {
                return {id: item.name, imgid: "vc-datastore.gif", urlid: item.urlid};
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
          suggestion: Handlebars.compile('<a href="/infos.php?q={{urlid}}"><p class="suggestionList"><img src="images/{{imgid}}" class="glyphicon-custom" /> {{id}}</p></a>')
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
  </script>
</body>
</html>
