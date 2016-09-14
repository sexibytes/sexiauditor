<?php
require("session.php");
$title = "Object Information";
$additionalStylesheet = array( 'css/jquery.dataTables.min.css' );
$additionalScript = array(  'js/jquery.dataTables.min.js',
                            'js/jszip.min.js',
                            'js/dataTables.autoFill.min.js',
                            'js/dataTables.bootstrap.min.js',
                            'js/dataTables.buttons.min.js',
                            'js/autoFill.bootstrap.min.js',
                            'js/buttons.bootstrap.min.js',
                            'js/buttons.colVis.min.js',
                            'js/buttons.html5.min.js',
                            'js/file-size.js');
require("header.php");
require("helper.php");

try
{
  
  # Main class loading
  $check = new SexiCheck();
  
  if (empty($_GET['q']))
  {
    
    throw new Exception('Missing object ID. We have nothing to look for, so we are going back to what we were doing...');
    
  } # END if (empty($_GET['q']))
  
  # Header generation
  // $check->displayHeader($_SERVER['SCRIPT_NAME'], $visible = true);
  
}
catch (Exception $e)
{
  
  # Any exception will be ending the script, we want exception-free run
  # CSS hack for navbar margin removal
  echo '  <style>#wrapper { margin-bottom: 0px !important; }</style>'."\n";
  require("exception.php");
  exit;

} # END try

$queryId = $_GET['q'];

# retrieve object name based on object type
$objName = $queryId;

?>
  <div style="padding-top: 10px; padding-bottom: 10px;" class="container">
    <div class="row">
      <div class="col-lg-12 alert alert-info" style="padding: 6px; margin-top: 20px; text-align: center;">
        <h1 style="margin-top: 10px;">Information about <?php echo $objName; ?></h1>
      </div>
    </div>
  </div>
    
<?php require("footer.php"); ?>
