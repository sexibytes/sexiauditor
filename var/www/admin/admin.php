<?php require("session.php"); ?>
<?php
$isAdminPage = true;
$title = "Admin Area";
require("header.php");

function displayPanel($link, $style, $glyphicon, $title, $description) {
	return '		<div class="col-lg-4 col-md-6">
			<a href="' . $link . '">
			<div class="panel ' . $style .'">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-2"><i class="glyphicon ' . $glyphicon . '" style="font-size: 2em;"></i></div>
						<div class="col-xs-10 text-right"><div class="huge">' . $title . '</div></div>
					</div>
				</div>
				<div class="panel-footer">
					<span class="pull-left">' . $description . '</span>
					<div class="clearfix"></div>
				</div>
			</div>
			</a>
		</div>
';
}
?>
	<div class="container" style="padding-top: 30px;">
	<div class="row">
<?php
	echo displayPanel("credstore.php", "panel-primary", "glyphicon-briefcase", "Credential Store", "In this section, you'll be able to manage your VMware Credential Store. This store is used to set up your vCenter information to allow SexiGraf query what's needed.");
	echo displayPanel("updater.php", "panel-success", "glyphicon-hdd", "Package Updater", "Here you'll find the update process of SexiGraf. Once you have downloaded an update package, go on this section to give your appliance the State-Of-The-Art code!");
	echo displayPanel("moduleselector.php", "panel-default", "glyphicon-check", "Module Selector", "With the module selector, you will be able to enable/disable each module and set the schedule (hourly, daily, weekly or monthly) of actions.");
?>
	</div>
	<div class="row">
<?php
	#echo displayPanel("refresh-inventory.php", "panel-warning", "glyphicon-th-list", "Refresh Inventory", "The Static Offline Inventory is automatically schedule to be updated every 6 hours. If you want to force a refresh, you can use this section to perform update.");
	echo displayPanel("showlog.php", "panel-danger", "glyphicon-search", "Log Viewer", "The Log Viewer will give you access to the content of log files. It is basically a web equivalent of the <code>tail -f</code> linux command. It should be used for debug purpose");
	echo displayPanel("timetobuild.php", "panel-info", "glyphicon-time", "Time To Build", "In this section, you will be able to display a graph that will show global time to build of scheduler. It can be useful to see impact of adding/removing vCenter.");
?>
	</div>
	</div>

<?php require("footer.php"); ?>
