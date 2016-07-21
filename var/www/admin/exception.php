<!DOCTYPE HTML>
<html>
<head>
  <meta charset="utf-8">
  <title>Exsheption issued</title>
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="css/exception.css">
</head>
<body>
  <div class="vertical-center">
    <div class="container">
      <div class="alert alert-danger" style="width:60%" role="alert">
        <strong>Attenshion !</strong><br /><br />
        You've reach this page due to an unexpected exsheption, but shtay calm, I'm here to help.<br />
        Pleashe find below more details about this exsheption:<br /><br />
        <pre>
          Exception ID =          <?php echo $_GET['e']; ?>&nbsp;
          Exception FullName =    <?php echo $_GET['e']; ?>&nbsp;
          Exception Description = <?php echo $_GET['e']; ?>&nbsp;
          Referer URL =           <?php echo $_SERVER["HTTP_REFERER"]; ?>
        </pre>
        <button type="button" class="btn btn-success">Please take me back to safety</button>
      </div>
    </div>
  </div>
  <div id="footer">
    <a href="https://en.wikipedia.org/wiki/Zardoz"><img src="../images/zardoz.png" /></a>
  </div>
</body>
</html>
