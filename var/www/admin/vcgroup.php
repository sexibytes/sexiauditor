<?php
require("session.php");
require("dbconnection.php");
$isAdminPage = true;
$title = "vCenter Logical Group";
require("header.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
  
  # As we don't keep history of group, we can TRUNCATE db and insert new values each time user validate, which is quicker than check every values
  $db->rawQuery("TRUNCATE TABLE vcenterGroups;");
  $data = array();
  
  if (!empty($_POST['groups']))
  {
    
    for ($i = 0; $i < count($_POST['groups']); $i++)
    {
      
      if (!empty($_POST['groups'][$i]) && !empty($_POST['members'][$i]))
      {
        
        foreach (explode(";", $_POST['members'][$i]) as $memberVC)
        {

          # Values are OK, so we add them to hash to be inserted
          array_push($data, array("group_name" => $_POST['groups'][$i], "vcenter_name" => $memberVC));

        }

      }
      
    }

    if (count($data) > 0)
    {
      
      $ids = $db->insertMulti('vcenterGroups', $data);

      if (!$ids)
      {
        
        echo '    <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> There was an error during settings update</div>';
        
      }
      else
      {
        
        echo '    <div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Success:</span> Settings successfully saved</div>';
        echo "    <script type=\"text/javascript\">$(window).on('load', function(){ setTimeout(function(){ $('.alert').fadeOut() }, 2000); });</script>";
        
      } # END if (!$ids)
      
    } # END if (count($data) > 0)
    
  } # END if (!empty($_POST['groups']))

} # END if ($_SERVER['REQUEST_METHOD'] == 'POST')

$db->groupBy ("group_name");
$vcenterGroups = $db->get("vcenterGroups", NULL, "group_name, GROUP_CONCAT(vcenter_name SEPARATOR ';') as members");
?>
  <div class="container"><br/>
    <div class="panel panel-primary">
      <div class="panel-heading"><h3 class="panel-title">vCenter Group Notes</h3></div>
      <div class="panel-body"><ul>
        <li>This page can be used to create logical group for your vCenters.</li>
        <li>These groups will be used in inventory pages (in the column named 'Group') and could be useful to define vCenter per Business Line.</li>
        <li>Please refer to the <a href="http://www.sexiauditor.fr/">project website</a> and documentation for more information.</li>
      </ul></div>
    </div>
    <h2><i class="glyphicon glyphicon-th-list"></i> vCenter Groups:</h2>
    <form class="form-inline" method="POST">
      <div class="input_fields_wrap">
<?php foreach ($vcenterGroups as $vcenterGroup) : ?>
        <div style="padding-bottom:10px;">
          <div class="form-group">
            <label>Group Name</label>
            <input type="text" class="form-control" name="groups[]" value="<?php echo $vcenterGroup["group_name"]; ?>">
          </div>&nbsp;
          <div class="form-group">
            <label>vCenters <small>(separated by semi-colon)</small></label>
            <input type="text" class="form-control" name="members[]" size="40" value="<?php echo $vcenterGroup["members"]; ?>">
          </div>
          <button class="btn btn-danger remove_field">Remove</button>
        </div>
<?php endforeach; ?>
      </div>
      <br>
      <button class="btn btn-default add_field_button">Add More Fields</button>
      <button type="submit" class="btn btn-success">Submit</button>
    </form>
  </div>
  <script type="text/javascript">
    $(document).ready(function() {
      // var max_fields      = 50; //maximum input boxes allowed
      var wrapper         = $(".input_fields_wrap"); //Fields wrapper
      var add_button      = $(".add_field_button"); //Add button ID
      var x = 1; //initlal text box count
      $(add_button).click(function(e){ //on add input button click
        e.preventDefault();
        $(wrapper).append('        <div style="padding-bottom:10px;">\n                     <div class="form-group">\n                      <label>Group Name</label>\n                      <input type="text" class="form-control" name="groups[]" placeholder="GOLD-PROD">\n                    </div>&nbsp;\n                    <div class="form-group">\n                      <label>vCenters <small>(separated by semi-colon)</small></label>\n                      <input type="text" class="form-control" name="members[]" size="40" placeholder="vcenter01.sexibyt.es;vcenter02.sexibyt.es">\n                    </div>\n                    <button class="btn btn-danger remove_field">Remove</button>\n                  </div>'); //add input box
      });
      $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); x--;
      })
    });
  </script>
<?php require("footer.php"); ?>
