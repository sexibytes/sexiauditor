<?php require("session.php"); ?>
<?php
$title = "vCenter Checks";
$additionalStylesheet = array(  'css/jquery.dataTables.min.css',
                                'css/bootstrap-datetimepicker.css');
$additionalScript = array(  'js/jquery.dataTables.min.js',
                            'js/jszip.min.js',
                            'js/dataTables.autoFill.min.js',
                            'js/dataTables.bootstrap.min.js',
                            'js/dataTables.buttons.min.js',
                            'js/autoFill.bootstrap.min.js',
                            'js/buttons.bootstrap.min.js',
                            'js/buttons.colVis.min.js',
                            'js/buttons.html5.min.js',
                            'js/file-size.js',
                            'js/moment.js',
                            'js/bootstrap-datetimepicker.js');
require("header.php");
require("helper.php");

# Main class loading
try {
  $check = new SexiCheck();
} catch (Exception $e) {
  # Any exception will be ending the script, we want exception-free run
  exit('  <div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span> ' . $e->getMessage() . '</div>');
}

if($check->getModuleSchedule('vcSessionAge') != 'off') {
  $check->displayCheck([  'xmlFile' => "sessions-global.xml",
                          'xpathQuery' => "/sessions/session",
                          'title' => 'Session Age',
                          'description' => 'The following displays vCenter sessions that exceed the maximum session age (' . $check->getConfig('vcSessionAge') . ' days).',
                          'thead' => array('Session Age', 'Last ActiveTime', 'UserName', 'ipAddress', 'UserAgent', 'vCenter'),
                          'tbody' => array('"<td>".DateTime::createFromFormat("Y-m-d", substr($entry->lastActiveTime, 0, 10))->diff(new DateTime("now"))->format("%a")."</td>"', '"<td>".$entry->lastActiveTime."</td>"', '"<td>".$entry->userName."</td>"', '"<td>".$entry->ipAddress."</td>"', '"<td>".$this->getUserAgent($entry->userAgent)."</td>"', '"<td>".$entry->vcenter."</td>"'),
                          'order' => '[ 0, "desc" ]',
                          'columnDefs' => '{ "orderable": false, className: "dt-body-center", "targets": [ 4 ] }']);
}

if($check->getModuleSchedule('vcLicenceReport') != 'off') {
  $check->displayCheck([  'xmlFile' => "licenses-global.xml",
                          'xpathQuery' => "/licenses/license",
                          'title' => 'License Report',
                          'description' => 'The following displays vCenter licenses.',
                          'thead' => array('Name', 'Unit', 'Total', 'Used', 'licenseKey', 'vCenter'),
                          'tbody' => array('"<td>".$entry->name."</td>"', '"<td>".$entry->costUnit."</td>"', '"<td>".$entry->total."</td>"', '"<td>".$entry->used."</td>"', '"<td>".'.(($check->getConfig('showPlainLicense') == 'disable') ? 'substr($entry->licenseKey, 0, 5) . "-#####-#####-#####-" . substr($entry->licenseKey, -5)' : '$entry->licenseKey').'."</td>"', '"<td>".$entry->vcenter."</td>"')]);
}
?>
    <h2>Permission report</h2>
	</div>
<?php require("footer.php"); ?>
