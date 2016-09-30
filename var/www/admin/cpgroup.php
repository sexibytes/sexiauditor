<?php
require("session.php");
require("dbconnection.php");
$isAdminPage = true;
$title = "Capacity Planning Group";
require("header.php");
?>
  <div class="container"><br/>
    <div class="panel panel-primary">
      <div class="panel-heading"><h3 class="panel-title">Capacity Planning Group Notes</h3></div>
      <div class="panel-body"><ul>
        <li>This page can be used to create logical group for your ESX servers.</li>
        <li>These groups will be used for Capacity Planning compute (every ESX of the same group will be aggregated).</li>
        <li>Please refer to the <a href="http://www.sexiauditor.fr/">project website</a> and documentation for more information.</li>
      </ul></div>
    </div>
    <h2><i class="glyphicon glyphicon-th-list"></i> Capacity Planning Groups:</h2>
    <form class="form-inline" method="POST">
      <div class="input_fields_wrap">
        <div style="padding-bottom:10px;">
          <div class="form-group">
            <label>Group Name</label>
            <input type="text" class="form-control" name="groups[]" placeholder="GOLD-PROD">
          </div>&nbsp;
          <div class="form-group">
            <label>ESX Servers (separated by commas)</label>
            <input type="email" class="form-control" name="esxservers[]" size="50" placeholder="esx01.sexibyt.es;esx02.sexibyt.es">
          </div>
          <button class="btn btn-danger remove_field">Remove</button>
        </div>
      </div>
      <br>
      <button class="btn btn-default add_field_button">Add More Fields</button>
      <button type="submit" class="btn btn-success">Submit</button>
    </form>
  </div>
  <script type="text/javascript">
    $(document).ready(function() {
      var max_fields      = 10; //maximum input boxes allowed
      var wrapper         = $(".input_fields_wrap"); //Fields wrapper
      var add_button      = $(".add_field_button"); //Add button ID
      var x = 1; //initlal text box count
      $(add_button).click(function(e){ //on add input button click
        e.preventDefault();
        if(x < max_fields){ //max input box allowed
          x++; //text box increment
          $(wrapper).append('        <div style="padding-bottom:10px;">\n                     <div class="form-group">\n                      <label>Group Name</label>\n                      <input type="text" class="form-control" name="groups[]" placeholder="GOLD-PROD">\n                    </div>&nbsp;\n                    <div class="form-group">\n                      <label>ESX Servers (separated by commas)</label>\n                      <input type="email" class="form-control" name="esxservers[]" size="50" placeholder="esx01.sexibyt.es;esx02.sexibyt.es">\n                    </div>\n                    <button class="btn btn-danger remove_field">Remove</button>\n                  </div>'); //add input box
        }
      });
      $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); x--;
      })
    });
  </script>
<?php require("footer.php"); ?>
