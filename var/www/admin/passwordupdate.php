<?php require("session.php"); ?>
<?php
$title = "Password Update";
require("header.php");
require("helper.php");

$xmlPasswordsFile = "/var/www/admin/conf/passwords.xml";
if (is_writeable($xmlPasswordsFile)):
    $xmlPassword = simplexml_load_file($xmlPasswordsFile);

	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$issue = true;
		do {
			if (!isset($_POST['currentPassword']) || !isset($_POST['newPassword']) || !isset($_POST['newPasswordConfirm']) || secureInput($_POST['currentPassword']) == '' || secureInput($_POST['newPassword']) == '' || secureInput($_POST['newPasswordConfirm']) == '') {
				$issueMessage = 'Missing mandatory values for "' . secureInput($_SESSION['username']) . '" passwords';
				break;
			}
			if (secureInput($_POST['newPassword']) != secureInput($_POST['newPasswordConfirm'])) {
				$issueMessage = 'New password confirmation for "' . secureInput($_SESSION['username']) . '" username doesn\'t match';
				break;
			}
			$currentHash = $xmlPassword->xpath('/passwords/password[id="' . secureInput($_SESSION['username']) . '"]/hash');
			if (hash('sha512', secureInput($_POST['currentPassword'])) != $currentHash[0][0]) {
				$issueMessage = 'Bad password for "' . secureInput($_SESSION['username']) . '" username';
				break;
			}
			$currentHash[0][0] = hash('sha512', secureInput($_POST['newPassword']));
			if (!$xmlPassword->asXML($xmlPasswordsFile)) {
				$issueMessage = 'Error updating new password for "' . secureInput($_SESSION['username']) . '" username';
				break;
			}
			$issue = false;
		} while (0);
		if ($issue) {
			echo '      <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $issueMessage . '</div>';
		} else {
			echo '      <div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Success:</span> Password updated successfuly, you can logout/login with your new infos</div>';
		}
	}
?>

<div class="container">
	<h1><i class="glyphicon glyphicon glyphicon-user"></i> User '<?php echo $_SESSION['username']; ?>' password update</h1>
	<form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
	<div class="form-group">
		<label for="currentPassword" class="col-sm-6 control-label">Current password for '<?php echo $_SESSION['username']; ?>' username</label>
		<div class="col-sm-4"><input type="password" class="form-control" name="currentPassword" id="currentPassword"></div>
	</div>
	<div class="form-group">
		<label for="newPassword" class="col-sm-6 control-label">New password for '<?php echo $_SESSION['username']; ?>' username</label>
		<div class="col-sm-4"><input type="password" class="form-control" name="newPassword" id="newPassword"></div>
	</div>
	<div class="form-group">
		<label for="newPasswordConfirm" class="col-sm-6 control-label">Confirm new password for '<?php echo $_SESSION['username']; ?>' username</label>
		<div class="col-sm-4"><input type="password" class="form-control" name="newPasswordConfirm" id="newPasswordConfirm"></div>
	</div>
	<!-- <input type="hidden" name="username" value=""> -->
	<div class="form-group">
		<div class="col-sm-offset-6 col-sm-6"><button type="submit" class="btn btn-primary">Update password</button></div>
	</div>
	</form>
</div>
<?php
else:
    echo '  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlPasswordsFile . ' is not existant or not writeable</div>';
endif; /* check xml file */
?>
<?php require("footer.php"); ?>
