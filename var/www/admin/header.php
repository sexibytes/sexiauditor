<?php

if (isset($isAdminPage) && $isAdminPage && (!isset($_SESSION['role']) || $_SESSION['role'] != '1'))
{
  # User tried to access admin page with user-only rights, kicking him out...
  header('Location: logout.php');

} # END if (isset($isAdminPage) && $isAdminPage && (!isset($_SESSION['role']) || $_SESSION['role'] != '1'))

?>
<!DOCTYPE HTML>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo $title; ?></title>
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="css/BootstrapXL.css">
  <link rel="stylesheet" type="text/css" href="css/sexiauditor.css">
<?php

if (isset($additionalStylesheet))
{
  
  # Dynamically adding additionnal CSS files
  foreach ($additionalStylesheet as $stylesheet )
  {
    
    echo '  <link rel="stylesheet" type="text/css" href="' . $stylesheet . '">' . "\n";
  
  } # END foreach ($additionalStylesheet as $stylesheet )
  
} # END if (isset($additionalStylesheet))

?>
  <script type="text/javascript" src="js/jquery-3.1.0.min.js"></script>
  <script type="text/javascript" src="js/bootstrap.min.js"></script>
  <script type="text/javascript" src="js/handlebars.min-latest.js"></script>
  <script type="text/javascript" src="js/typeahead.jquery.min.js"></script>
  <script type="text/javascript" src="js/bloodhound.min.js"></script>
<?php

if (isset($additionalScript))
{
  
  # Dynamically adding additionnal JS files
  foreach ($additionalScript as $script )
  {
    
    echo '  <script type="text/javascript" src="' . $script . '"></script>' . "\n";
  
  } # END foreach ($additionalScript as $script )
  
} # END if (isset($additionalScript))

require_once("class/SexiLang.class.php");
require_once("class/SexiHelper.class.php");
$sexihelper = new SexiHelper();
$sexilang = new SexiLang($sexihelper->getConfig('lang'));
?>
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="images/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
</head>
<body>
  <div id="wrapper">
<?php if (isset($isAdminPage) && $isAdminPage): ?>
    <nav class="navbar navbar-default navbar-fixed-bottom navbar-danger">
      <div class=""><?php echo $sexilang->getLocaleText('ADMINRIGHTS'); ?></div>
    </nav>
<?php endif; ?>
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">
      <div class="col-sm-3 col-md-3 searchLookup">
        <div class="input-group" id="sexisearch">
          <input type="text" class="form-control typeahead" placeholder="SexiSearch" autocomplete="off" size="40">
        </div>
      </div>
      <div class="navbar-brand"><a href="index.php">SexiAuditor</a></div>
      <ul class="nav navbar-top-links navbar-right">
        <li><a href="passwordupdate.php"><?php echo $sexilang->getLocaleText('WELCOME'); ?> <?php echo (isset($_SESSION['displayname']) ? $_SESSION['displayname'] : (isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown')) . ((isset($_SESSION['role']) && $_SESSION['role'] == '1') ? ' <i class="glyphicon glyphicon-star"></i>' : ''); ?></a></li>
        <li><i class="glyphicon glyphicon-option-vertical" style="color: #BBB;"></i></li>
        <li class="dropdown">
          <a id="dLabel" class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
            <i class="glyphicon glyphicon-tasks"></i>  <i class="glyphicon glyphicon-triangle-bottom" style="font-size: 0.8em;"></i>
          </a>
<?php

# CSS hack for displaying admin columns
if (isset($_SESSION['role']) && $_SESSION['role'] == '1')
{
  
  $nbColumn = " columns-3";
  $widthColumn = "col-sm-4";
  
}
else
{
  
  $nbColumn = " columns-2";
  $widthColumn = "col-sm-6";
  
} # END if (isset($_SESSION['role']) && $_SESSION['role'] == '1')

?>
          <ul class="dropdown-menu multi-column<?php echo $nbColumn; ?>">
            <div class="row">
              <div class="<?php echo $widthColumn; ?>">
                <ul class="multi-column-dropdown">
                  <li><a href="index.php"><i class="glyphicon glyphicon-map-marker glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('USERDASHBOARD'); ?></a></li>
                  <li class="divider"></li>
                  <li class="importantLabel"><a href="inv.php"><i class="glyphicon glyphicon-list-alt glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('VMINVENTORY'); ?></a></li>
                  <li class="importantLabel"><a href="invhost.php"><i class="glyphicon glyphicon-list-alt glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('HOSTINVENTORY'); ?></a></li>
                  <li><a href="capacityplanning.php"><i class="glyphicon glyphicon-signal glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('CAPACITYPLANNING'); ?></a></li>
                  <li class="divider"></li>
                  <li><a href="check-vsan.php"><img src="images/vc-vsan.gif" class="glyphicon-custom" /> <?php echo $sexilang->getLocaleText('VSANCHECKS'); ?></a></li>
                  <li><a href="check-vcenter.php"><img src="images/vc-vcenter.gif" class="glyphicon-custom" /> <?php echo $sexilang->getLocaleText('VCENTERCHECKS'); ?></a></li>
                  <li><a href="check-cluster.php"><img src="images/vc-cluster.gif" class="glyphicon-custom" /> <?php echo $sexilang->getLocaleText('CLUSTERCHECKS'); ?></a></li>
                  <li><a href="check-host.php"><img src="images/vc-host.gif" class="glyphicon-custom" /> <?php echo $sexilang->getLocaleText('HOSTCHECKS'); ?></a></li>
                  <li><a href="check-datastore.php"><img src="images/vc-datastore.gif" class="glyphicon-custom" /> <?php echo $sexilang->getLocaleText('DATASTORECHECKS'); ?></a></li>
                  <li><a href="check-network.php"><img src="images/vc-network.gif" class="glyphicon-custom" /> <?php echo $sexilang->getLocaleText('NETWORKCHECKS'); ?></a></li>
                  <li><a href="check-vm.php"><img src="images/vc-vm.gif" class="glyphicon-custom" /> <?php echo $sexilang->getLocaleText('VMCHECKS'); ?></a></li>
                </ul>
              </div>
<?php if (isset($_SESSION['role']) && $_SESSION['role'] == '1'): ?>
              <div class="<?php echo $widthColumn; ?>">
                <ul class="multi-column-dropdown">
                  <li><a href="admin.php"><i class="glyphicon glyphicon-map-marker glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('ADMINDASHBOARD'); ?></a></li>
                  <li class="divider"></li>
                  <li><a href="credstore.php"><i class="glyphicon glyphicon-briefcase glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('CREDENTIALSTORE'); ?></a></li>
                  <li><a href="updater.php"><i class="glyphicon glyphicon-hdd glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('PACKAGEUPDATER'); ?></a></li>
                  <li><a href="moduleselector.php"><i class="glyphicon glyphicon-check glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('MODULESELECTOR'); ?></a></li>
                  <li><a href="showlog.php"><i class="glyphicon glyphicon-search glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('LOGVIEWER'); ?></a></li>
                  <li><a href="timetobuild.php"><i class="glyphicon glyphicon-time glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('TIMETOBUILD'); ?></a></li>
                  <li><a href="bundle.php"><i class="glyphicon glyphicon-floppy-disk glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('ESXBUNDLE'); ?></a></li>
                </ul>
              </div>
<?php endif; ?>
              <div class="<?php echo $widthColumn; ?>">
                <ul class="multi-column-dropdown">
<?php if (isset($_SESSION['role']) && $_SESSION['role'] == '1'): ?>
                  <li><a href="config.php"><i class="glyphicon glyphicon-pencil glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('MODULESETTINGS'); ?></a></li>
                  <li><a href="cpgroup.php"><i class="glyphicon glyphicon-th-list glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('CAPACITYPLANNINGGROUP'); ?></a></li>
                  <li><a href="vcgroup.php"><i class="glyphicon glyphicon-th-list glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('VCENTERGROUP'); ?></a></li>
                  <li><a href="users.php"><i class="glyphicon glyphicon-user glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('USERSMANAGEMENT'); ?></a></li>
                  <li><a href="import-export.php"><i class="glyphicon glyphicon-transfer glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('IMPORTEXPORT'); ?></a></li>
<?php endif; ?>
                  <li><a href="onetime.php"><i class="glyphicon glyphicon-book glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('ONETIMEREPORT'); ?></a></li>
                  <li><a href="pdfreports.php"><i class="glyphicon glyphicon-print glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('PDFREPORTS'); ?></a></li>
                  <li><a href="status.php"><i class="glyphicon glyphicon-screenshot glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('SCHEDULERSTATUS'); ?></a></li>
                  <li class="divider"></li>
                  <li><a href="passwordupdate.php"><i class="glyphicon glyphicon-asterisk glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('UPDATEPASSWORD'); ?></a></li>
                  <li><a href="about.php"><i class="glyphicon glyphicon-question-sign glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('ABOUTSEXIAUDITOR'); ?></a></li>
                  <li><a href="logout.php"><i class="glyphicon glyphicon-log-out glyphicon-custom"></i> <?php echo $sexilang->getLocaleText('LOGOUT'); ?></a></li>
                </ul>
              </div>
            </div>
          </ul>
        </li>
      </ul>
    </nav>
    <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="plan-info" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-body"><!-- /# content will goes here after ajax calls --></div>
          <div class="modal-footer">
            <a href="#" id="modal-previous" rel="modal" style="display:none;" class="btn btn-primary"><?php echo $sexilang->getLocaleText('GOBACK'); ?></a>
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $sexilang->getLocaleText('CLOSE'); ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
