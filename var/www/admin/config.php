<?php
require("session.php");
require("dbconnection.php");
$isAdminPage = true;
$title = "Module Settings";
require("header.php");
require("helper.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $issueOnUpdate = false;
  foreach (array_keys($_POST) as $postKey) {
    $data = Array ('value' => $_POST[$postKey]);
    $db->where ('configid', preg_replace('/schedule-(\w+)/i', '${1}', $postKey));
    if (!$db->update ('config', $data)) {
      $issueOnUpdate = true;
    }
  }

  if ($issueOnUpdate) {
    echo '      <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> There was an error during settings update</div>';
  } else {
    echo '      <div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Success:</span> Settings successfully saved</div>';
    echo "      <script type=\"text/javascript\">$(window).on('load', function(){ setTimeout(function(){ $('.alert').fadeOut() }, 2000); });</script>";
  }
}

?>
  <!--override default settings to display custom color -->
  <style>
  .btn-danger, .btn-success {
    color: #333;
    background-color: #fff;
    border-color: #ccc;
  }
  .modulePath {
    font-style: italic;
    font-size: small;
  }
  </style>
  <div class="container">
    <h1>Modules Settings</h1>
    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
<?php
  $db->join("configtype t", "c.type=t.id", "LEFT");
  $db->orderBy("c.id","asc");
  $resultConfig = $db->get('config c');
  foreach ($resultConfig as $setting) {
    echo '      <div class="form-group">
    <label for="' . $setting['configid'] . '" class="col-sm-6 control-label">' . $setting['label'] . '</label>
    <div class="col-sm-4">'."\n";
    switch ($setting['type']) {
      case 'boolean':
        echo '          <div class="btn-group" data-toggle="buttons">
            <button name="radio" class="btn btn-danger' . (($setting['value'] == 'disable') ? ' active': '' ) . '"><input type="radio" name="' . $setting['configid'] . '" value="disable">No</button>
            <button name="radio" class="btn btn-success' . (($setting['value'] == 'enable') ? ' active': '' ) . '"><input type="radio" name="' . $setting['configid'] . '" value="enable">Yes</button>
          </div>'."\n";
        break;
      case 'daily':
        echo '          <select name="' . $setting['configid'] . '" class="form-control">'."\n";
        for ($hour = 0; $hour <= 23; $hour++) { echo '            <option value="' . $hour . '"' . ($hour == $setting['value'] ? " selected" : "") . '>' . str_pad($hour, 2, 0, STR_PAD_LEFT) . 'h00</option>'."\n"; }
        echo '          </select>'."\n";
        break;
      case 'weekly':
        echo '          <select name="' . $setting['configid'] . '" class="form-control">'."\n";
        for ($day = 0; $day <= 6; $day++) { echo '            <option value="' . $day . '"' . ($day == $setting['value'] ? " selected" : "") . '>' . jddayofweek($day, CAL_DOW_LONG) . '</option>'."\n"; }
        echo '          </select>'."\n";
        break;
      case 'monthly':
        echo '          <select name="' . $setting['configid'] . '" class="form-control">'."\n";
        for ($month = 1; $month <= 31; $month++) { echo '            <option value="' . $month . '"' . ($month == $setting['value'] ? " selected" : "") . '>' . addOrdinalNumberSuffix($month) . '</option>'."\n"; }
        echo '          </select>'."\n";
        break;
      case 'powerList':
        echo '          <select name="' . $setting['configid'] . '" class="form-control">'."\n";
        foreach ($powerChoice as $key => $value) { echo '            <option value="' . $key . '"' . ($key == $setting['value'] ? " selected" : "") . '>' . $value . '</option>'."\n"; }
        echo '          </select>'."\n";
        break;
      case 'servicePolicy':
        echo '          <select name="' . $setting['configid'] . '" class="form-control">'."\n";
        foreach ($servicePolicyChoice as $key => $value) { echo '            <option value="' . $key . '"' . ($key == $setting['value'] ? " selected" : "") . '>' . $value . '</option>'."\n"; }
        echo '          </select>'."\n";
        break;
      case 'language':
        echo '          <select name="' . $setting['configid'] . '" class="form-control">'."\n";
        foreach ($langChoice as $key => $value) { echo '            <option value="' . $key . '"' . ($key == $setting['value'] ? " selected" : "") . '>' . $value . '</option>'."\n"; }
        echo '          </select>'."\n";
        break;
      case 'number':
        echo '          <input type="number" min="0" class="form-control" name="' . $setting['configid'] . '" id="' . $setting['configid'] . '" value="' . $setting['value'] . '">'."\n";
        break;
      default:
        echo '          <input type="' . $setting['type'] . '" class="form-control" name="' . $setting['configid'] . '" id="' . $setting['configid'] . '" value="' . $setting['value'] . '">'."\n";
        break;
    }
    echo '        </div>
    </div>'."\n";
  }
?>
      <div class="form-group">
        <div class="col-sm-offset-6 col-sm-6">
          <button type="submit" class="btn btn-default">Save module settings</button>
        </div>
      </div>
    </form>
  </div>
<?php require("footer.php"); ?>
