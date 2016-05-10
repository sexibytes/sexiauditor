<?php if (isset($isAdminPage) && $isAdminPage && (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin')) { header('Location: logout.php'); } ?>
<!DOCTYPE HTML>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo $title; ?></title>
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="css/BootstrapXL.css">
  <link rel="stylesheet" type="text/css" href="css/sexigraf.css">
<?php
    if(isset($additionalStylesheet)) {
        foreach ($additionalStylesheet as $stylesheet ) { echo '  <link rel="stylesheet" type="text/css" href="' . $stylesheet . '">' . "\n"; }
    }
?>

  <script type="text/javascript" src="js/jquery.min.js"></script>
  <!-- <script type="text/javascript" src="js/jquery.dropdown.js"></script> -->
  <script type="text/javascript" src="js/bootstrap.min.js"></script>
<?php
    if(isset($additionalScript)) {
        foreach ($additionalScript as $script ) { echo '  <script type="text/javascript" src="' . $script . '"></script>' . "\n"; }
    }
?>
</head>
<body>
  <div id="wrapper">
<?php if (isset($isAdminPage) && $isAdminPage): ?>
    <nav class="navbar navbar-default navbar-fixed-bottom navbar-danger">
      <div class="">Beware of these awesome admin rights, with power comes great responsibility-ish !!!</div>
    </nav>
<?php endif; ?>
    <!-- <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0"> -->
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">
      <div class="navbar-brand">SexiAuditor</div>
      <ul class="nav navbar-top-links navbar-right">
        <li><a href="passwordupdate.php">Welcome <?php echo (isset($_SESSION['displayname']) ? $_SESSION['displayname'] : $_SESSION['username']) . ((isset($_SESSION['role']) && $_SESSION['role'] == 'admin') ? ' <i class="glyphicon glyphicon-star"></i>' : ''); ?></a></li>
        <li><i class="glyphicon glyphicon-option-vertical" style="color: #BBB;"></i></li>
        <li class="dropdown">
          <a id="dLabel" class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
              <i class="glyphicon glyphicon-tasks"></i>  <i class="glyphicon glyphicon-triangle-bottom" style="font-size: 0.8em;"></i>
          </a>
<?php
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
	$nbColumn = " columns-3";
	$widthColumn = "col-sm-4";
} else {
	$nbColumn = " columns-2";
	$widthColumn = "col-sm-6";
}
?>
          <ul class="dropdown-menu multi-column<?php echo $nbColumn; ?>">
            <div class="row">
              <div class="<?php echo $widthColumn; ?>">
                <ul class="multi-column-dropdown">
                  <li><a href="index.php"><i class="glyphicon glyphicon-map-marker glyphicon-custom"></i> User Dashboard</a></li>
                  <li class="divider"></li>
                  <li><a href="check-vcenter.php"><img src="images/vc-vcenter.gif" class="glyphicon-custom" /> vCenter Checks</a></li>
                  <li><a href="check-cluster.php"><img src="images/vc-cluster.gif" class="glyphicon-custom" /> Cluster Checks</a></li>
                  <li><a href="check-host.php"><img src="images/vc-host.gif" class="glyphicon-custom" /> Host Checks</a></li>
                  <li><a href="check-datastore.php"><img src="images/vc-datastore.gif" class="glyphicon-custom" /> Datastore Checks</a></li>
                  <li><a href="check-network.php"><img src="images/vc-network.gif" class="glyphicon-custom" /> Network Checks</a></li>
                  <li><a href="check-vm.php"><img src="images/vc-vm.gif" class="glyphicon-custom" /> VM Checks</a></li>
                  <li><a href="inv.php"><i class="glyphicon glyphicon-list-alt glyphicon-custom"></i> Global Inventory</a></li>
                </ul>
              </div>
<?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
              <div class="<?php echo $widthColumn; ?>">
                <ul class="multi-column-dropdown">
                  <li><a href="admin.php"><i class="glyphicon glyphicon-map-marker glyphicon-custom"></i> Admin Dashboard</a></li>
                  <li class="divider"></li>
                  <li><a href="credstore.php"><i class="glyphicon glyphicon-briefcase glyphicon-custom"></i> Credential Store</a></li>
                  <li><a href="updater.php"><i class="glyphicon glyphicon-hdd glyphicon-custom"></i> Package Updater</a></li>
                  <li><a href="moduleselector.php"><i class="glyphicon glyphicon-check glyphicon-custom"></i> Module Selector</a></li>
                  <li><a href="showlog.php"><i class="glyphicon glyphicon-search glyphicon-custom"></i> Log Viewer</a></li>
                  <li><a href="timetobuild.php"><i class="glyphicon glyphicon-time glyphicon-custom"></i> Time To Build</a></li>
                </ul>
              </div>
<?php endif; ?>
              <div class="<?php echo $widthColumn; ?>">
                <ul class="multi-column-dropdown">
<?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                  <li><a href="config.php"><i class="glyphicon glyphicon-pencil glyphicon-custom"></i> Module Settings</a></li>
                  <li><a href="users.php"><i class="glyphicon glyphicon-user glyphicon-custom"></i> Users Management</a></li>
<?php endif; ?>
                  <li><a href="onetime.php"><i class="glyphicon glyphicon-book glyphicon-custom"></i> One Time Report</a></li>
                  <li><a href="pdfreports.php"><i class="glyphicon glyphicon-print glyphicon-custom"></i> PDF Reports</a></li>
                  <li><a href="status.php"><i class="glyphicon glyphicon-screenshot glyphicon-custom"></i> Scheduler Status</a></li>
                  <li class="divider"></li>
                  <li><a href="passwordupdate.php"><i class="glyphicon glyphicon-asterisk glyphicon-custom"></i> Update Password</a></li>
                  <li><a href="logout.php"><i class="glyphicon glyphicon-log-out glyphicon-custom"></i> Logout</a></li>
                </ul>
              </div>
            </div>
          </ul>
        </li>
      </ul>
    </nav>
  </div>
