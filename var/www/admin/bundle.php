<?php
require("session.php");
$title = "ESX Bundle";
$additionalStylesheet = array(  'css/jquery.dataTables.min.css',
                                'css/bootstrap-datetimepicker.css');
$additionalScript = array(  'js/jquery.dataTables.min.js',
                            'js/jszip.min.js',
                            'js/dataTables.autoFill.min.js',
                            'js/dataTables.bootstrap.min.js',
                            'js/dataTables.buttons.min.js',
                            'js/autoFill.bootstrap.min.js',
                            'js/buttons.bootstrap.min.js',
                            'js/buttons.colVis.min.js',
                            'js/buttons.html5.min.js',
                            'js/file-size.js',
                            'js/moment.js',
                            'js/bootstrap-datetimepicker.js');
require("header.php");
require("helper.php");

try {
  # Main class loading
  $check = new SexiCheck();
  # Header generation
  $check->displayHeader($_SERVER['SCRIPT_NAME'], $visible = false);
} catch (Exception $e) {
  # Any exception will be ending the script, we want exception-free run
  # CSS hack for navbar margin removal
  echo '  <style>#wrapper { margin-bottom: 0px !important; }</style>'."\n";
  require("exception.php");
  exit;
}
?>

    <table id="esxBundles" class="table table-hover">
      <thead><tr>
        <th class="col-sm-8 text-left">Filename</th>
        <th class="col-sm-4 text-right">Size</th>
      </tr></thead>
      <tbody>
<?php
$dir = glob('/var/www/admin/esxbundle/'.str_replace("/", "", $check->getSelectedDate()).'*', GLOB_ONLYDIR)[0]."/";

if ($handle = opendir($dir))
{
  
  while (false !== ($file = readdir($handle)))
  {
      
    if ($file != "." && $file != ".." && $file != ".gitignore") {
      echo '        <tr>'."\n";
      echo '          <td class="text-left"><i class="glyphicon glyphicon-file"></i> <a href="/esxbundle/' . split("/", $dir)[5] . "/" . $file . '">' . $file . '</a></td>'."\n";
      echo '          <td class="text-right">' . human_filesize(filesize($dir.$file)) . '</td>'."\n";
      echo '        </tr>'."\n";
    }
    
  } # END while (false !== ($file = readdir($handle)))
  
  closedir($handle);

} # END if ($handle = opendir($dir))

?>
      </tbody>
    </table>
  </div>

  <script type="text/javascript">
    $(document).ready( function () {
      var table = $('#esxBundles').DataTable( {
        "search": {
          "smart": false,
          "regex": true
        }
      } );
    } );
    document.getElementById("wrapper-container").style.display = "block";
    document.getElementById("purgeLoading").style.display = "none";
  </script>

<?php require("footer.php"); ?>
