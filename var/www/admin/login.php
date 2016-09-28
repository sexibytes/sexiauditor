<?php
require("dbconnection.php");
require("helper.php");

try
{
  
  # Main class loading
  $check = new SexiCheck();
  
}
catch (Exception $e)
{
  
  # Any exception will be ending the script, we want exception-free run
  # CSS hack for navbar margin removal
  echo '  <style>#wrapper { margin-bottom: 0px !important; }</style>'."\n";
  require("exception.php");
  exit;
  
} # END try

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
  
  $issue = true;
  
  do
  {
    
    $db->where('username', secureInput($_POST['username']));
    $resultUser = $db->getOne('users');

    if ($db->count < 1)
    {
      
      $issueMessage = $check->getLocaleText("UNKNOWNUSERNAME") . " " . secureInput($_POST['username']);
      break;
      
    } # END if ($db->count < 1)

    if ($resultUser['password'] != hash('sha512', secureInput($_POST['password'])))
    {
      
      $issueMessage = $check->getLocaleText("BADPASSWORD") . " " . secureInput($_POST['username']);
      break;
      
    } # END if ($resultUser['password'] != hash('sha512', secureInput($_POST['password'])))

    # everything is doing ok, we are good to go
    # session instanciation and variable definition
    $issue = false;
    session_name('SexiAuditor');
    session_start();
    $_SESSION['username'] = $resultUser['username'];
    $_SESSION['displayname'] = $resultUser['displayname'];
    $_SESSION['role'] = $resultUser['role'];
    $_SESSION['email'] = $resultUser['email'];
    $_SESSION['isLogged'] = true;
    header('Location: index.php');
    
  } # END do
  while (0);
  
}
elseif (!empty($_GET['e']) && $_GET['e'] == "timeout")
{
  
  $issue = true;
  $issueMessage = $check->getLocaleText("TIMEOUT");
  
} # END if ($_SERVER['REQUEST_METHOD'] == 'POST')

?>
<!DOCTYPE HTML>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo $check->getLocaleText("LOGINSEXIAUDITOR"); ?></title>
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="css/sexiauditor.css">
  <link rel="stylesheet" type="text/css" href="css/auth.css">
</head>
<body>
<div class="container">
  <div class="row">
    <div class="col-sm-6 col-md-4 col-md-offset-4">
      <div class="account-wall">
    		<img class="profile-img" src="images/unicorn.png" alt="">
        <form class="form-signin" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        <input type="text" name="username" class="form-control" placeholder="<?php echo $check->getLocaleText("USERNAME"); ?>" required autofocus>
        <input type="password" name="password" class="form-control" placeholder="<?php echo $check->getLocaleText("PASSWORD"); ?>" required>
<?php

if (!empty($issue))
{
  
	echo '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $issueMessage . '</div>';
  
} # END if (!empty($issue))

?>
        <button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo $check->getLocaleText("SIGNIN"); ?></button>
        </form>
      </div>
    </div>
  </div>
</div>
</body>
</html>
