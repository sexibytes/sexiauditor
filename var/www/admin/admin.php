<?php 
session_name('Private');
if (session_status() != PHP_SESSION_ACTIVE ) { session_start(); }
#$private_id = session_id();
#if (!isset($_SESSION['viewState'])) { $_SESSION['viewState'] = 'user'; }
#$validViewState = array('user', 'admin');
# define default view if not or wrong specified
#if ((!isset($_GET['viewState']) || !in_array($_GET['viewState'], $validViewState)) && isset($isAdminPage) && !$isAdminPage) { $_GET['viewState'] = 'user'; }
#session_write_close();
$isAdminPage = TRUE;
#session_start();
$title = "SexiGraf summary";
require("header.php");
?>
	<div class="row" style="margin:0px;padding:30px 30px 0px 30px;">
		<div class="col-lg-4 col-md-6">
			<a href="credstore.php">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<div class="row">
							<div class="col-xs-2">
								<i class="glyphicon glyphicon-briefcase" style="font-size: 2em;"></i>
							</div>
							<div class="col-xs-10 text-right">
								<div class="huge">Credential Store</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<span class="pull-left">In this section, you\'ll be able to manage your VMware Credential Store. This store is used to set up your vCenter information to allow SexiGraf query what\'s needed.</span>
						<div class="clearfix"></div>
					</div>
				</div>
			</a>
		</div>
		<div class="col-lg-4 col-md-6">
			<a href="updater.php">
				<div class="panel panel-green">
					<div class="panel-heading">
						<div class="row">
							<div class="col-xs-2">
								<i class="glyphicon glyphicon-hdd" style="font-size: 2em;"></i>
							</div>
							<div class="col-xs-10 text-right">
								<div class="huge">Package Updater</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<span class="pull-left">Here you\'ll find the update process of SexiGraf. Once you have downloaded an update package, go on this section to give your appliance the State-Of-The-Art code!</span>
						<div class="clearfix"></div>
					</div>
				</div>
			</a>
		</div>
		<div class="col-lg-4 col-md-6">
			<a href="sandbox.php">
				<div class="panel panel-red">
					<div class="panel-heading">
						<div class="row">
							<div class="col-xs-2">
								<i class="glyphicon glyphicon-trash" style="font-size: 2em;"></i>
							</div>
							<div class="col-xs-10 text-right">
								<div class="huge">Sandbox</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<span class="pull-left">Sanbox</span>
						<div class="clearfix"></div>
					</div>
				</div>
			</a>
		</div>
	</div>
	<div class="row" style="margin:0px;padding:30px;">
		<div class="col-lg-4 col-md-6">
			<a href="refresh-inventory.php">
				<div class="panel panel-yellow">
					<div class="panel-heading">
						<div class="row">
							<div class="col-xs-2">
								<i class="glyphicon glyphicon-th-list" style="font-size: 2em;"></i>
							</div>
							<div class="col-xs-10 text-right">
								<div class="huge">Refresh Inventory</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<span class="pull-left">The Static Offline Inventory is automatically schedule to be updated every 6 hours. If you want to force a refresh, you can use this section to perform update.</span>
						<div class="clearfix"></div>
					</div>
				</div>
			</a>
		</div>
		<div class="col-lg-4 col-md-6">
			<a href="showlog.php">
				<div class="panel panel-grey">
					<div class="panel-heading">
						<div class="row">
							<div class="col-xs-2">
								<i class="glyphicon glyphicon-search" style="font-size: 2em;"></i>
							</div>
							<div class="col-xs-10 text-right">
								<div class="huge">Log Viewer</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<span class="pull-left">The Log Viewer will give you access to the content of SexiGraf log files. It is basically a web equivalent of the <code>tail -f</code> linux command. It should be used for debug purpose</span>
						<div class="clearfix"></div>
					</div>
				</div>
			</a>
		</div>
	</div>

<?php require("footer.php"); ?>
