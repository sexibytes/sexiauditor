<?php
require("dbconnection.php");
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
  require("helper.php");
  $issue = true;
  do {
    $db->where('username', secureInput($_POST['username']));
    $resultUser = $db->getOne('users');

    if ($db->count < 1) {
      $issueMessage = "Unknown username " . secureInput($_POST['username']);
      break;
    }

    if ($resultUser['password'] != hash('sha512', secureInput($_POST['password']))) {
      $issueMessage = "Bad password for username " . secureInput($_POST['username']);
      break;
    }

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
  } while (0);
}
?>
<!DOCTYPE HTML>
<html>
<head>
  <meta charset="utf-8">
  <title>Login to SexiAuditor</title>
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
        <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
<?php
if (isset($issue) && $issue) {
	echo '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $issueMessage . '</div>';
}
?>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
        </form>
      </div>
    </div>
  </div>
</div>
</body>
</html>
