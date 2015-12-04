<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/sexigraf.css">
	
	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/jquery.dropdown.js"></script>
	<script type="text/javascript" src="js/php_file_tree_jquery.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
</head>
<body>
	<div id="wrapper">
<?php if (isset($isAdminPage) && $isAdminPage): ?>
        <nav class="navbar navbar-default navbar-fixed-bottom navbar-danger">
            <div class="">Beware of these awesome admin rights, with power comes great responsibility-ish !!!</div>
        </nav>
<?php endif; ?>
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <ul class="nav navbar-top-links navbar-right">
                <li><a href="index.php"><i class="glyphicon glyphicon-stats"></i> User View</a></li>
                <li><a href="admin.php"><i class="glyphicon glyphicon-cog"></i> Admin View</a></li>
                <li><i class="glyphicon glyphicon-option-vertical" style="color: #BBB;"></i></li>
                <li class="dropdown">
                    <a id="dLabel"  class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
                        <i class="glyphicon glyphicon-tasks"></i>  <i class="glyphicon glyphicon-triangle-bottom" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dLabel">
<?php if (isset($isAdminPage) && $isAdminPage): ?>
                        <li><a href="admin.php"><i class="glyphicon glyphicon-map-marker glyphicon-custom"></i> Summary</a></li>
                        <li><a href="index.php"><i class="glyphicon glyphicon-stats glyphicon-custom"></i> Switch to User View</a></li>
                        <li class="divider"></li>
                        <li><a href="credstore.php"><i class="glyphicon glyphicon-briefcase glyphicon-custom"></i> vSphere Credential Store</a></li>
                        <li><a href="updater.php"><i class="glyphicon glyphicon-hdd glyphicon-custom"></i> Package Updater</a></li>
                        <li><a href="purge.php"><i class="glyphicon glyphicon-trash glyphicon-custom"></i> Stats Remover</a></li>
                        <li><a href="refresh-inventory.php"><i class="glyphicon glyphicon-th-list glyphicon-custom"></i> Refresh Inventory</a></li>
                        <li><a href="showlog.php"><i class="glyphicon glyphicon-search glyphicon-custom"></i> Log Viewer</a></li>
<?php else: ?>
                        <li><a href="index.php"><i class="glyphicon glyphicon-map-marker glyphicon-custom"></i> Summary</a></li>
                        <li><a href="admin.php"><i class="glyphicon glyphicon-cog glyphicon-custom"></i> Switch to Admin View</a></li>
                        <li class="divider"></li>
<?php endif; ?>
                    </ul>
                </li>
            </ul>
        </nav>
	</div>
