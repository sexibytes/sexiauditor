<?php require("session.php"); ?>
<?php
$isAdminPage = true;
$title = "SexiAuditor vSphere Credential Store";
require("header.php");
require("helper.php");
?>
	<div class="container"><br/>
		<div class="panel panel-default">
			<div class="panel-heading"><h3 class="panel-title">Credential Store Notes</h3></div>
			<div class="panel-body"><ul>
				<li>The credential store is used to store credential that will be used for vCenter query, it use vSphere SDK Credential Store Library</li>
				<li>Please refer to the <a href="http://www.sexiauditor.fr/">project website</a> and documentation for more information.</li>
			</ul></div>
		</div>
		<h2><span class="glyphicon glyphicon-briefcase"></span> SexiAuditor Credential Store</h2>
<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$safe_submit = secureInput($_POST["submit"]);
		$safe_vcenter = secureInput($_POST["input-vcenter"]);
		$username = secureInput(str_replace("\\","\\\\",$_POST["input-username"]));
		switch ($safe_submit) {
			case "addmodify":
				$password = secureInput($_POST["input-password"]);
				$errorHappened = false;
				if (empty($safe_vcenter) or empty($username) or empty($password)) {
					$errorHappened = true;
					$errorMessage = "All mandatory values have not been provided.";
				} elseif (!isHttpAvailable($safe_vcenter)) {
					$errorHappened = true;
					$errorMessage = "Cannot connect to server " . $safe_vcenter . ", please check firewall access.";
				} elseif (!filter_var($safe_vcenter, FILTER_VALIDATE_IP) and (gethostbyname($safe_vcenter) == $safe_vcenter)) {
					$errorHappened = true;
					$errorMessage = "vCenter IP or FQDN is not correct.";
				} elseif (shell_exec("/usr/lib/vmware-vcli/apps/general/credstore_admin.pl --credstore " . $credstoreFile . " list --server " . $safe_vcenter . " | grep " . $safe_vcenter . " | wc -l") > 0) {
					$errorHappened = true;
					$errorMessage = "vCenter IP or FQDN is already in credential store, duplicate entry is not supported.";
				} elseif (preg_match("/^([a-zA-Z0-9-_.]*)\\\\?([a-zA-Z0-9-_.]+)$|^([a-zA-Z0-9-_.]*)$|^([a-zA-Z0-9-_.]+)@([a-zA-Z0-9-_.]*)$/", $username) == 0) {
					$errorHappened = true;
					$errorMessage = "Bad username format, supported format are DOMAIN\USERNAME, USERNAME, USERNAME@DOMAIN.TLD";
				} else {
					# if input seems to be well-formated, we just need to test a connection query
					exec("/usr/lib/vmware-vcli/apps/general/connect.pl --server " . escapeshellcmd($safe_vcenter) . " --username '" . $username . "' --password '" . $password . "'", $null, $return_var);
					if ($return_var) {
						$errorHappened = true;
						$errorMessage = "Cannot complete login due to an incorrect user name or password";
					}
				}
				if ($errorHappened) {
					echo '	<div class="alert alert-danger" role="alert">
		<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
		<span class="sr-only">Error:</span>
		' . $errorMessage . '
	</div>';
				} else {
					echo '	<div class="alert alert-success" role="alert">
		<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
		<span class="sr-only">Success:</span>';
					echo shell_exec("/usr/lib/vmware-vcli/apps/general/credstore_admin.pl --credstore " . $credstoreFile . " add --server " . $safe_vcenter . " --username " . escapeshellcmd($username) . " --password " . escapeshellcmd($password));
					// Once newly vCenter has been added, we want the inventory to be updated
					shell_exec("sudo /bin/bash /var/www/scripts/updateInventory.sh > /dev/null 2>/dev/null &");
					echo '	</div>';
					echo '<script type="text/javascript">setTimeout(function(){ location.replace("credstore.php"); }, 1000);</script>';
				}
				break;
			case "delete-vcentry":
                echo '  <div class="alert alert-warning" role="warning">
		<h4><span class="glyphicon glyphicon-alert" aria-hidden="true"></span>
                <span class="sr-only">Warning:</span>
		Confirmation needed!</h4>
		You are about to delete entry from VMware Credential Store for ' . $safe_vcenter . '. Are you sure about this? We mean, <strong>really sure</strong>?<br />
		<form class="form" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
                	<input type="hidden" name="input-vcenter" value="' . $safe_vcenter . '">
                        <input type="hidden" name="input-username" value="' . $username . '">
			<p><button name="submit" class="btn btn-warning" value="delete-vcentry-confirmed">Delete entry</button></p>
		</form>';
				echo '  </div>';
			break;
			case "delete-vcentry-confirmed":
	            echo '  <div class="alert alert-success" role="alert">
                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                <span class="sr-only">Success:</span>';
				echo shell_exec("/usr/lib/vmware-vcli/apps/general/credstore_admin.pl --credstore " . $credstoreFile . " remove --server " . $safe_vcenter . " --username " . escapeshellcmd($username)) . "Refreshing...";
				echo '  </div>';
				echo '<script type="text/javascript">setTimeout(function(){ location.replace("credstore.php"); }, 1000);</script>';
			break;
		}
	}
?>
		<table class="table table-hover">
      		<thead><tr>
				<th class="col-sm-5">vCenter Name</th>
				<th class="col-sm-4">Username</th>
				<th class="col-sm-2">Password</th>
				<th class="col-sm-1">&nbsp;</th>
       		</tr></thead>
	    <tbody>
		<tr><form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
			<td><input type="text" class="form-control" name="input-vcenter" placeholder="vCenter IP or FQDN" aria-describedby="vcenter-label"></td>
			<td><input type="text" class="form-control" name="input-username" placeholder="Username" aria-describedby="username-label"></td>
			<td><input type="password" class="form-control" name="input-password" placeholder="Password" aria-describedby="password-label"></td>
			<td><button name="submit" class="btn btn-success" value="addmodify">Add</button></td>
		</form></tr>
<?php
	$credstoreData = shell_exec("/usr/lib/vmware-vcli/apps/general/credstore_admin.pl --credstore " . $credstoreFile . " list");
	foreach(preg_split("/((\r?\n)|(\r\n?))/", $credstoreData) as $line) {
		if (strlen($line) == 0) { break; }
		if (preg_match('/^(?:(?!Server).)/', $line)) {
			$lineObjects = preg_split('/\s+/', $line);
			echo '              <tr>
              		<td>' . $lineObjects[0] . "</td>
			<td>" . $lineObjects[1] . '</td>
			<td>***********</td>';
			echo '			<td><form class="form" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
				<input type="hidden" name="input-vcenter" value="' . $lineObjects[0] . '">
				<input type="hidden" name="input-username" value="' . $lineObjects[1] . '">
				<button name="submit" class="btn btn-danger" value="delete-vcentry">Delete</button>
			</form></td>
		</tr>
';
		}
	}
?>
	      </tbody>
	    </table>
	</div>
<?php require("footer.php"); ?>
