<?php require("session.php"); ?>
<?php
$isAdminPage = true;
$title = "Module Settings";
require("header.php");
require("helper.php");

$xmlSettingsFile = "/var/www/admin/conf/modulesettings.xml";
if (is_writeable($xmlSettingsFile)) {
    $xmlSettings = simplexml_load_file($xmlSettingsFile);
} else {
    echo '  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlSettingsFile . ' is not existant or not writeable</div>';
    require("footer.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach (array_keys($_POST) as $postKey) {
        $xmlSettings->xpath('/settings/setting[id="' . $postKey . '"]')[0]->value = $_POST[$postKey];
    }
    
    if ($xmlSettings->asXML( $xmlSettingsFile )) {
        echo '      <div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Success:</span> Settings successfully saved</div>';
        echo "      <script type=\"text/javascript\">$(window).load(function(){ setTimeout(function(){ $('.alert').fadeOut() }, 2000); });</script>";
    } else {
                echo '      <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Error:</span> There was an error during settings update</div>';
        // reloading previous file
        $xmlSettings = simplexml_load_file($xmlSettingsFile);
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
    foreach ($xmlSettings->xpath("/settings/setting") as $setting) {
        echo '		  <div class="form-group">
			<label for="' . $setting->id . '" class="col-sm-6 control-label">' . $setting->label . '</label>
			<div class="col-sm-4">';
		switch ($setting->type) {
			case 'boolean':
				echo '              <div class="btn-group" data-toggle="buttons">
					<button name="radio" class="btn btn-danger' . (($setting->value == 'disable') ? ' active': '' ) . '"><input type="radio" name="' . $setting->id . '" value="disable">No</button>
					<button name="radio" class="btn btn-success' . (($setting->value == 'enable') ? ' active': '' ) . '"><input type="radio" name="' . $setting->id . '" value="enable">Yes</button>
				</div>';
				break;
			case 'daily':
				echo '			  <select name="' . $setting->id . '" class="form-control">';
				for ($hour = 0; $hour <= 23; $hour++) { echo '			  	<option value="' . $hour . '"' . ($hour == $setting->value ? " selected" : "") . '>' . str_pad($hour, 2, 0, STR_PAD_LEFT) . 'h00</option>'; }
				echo '			  </select>';
				break;
			case 'weekly':
				echo '			  <select name="' . $setting->id . '" class="form-control">';
				for ($day = 0; $day <= 6; $day++) { echo '			  	<option value="' . $day . '"' . ($day == $setting->value ? " selected" : "") . '>' . jddayofweek($day, CAL_DOW_LONG) . '</option>'; }
				echo '			  </select>';
				break;
			case 'monthly':
				echo '			  <select name="' . $setting->id . '" class="form-control">';
				for ($month = 1; $month <= 31; $month++) { echo '			  	<option value="' . $month . '"' . ($month == $setting->value ? " selected" : "") . '>' . addOrdinalNumberSuffix($month) . '</option>'; }
				echo '			  </select>';
				break;
			default:
				echo '			  <input type="' . $setting->type . '" class="form-control" name="' . $setting->id . '" id="' . $setting->id . '" value="' . $setting->value . '">';
				break;
		}
        echo '			</div>
		  </div>';
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
