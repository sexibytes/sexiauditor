<?php
require("session.php");
require("dbconnection.php");
$isAdminPage = true;
$title = "Users Management";
require("header.php");
require("helper.php");
require 'phpmailer/PHPMailerAutoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
  
  $issue = true;
  
  do
  {
    
    $db->where('configid', "smtpAddress");
    $smtpHost = $db->getOne('config');
    $smtpHost = $smtpHost['value'];
    $db->where('configid', "senderMail");
    $smtpSender = $db->getOne('config');
    $smtpSender = $smtpSender['value'];
    $db->where('configid', "recipientMail");
    $smtpRecipient = $db->getOne('config');
    $smtpRecipient = $smtpRecipient['value'];
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = false;
    $mail->setFrom($smtpSender);
    $mail->isHTML(true);

    switch ($_POST['submit'])
    {
      
      case "add" :
      
        if (!isset($_POST['input-displayname']) || !isset($_POST['input-username']) || !isset($_POST['input-password']) || !isset($_POST['input-email']) || !isset($_POST['input-role']) || secureInput($_POST['input-displayname']) == '' || secureInput($_POST['input-username']) == '' || secureInput($_POST['input-password']) == '' || secureInput($_POST['input-email']) == '' )
        {
          
          $issueMessage = 'Missing mandatory values, please fill all requested fields';
          break;
          
        } # END if checks
        
        $db->where('username', secureInput($_POST['input-username']));
        $resultUser = $db->get('users');
        
        if ($db->count > 0)
        {
          
          $issueMessage = 'Username "' . secureInput($_POST['input-username']) . '" already exists';
          break;
          
        } # END if ($db->count > 0)
        
        $data = Array ("username" => secureInput($_POST['input-username']),
                       "displayname" => secureInput($_POST['input-displayname']),
                       "email" => secureInput($_POST['input-email']),
                       "role" => secureInput($_POST['input-role']),
                       "password" => hash('sha512', secureInput($_POST['input-password']))
        );
        $id = $db->insert ('users', $data);
        
        if (!$id)
        {
          
          $issueMessage = 'Error adding new user "' . secureInput($_POST['username']);
          break;
          
        } # END if (!$id)
        
        $db->where('id', secureInput($_POST['input-role']));
        $resultRole = $db->getOne('roles');
        $mail->addAddress(secureInput($_POST['input-email']));
        $mail->Subject = '['.strtoupper(gethostname()).'] New user has been created';
        $mail->Body    = sendMailNewUser(secureInput($_POST['input-username']), secureInput($_POST['input-displayname']), secureInput($_POST['input-password']), $resultRole['role'], "http://". $_SERVER['HTTP_HOST']);
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
        if (!$mail->send())
        {
          
          $issueMessage = 'Message could not be sent, with the following error: ' . $mail->ErrorInfo;
          break;
          
        } # END if (!$mail->send())
        
        $okMessage = 'New user "' . secureInput($_POST['input-username']) . '" have been successfuly added.';
        $issue = false;
        
      break; # END case "add" :
      
      case "edit-username" :
      
        if (!isset($_POST['input-new-displayname']) || !isset($_POST['input-new-email']) || (!isset($_POST['input-new-role']) && $_POST['input-username'] != "admin") || secureInput($_POST['input-new-displayname']) == '' || secureInput($_POST['input-new-email']) == '' )
        {
          
          $issueMessage = 'Missing mandatory values, please fill all requested fields';
          break;
          
        } # END if checkd
        
        $data = Array ( "displayname" => secureInput($_POST["input-new-displayname"]),
                        "email" => secureInput($_POST['input-new-email']),
                        "role" => secureInput($_POST['input-new-role'])
                      );
        $db->where ('username', secureInput($_POST['input-username']));
        
        if (!$db->update ('users', $data))
        {
          
          $issueMessage = 'Error editing username "' . secureInput($_POST['username']);
          break;
          
        } # END if (!$db->update ('users', $data))
        
        $okMessage = 'User "' . secureInput($_POST['input-username']) . '" have been successfuly edited.';
        $issue = false;
        
      break; # END case "edit-username" :
      
      case "resetpw" :
      
        if (!isset($_POST['input-new-password']) || secureInput($_POST['input-new-password']) == '')
        {
          
          $issueMessage = 'Empty password is not supported';
          break;
          
        } # END if (!isset($_POST['input-new-password']) || secureInput($_POST['input-new-password']) == '')
        
        $data = Array ("password" => hash('sha512', secureInput($_POST['input-new-password'])));
        $db->where ('username', secureInput($_POST['input-username']));
        
        if (!$db->update ('users', $data))
        {
          
          $issueMessage = 'Error updating new password for "' . secureInput($_POST['input-username']) . '" username';
          break;
          
        } # END if (!$db->update ('users', $data))
        
        $mail->addAddress($smtpRecipient);
        $mail->Subject = '['.strtoupper(gethostname()).'] Password reset initiated for user ' . secureInput($_POST['input-username']);
        $mail->Body    = 'The password for user ' . secureInput($_POST['input-username']) . ' have been reseted by ' . secureInput($_SESSION['username']) . '<br />New password is '. secureInput($_POST['input-new-password']);
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
        if (!$mail->send())
        {
          
          $issueMessage = 'Message could not be sent, with the following error: ' . $mail->ErrorInfo;
          break;
          
        } # END if (!$mail->send())
        
        $okMessage = 'Password for user "' . secureInput($_POST['input-username']) . '" have been successfuly reseted.';
        $issue = false;
        
      break; # END case "resetpw" :
      
      case "delete-username" :

        $db->where('username', secureInput($_POST['input-username']));
        
        if (!$db->delete('users'))
        {
          
          $issueMessage = 'Error deleting user "' . secureInput($_POST['username']) . '" username';
          break;
          
        } # END if (!$db->delete('users'))
        
        $okMessage = 'User "' . secureInput($_POST['input-username']) . '" have been successfuly deleted.';
        $issue = false;
        
      break; # END case "delete-username" :
      
    } # END switch ($_POST['submit'])
    
  } while (0); # END do
  
  if ($issue)
  {
    
    echo '      <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $issueMessage . '</div>';
    
  }
  else
  {
    
    echo '      <div class="alert alert-success" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Success:</span> ' . $okMessage . '</div>';
    echo "      <script type=\"text/javascript\">$(window).on('load', function(){ setTimeout(function(){ $('.alert').fadeOut() }, 3000); });</script>";
    
  } # END if ($issue)
  
} # END if ($_SERVER['REQUEST_METHOD'] == 'POST')

$resultUsers = $db->get('users');
?>
  <!--override default settings to display custom color -->
  <style>.btn:focus { outline: none; }</style>
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
        <button name="radio" class="btn btn-default"><input type="radio" name="input-role" value="1">Admin</button>
        <button name="radio" class="btn btn-default active"><input type="radio" name="input-role" value="2" checked="">Reader</button>
      </div></td>
      <td><button name="submit" class="btn btn-success" value="add">Add</button></td>
    </form></tr>
<?php
foreach ($resultUsers as $user)
{
  
  echo '              <tr>';
  echo '            <form class="form" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">';
  echo '            <td>' . $user['displayname'] . '</td>';
  echo '            <td>' . $user['username'] . '</td>';
  echo '            <td>***********</td>';
  echo '            <td>' . $user['email'] . '</td>';
  echo '            <td>' . (($user['role'] == '1') ? '<i class="glyphicon glyphicon-king"></i>' : '<i class="glyphicon glyphicon-user"></i>'). '</td>';
  echo '      <td>
        <input type="hidden" name="input-username" value="' . $user['username'] . '">';
      echo '<a href="#edit-' . $user['username'] . '" class="btn btn-warning" data-toggle="modal" data-tooltip="Edit user properties"><i class="glyphicon glyphicon-pencil"></i></a>
        <div id="edit-' . $user['username'] . '" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Edit properties for user "' . $user['username'] . '"</h4>
                    </div>
                    <div class="modal-body">
                      <div class="form-group">
                        <label for="input-new-displayname" class="col-sm-4 control-label">Display Name</label>
                        <div class="col-sm-8"><input type="text" class="form-control" name="input-new-displayname" id="input-new-displayname" value="' . $user['displayname'] . '"></div>
                      </div>
                      <div class="form-group">
                        <label for="input-new-email" class="col-sm-4 control-label">Email</label>
                        <div class="col-sm-8"><input type="email" class="form-control" name="input-new-email" id="input-new-email" value="' . $user['email'] . '"></div>
                      </div>';
      if ($user['username'] != 'admin') {
        echo '                      <div class="form-group">
                        <label for="input-new-role" class="col-sm-4 control-label">Role</label>
                        <div class="col-sm-8"><div class="btn-group" data-toggle="buttons">
                          <button name="radio" class="btn btn-default' . (($user['role'] == "1") ? " active" : "") . '"><input type="radio" name="input-new-role" value="1"' . (($user['role'] == "1") ? " checked=\"\"" : "") . '>Admin</button>
                          <button name="radio" class="btn btn-default' . (($user['role'] == "2") ? " active" : "") . '"><input type="radio" name="input-new-role" value="2"' . (($user['role'] == "2") ? " checked=\"\"" : "") . '>Reader</button>
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
      if ($user['username'] != 'admin') {
        echo '<a href="#resetpw-' . $user['username'] . '" class="btn btn-info" data-toggle="modal" data-tooltip="Reset Password"><i class="glyphicon glyphicon-lock"></i></a>
          <div id="resetpw-' . $user['username'] . '" class="modal fade">
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
        echo '<a href="#delete-' . $user['username'] . '" class="btn btn-danger" data-toggle="modal" data-tooltip="Delete User"><i class="glyphicon glyphicon-trash"></i></a>
          <div id="delete-' . $user['username'] . '" class="modal fade">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 class="modal-title">Confirmation</h4>
                      </div>
                      <div class="modal-body"><p>Do you want to delete user "' . $user['username'] . '" ?</p></div>
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

} # END foreach ($resultUsers as $user)

?>
        </tbody>
      </table>
  </div>
<?php require("footer.php"); ?>
