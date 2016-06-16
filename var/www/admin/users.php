<?php require("session.php"); ?>
<?php
$isAdminPage = true;
$title = "Users Management";
require("header.php");
require("helper.php");
require 'PHPMailer/PHPMailerAutoload.php';
$xmlPasswordsFile = "/var/www/admin/conf/passwords.xml";
if (is_writeable($xmlPasswordsFile)):
  $xmlPassword = simplexml_load_file($xmlPasswordsFile);
  if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $issue = true;
    do {
      $xmlSettingsFile = "/var/www/admin/conf/configs.xml";
      if (is_readable($xmlSettingsFile)) {
        $xmlSettings = simplexml_load_file($xmlSettingsFile);
        $smtpHost = $xmlSettings->xpath('/configs/config[id="smtpAddress"]')[0]->value;
        $smtpSender = $xmlSettings->xpath('/configs/config[id="senderMail"]')[0]->value;
        $smtpRecipient = $xmlSettings->xpath('/configs/config[id="recipientMail"]')[0]->value;
        $smtpHost = $xmlSettings->xpath('/configs/config[id="smtpAddress"]')[0]->value;
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = false;
        $mail->setFrom($smtpSender);
        $mail->isHTML(true);
      } else {
        $issueMessage = $xmlSettingsFile . ' is not existant or not readable';
        break;
      }
      switch ($_POST['submit']) {
        case "add" :
          if (!isset($_POST['input-displayname']) || !isset($_POST['input-username']) || !isset($_POST['input-password']) || !isset($_POST['input-email']) || !isset($_POST['input-role']) || secureInput($_POST['input-displayname']) == '' || secureInput($_POST['input-username']) == '' || secureInput($_POST['input-password']) == '' || secureInput($_POST['input-email']) == '' ) {
            $issueMessage = 'Missing mandatory values, please fill all requested fields';
            break;
          }

          if (count($xmlPassword->xpath('/passwords/password[id="' . secureInput($_POST['input-username']) . '"]')) != 0) {
            $issueMessage = 'Username "' . secureInput($_POST['input-username']) . '" already exists';
            break;
          }
          $newUSer = $xmlPassword->addChild('password');
          $newUSer->addChild('id', secureInput($_POST['input-username']));
          $newUSer->addChild('displayname', secureInput($_POST['input-displayname']));
          $newUSer->addChild('email', secureInput($_POST['input-email']));
          $newUSer->addChild('role', secureInput($_POST['input-role']));
          $newUSer->addChild('hash', hash('sha512', secureInput($_POST['input-password'])));
          $dom = new DOMDocument("1.0");
          $dom->preserveWhiteSpace = false;
          $dom->formatOutput = true;
          $dom->loadXML($xmlPassword->asXML());
          if (!$dom->save($xmlPasswordsFile)) {
            $issueMessage = 'Error adding new user "' . secureInput($_POST['username']);
            break;
          }
          $mail->addAddress(secureInput($_POST['input-email']));
          $mail->Subject = 'User created on ' . gethostname();
          $mail->Body    = sendMailNewUser(secureInput($_POST['input-username']), secureInput($_POST['input-displayname']), secureInput($_POST['input-role']), "http://". $_SERVER['HTTP_HOST']);
          $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
          if(!$mail->send()) {
            $issueMessage = 'Message could not be sent, with the following error: ' . $mail->ErrorInfo;
            break;
          }
          $okMessage = 'New user "' . secureInput($_POST['input-username']) . '" have been successfuly added.';
          $issue = false;
        break;
        case "edit-username" :
          if (!isset($_POST['input-new-displayname']) || !isset($_POST['input-new-email']) || (!isset($_POST['input-new-role']) && $_POST['input-username'] != "admin") || secureInput($_POST['input-new-displayname']) == '' || secureInput($_POST['input-new-email']) == '' ) {
            $issueMessage = 'Missing mandatory values, please fill all requested fields';
            break;
          }
          $currentUser = $xmlPassword->xpath('/passwords/password[id="' . secureInput($_POST['input-username']) . '"]');
          $currentDisplayName = $currentUser[0][0]->displayname;
          $currentEmail = $currentUser[0][0]->email;
          $currentRole = $currentUser[0][0]->role;

          if ($currentDisplayName != secureInput($_POST["input-new-displayname"])) { $currentUser[0][0]->displayname = secureInput($_POST["input-new-displayname"]); }
          if ($currentEmail != secureInput($_POST["input-new-email"])) { $currentUser[0][0]->email = secureInput($_POST["input-new-email"]); }
          if ($currentRole != secureInput($_POST["input-new-role"])) { $currentUser[0][0]->role = secureInput($_POST["input-new-role"]); }

          $dom = new DOMDocument("1.0");
          $dom->preserveWhiteSpace = false;
          $dom->formatOutput = true;
          $dom->loadXML($xmlPassword->asXML());
          if (!$dom->save($xmlPasswordsFile)) {
            $issueMessage = 'Error updating new password for "' . secureInput($_POST['username']) . '" username';
            break;
          }
          $okMessage = 'User "' . secureInput($_POST['input-username']) . '" have been successfuly edited.';
          $issue = false;
        break;
        case "resetpw" :
          if (!isset($_POST['input-new-password']) || secureInput($_POST['input-new-password']) == '') {
            $issueMessage = 'Empty password is not supported';
            break;
          }
          $currentHash = $xmlPassword->xpath('/passwords/password[id="' . secureInput($_SESSION['username']) . '"]/hash');
          $currentHash[0][0] = hash('sha512', secureInput($_POST['input-new-password']));
          if (!$xmlPassword->asXML($xmlPasswordsFile)) {
            $issueMessage = 'Error updating new password for "' . secureInput($_SESSION['username']) . '" username';
            break;
          }
          $mail->addAddress($smtpRecipient);
          $mail->Subject = 'Password reset initiated for user ' . secureInput($_POST['input-username']);
          $mail->Body    = 'The password for user ' . secureInput($_POST['input-username']) . ' have been reseted by ' . secureInput($_SESSION['username']) . '<br />';
          $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
          if(!$mail->send()) {
            $issueMessage = 'Message could not be sent, with the following error: ' . $mail->ErrorInfo;
            break;
          }
          $okMessage = 'Password for user "' . secureInput($_POST['input-username']) . '" have been successfuly reseted.';
          $issue = false;
          exit;
        break;
        case "delete-username" :
          unset($xmlPassword->xpath('/passwords/password[id="' . $_POST['input-username'] . '"]')[0]->{0});
          if (!$xmlPassword->asXml($xmlPasswordsFile)) {
            $issueMessage = 'Error deleting user "' . secureInput($_POST['username']) . '" username';
            break;
          }
          $okMessage = 'User "' . secureInput($_POST['input-username']) . '" have been successfuly deleted.';
          $issue = false;
        break;
      }
    } while (0);
    if ($issue) {
      echo '      <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $issueMessage . '</div>';
    } else {
      echo '      <div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Success:</span> ' . $okMessage . '</div>';
      echo "      <script type=\"text/javascript\">$(window).load(function(){ setTimeout(function(){ $('.alert').fadeOut() }, 3000); });</script>";
    }
  }
  $xpathFullUsers = $xmlPassword->xpath("/passwords/password");
?>
  <div class="container">
    <h2><span class="glyphicon glyphicon-briefcase"></span> Users Management</h2>
    <table class="table table-hover">
          <thead><tr>
        <th class="col-sm">Display Name</th>
        <th class="col-sm">Username</th>
        <th class="col-sm">Password</th>
        <th class="col-sm">Email</th>
        <th class="col-sm">Role</th>
        <th class="col-sm">&nbsp;</th>
           </tr></thead>
      <tbody>
    <tr><form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
      <td><input type="text" class="form-control" name="input-displayname" placeholder="Display Name" aria-describedby="displayname-label"></td>
      <td><input type="text" class="form-control" name="input-username" placeholder="Username" aria-describedby="username-label"></td>
      <td><input type="password" class="form-control" name="input-password" placeholder="Password" aria-describedby="password-label"></td>
      <td><input type="email" class="form-control" name="input-email" placeholder="EMail" aria-describedby="email-label"></td>

      <td><div class="btn-group" data-toggle="buttons">
        <button name="radio" class="btn btn-default"><input type="radio" name="input-role" value="admin">Admin</button>
        <button name="radio" class="btn btn-default active"><input type="radio" name="input-role" value="reader" checked="">Reader</button>
      </div></td>
      <td><button name="submit" class="btn btn-success" value="add">Add</button></td>
    </form></tr>
<?php
    foreach ($xpathFullUsers as $user) {
      echo '              <tr>
            <form class="form" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">
            <td>' . $user->displayname . '</td>
            <td>' . $user->id . '</td>
            <td>***********</td>
            <td>' . $user->email . '</td>
            <td>' . (($user->role == 'admin') ? '<i class="glyphicon glyphicon-king"></i>' : '<i class="glyphicon glyphicon-user"></i>'). '</td>';
      echo '      <td>
        <input type="hidden" name="input-username" value="' . $user->id . '">';
      echo '<a href="#edit-' . $user->id . '" class="btn btn-warning" data-toggle="modal" data-tooltip="Edit user properties"><i class="glyphicon glyphicon-pencil"></i></a>
        <div id="edit-' . $user->id . '" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Edit properties for user "' . $user->id . '"</h4>
                    </div>
                    <div class="modal-body">
                      <div class="form-group">
                        <label for="input-new-displayname" class="col-sm-4 control-label">Display Name</label>
                        <div class="col-sm-8"><input type="text" class="form-control" name="input-new-displayname" id="input-new-displayname" value="' . $user->displayname . '"></div>
                      </div>
                      <div class="form-group">
                        <label for="input-new-email" class="col-sm-4 control-label">Email</label>
                        <div class="col-sm-8"><input type="email" class="form-control" name="input-new-email" id="input-new-email" value="' . $user->email . '"></div>
                      </div>';
      if ($user->id != 'admin') {
        echo '                      <div class="form-group">
                        <label for="input-new-role" class="col-sm-4 control-label">Role</label>
                        <div class="col-sm-8"><div class="btn-group" data-toggle="buttons">
                          <button name="radio" class="btn btn-default' . (($user->role == "admin") ? " active" : "") . '"><input type="radio" name="input-new-role" value="admin"' . (($user->role == "admin") ? " checked=\"\"" : "") . '>Admin</button>
                          <button name="radio" class="btn btn-default' . (($user->role == "reader") ? " active" : "") . '"><input type="radio" name="input-new-role" value="reader"' . (($user->role == "reader") ? " checked=\"\"" : "") . '>Reader</button>
                        </div></div>
                      </div>';
      }
      echo '                      <p class="text-warning"><small>If you don\'t save, your changes will be lost.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button name="submit" class="btn btn-success" value="edit-username">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>';
      if ($user->id != 'admin') {
        echo '<a href="#resetpw-' . $user->id . '" class="btn btn-info" data-toggle="modal" data-tooltip="Reset Password"><i class="glyphicon glyphicon-lock"></i></a>
          <div id="resetpw-' . $user->id . '" class="modal fade">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 class="modal-title">Reset password</h4>
                      </div>
                      <div class="modal-body">
                        <div class="form-group">
                          <label for="input-new-displayname" class="col-sm-4 control-label">New password</label>
                          <div class="col-sm-8"><input type="password" class="form-control" name="input-new-password" id="input-new-password" value=""></div>
                        </div>
                        <p class="text-warning"><small>A mail will be sent after password reset.</small></p>
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                          <button name="submit" class="btn btn-danger" value="resetpw">Reset password</button>
                      </div>
                  </div>
              </div>
          </div>';
        echo '<a href="#delete-' . $user->id . '" class="btn btn-danger" data-toggle="modal" data-tooltip="Delete User"><i class="glyphicon glyphicon-trash"></i></a>
          <div id="delete-' . $user->id . '" class="modal fade">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 class="modal-title">Confirmation</h4>
                      </div>
                      <div class="modal-body"><p>Do you want to delete user "' . $user->id . '" ?</p></div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                          <button name="submit" class="btn btn-danger" value="delete-username">Delete User</button>
                      </div>
                  </div>
              </div>
          </div>';
      }
      echo '</td>
      </form>
    </tr>';
  }
?>
        </tbody>
      </table>
  </div>
<?php
else:
  echo '  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> File ' . $xmlPasswordsFile . ' is not existant or not writeable</div>';
endif; /* check xml file */
?>
<?php require("footer.php"); ?>
