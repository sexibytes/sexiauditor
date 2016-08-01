<?php require("session.php"); ?>
<?php
require("helper.php");

// SQL server connection information
$sql_details = array(
	'user' => 'sexiauditor',
	'pass' => 'Sex!@ud1t0r',
	'db'   => 'sexiauditor',
	'host' => 'localhost'
);

if (isset($_GET['c'])) {
	$joinQuery = "";
	$extraCondition = "";
	$latest = true;
	# if timestamp not sent, we consider it as latest query
	if (isset($_GET['t'])) {
		$dateToSearch = date("Y-m-d", $_GET['t']);
		# if date sent is today, we consider it as latest query
		if ($dateToSearch != date("Y-m-d")) {
			# if not, we build our dates objects that will be used in SQL query (after firstseen + before lastseen)
			$latest = false;
			$dateStart = $dateToSearch . " 23:59:59";
			$dateEnd = $dateToSearch . " 00:00:01";
		}
	}

	switch($_GET['c']) {
		case 'VMCPURAMHDDRESERVATION':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
				array( 'db' => 'vms.memReservation', 'dt' => 1, 'field' => 'memReservation', 'formatter' => function( $d, $row ) { return "$d MB"; }),
				array( 'db' => 'vms.cpuReservation', 'dt' => 2, 'field' => 'cpuReservation', 'formatter' => function( $d, $row ) { return "$d MB"; }),
				array( 'db' => 'v.vcname', 'dt' => 3, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND (vms.cpuReservation > 0 OR vms.memReservation > 0) GROUP BY vms.moref, v.id";
		break;
		case 'VMCONSOLIDATIONNEEDED':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
				array( 'db' => 'v.vcname', 'dt' => 1, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND vms.consolidationNeeded = 1 GROUP BY vms.moref, v.id";
		break;
		case 'VMPHANTOMSNAPSHOT':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
				array( 'db' => 'v.vcname', 'dt' => 1, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND vms.phantomSnapshot > 0 GROUP BY vms.moref, v.id";
		break;
		case 'VMCPURAMHDDLIMITS':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
				array( 'db' => 'vms.cpuLimit', 'dt' => 1, 'field' => 'cpuLimit', 'formatter' => function( $d, $row ) { if ($d != '-1') {return "$d MHz";} else {return "$d";}}),
				array( 'db' => 'vms.memLimit', 'dt' => 2, 'field' => 'memLimit', 'formatter' => function( $d, $row ) { if ($d != '-1') {return "$d MB";} else {return "$d";}}),
				array( 'db' => 'v.vcname', 'dt' => 3, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND (vms.cpuLimit > 0 OR vms.memLimit > 0) GROUP BY vms.moref, v.id";
		break;
		case 'VMCPURAMHOTADD':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
				array( 'db' => 'vms.cpuHotAddEnabled', 'dt' => 1, 'field' => 'cpuHotAddEnabled', 'formatter' => function( $d, $row ) { if ($d == 1) {return "<i class=\"glyphicon glyphicon-ok-sign alarm-green\"></i>";} else {return "<i class=\"glyphicon glyphicon-remove-sign alarm-red\"></i>";}}),
				array( 'db' => 'vms.memHotAddEnabled', 'dt' => 2, 'field' => 'memHotAddEnabled', 'formatter' => function( $d, $row ) { if ($d == 1) {return "<i class=\"glyphicon glyphicon-ok-sign alarm-green\"></i>";} else {return "<i class=\"glyphicon glyphicon-remove-sign alarm-red\"></i>";}}),
				array( 'db' => 'v.vcname', 'dt' => 3, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND (vms.cpuHotAddEnabled = 1 OR vms.memHotAddEnabled = 1) GROUP BY vms.moref, v.id";
		break;
		case 'VMBALLOONZIPSWAP':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
				array( 'db' => 'vms.swappedMemory', 'dt' => 1, 'field' => 'swappedMemory', 'formatter' => function( $d, $row ) { return human_filesize($d);}),
				array( 'db' => 'vms.balloonedMemory', 'dt' => 2, 'field' => 'balloonedMemory', 'formatter' => function( $d, $row ) { return human_filesize($d);}),
				array( 'db' => 'vms.compressedMemory', 'dt' => 3, 'field' => 'compressedMemory', 'formatter' => function( $d, $row ) { return human_filesize($d);}),
				array( 'db' => 'v.vcname', 'dt' => 4, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND (vms.swappedMemory > 0 OR vms.balloonedMemory > 0 OR vms.compressedMemory > 0) GROUP BY vms.moref, v.id";
		break;
		case 'VMMULTIWRITERMODE':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
				array( 'db' => 'v.vcname', 'dt' => 1, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND vms.multiwriter = 1 GROUP BY vms.moref, v.id";
		break;
		case 'VMSCSIBUSSHARING':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
				array( 'db' => 'vms.powerState', 'dt' => 1, 'field' => 'powerState' ),
				array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND vms.sharedBus = 1 GROUP BY vms.moref, v.id";
		break;
		case 'VMINVALIDORINACCESSIBLE':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
				array( 'db' => 'vms.connectionState', 'dt' => 1, 'field' => 'connectionState' ),
				array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND vms.connectionState NOT LIKE 'connected' GROUP BY vms.moref, v.id";
		break;
		case 'VMINCONSISTENT':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
				array( 'db' => 'vms.vmxpath', 'dt' => 1, 'field' => 'vmxpath' ),
				array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND vms.vmxpath NOT LIKE CONCAT('%', vms.name, '/', vms.name, '.vmx') GROUP BY vms.moref, v.id";
		break;
		case 'VMREMOVABLECONNECTED':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.id', 'dt' => 0, 'field' => 'id', 'formatter' => function( $d, $row ) { return "<i class=\"glyphicon glyphicon-floppy-disk alarm-red\"></i> - <i class=\"glyphicon glyphicon-cd alarm-red\"></i>"; } ),
				array( 'db' => 'vms.name', 'dt' => 1, 'field' => 'name' ),
				array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND vms.removable = 1 GROUP BY vms.moref, v.id";
		break;
		case 'ALARMSVM':
			$table = 'alarms';
			$primaryKey = 'id';
			$columns = array(
			array( 'db' => 'a.status', 'dt' => 0, 'field' => 'status', 'formatter' => function( $d, $row ) { switch($d) { case "unknown": return '<i class="glyphicon glyphicon-question-sign"></i>'; case "green": return '<i class="glyphicon glyphicon-ok-sign alarm-green"></i>'; case "yellow": return '<i class="glyphicon glyphicon-exclamation-sign alarm-yellow"></i>'; case "red": return '<i class="glyphicon glyphicon-remove-sign alarm-red"></i>'; }}),
				array( 'db' => 'a.alarm_name', 'dt' => 1, 'field' => 'alarm_name' ),
				array( 'db' => 'a.time', 'dt' => 2, 'field' => 'time' ),
				array( 'db' => 'vms.name', 'dt' => 3, 'field' => 'name' ),
				array( 'db' => 'v.vcname', 'dt' => 4, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} a INNER JOIN vcenters v ON a.vcenter = v.id INNER JOIN vms ON a.entityMoRef = vms.moref";
			if ($latest) {
				$timeCondition = "a.active = 1";
			} else {
				$timeCondition = "a.firstseen > '" . $dateStart . "' AND a.lastseen < '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND a.entityMoRef LIKE 'VirtualMachine%' GROUP BY a.moref, v.id";
		break;
		case 'VMGUESTIDMISMATCH':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
				array( 'db' => 'vms.guestId', 'dt' => 1, 'field' => 'guestId' ),
				array( 'db' => 'vms.configGuestId', 'dt' => 2, 'field' => 'configGuestId' ),
				array( 'db' => 'v.vcname', 'dt' => 3, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND vms.guestId <> 'Not Available' AND vms.guestId <> vms.configGuestId GROUP BY vms.moref, v.id";
		break;
		case 'VMPOWEREDOFF':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
				array( 'db' => 'v.vcname', 'dt' => 1, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND vms.powerState = 'poweredOff' GROUP BY vms.moref, v.id";
		break;
		case 'VMMISNAMED':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
				array( 'db' => 'vms.fqdn', 'dt' => 1, 'field' => 'fqdn' ),
				array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
			if ($latest) {
				$timeCondition = "vms.active = 1";
			} else {
				$timeCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "'";
			}
			$extraCondition = $timeCondition . " AND vms.fqdn <> 'Not Available' AND vms.fqdn NOT LIKE CONCAT(vms.name, '%')  GROUP BY vms.moref, v.id";
		break;
		case 'VMINVENTORY':
			$table = 'vms';
			$primaryKey = 'id';
			$columns = array(
				array( 'db' => 'vms.id', 'dt' => 0, 'field' => 'id'),
				array( 'db' => 'vms.name', 'dt' => 1, 'field' => 'name', 'formatter' => function( $d, $row ) { return '<a href=\'showvm.php?vmid=' . $row[0] . '\' rel="modal">' . $d . '</a>'; }),
				array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' ),
				array( 'db' => 'c.cluster_name', 'dt' => 3, 'field' => 'cluster_name' ),
				array( 'db' => 'h.host_name', 'dt' => 4, 'field' => 'host_name', 'formatter' => function( $d, $row ) { return '<a href=\'showhost.php?hostid=' . $row[15] . '\' rel="modal">' . $d . '</a>'; }),
				array( 'db' => 'vms.vmxpath', 'dt' => 5, 'field' => 'vmxpath' ),
				array( 'db' => 'vms.portgroup', 'dt' => 6, 'field' => 'portgroup', 'formatter' => function( $d, $row ) { return str_ireplace(',','<br/>',$d); }),
				array( 'db' => 'vms.ip', 'dt' => 7, 'field' => 'ip', 'formatter' => function( $d, $row ) { return str_ireplace(',','<br/>',$d); }),
				array( 'db' => 'vms.numcpu', 'dt' => 8, 'field' => 'numcpu' ),
				array( 'db' => 'vms.memory', 'dt' => 9, 'field' => 'memory' ),
				array( 'db' => 'vms.commited', 'dt' => 10, 'field' => 'commited' ),
				array( 'db' => 'vms.provisionned', 'dt' => 11, 'field' => 'provisionned' ),
				array( 'db' => 'd.datastore_name', 'dt' => 12, 'field' => 'datastore_name' ),
				array( 'db' => 'vms.vmpath', 'dt' => 13, 'field' => 'vmpath' ),
				array( 'db' => 'vms.mac', 'dt' => 14, 'field' => 'mac', 'formatter' => function( $d, $row ) { return str_ireplace(',','<br/>',$d); }),
				array( 'db' => 'vms.host', 'dt' => 15, 'field' => 'host' )
			);
			$joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN clusters c ON h.cluster = c.id INNER JOIN vcenters AS v ON (h.vcenter = v.id) INNER JOIN datastores AS d ON (vms.datastore = d.id)";
			if ($latest) {
				$extraCondition = "vms.active = 1";
			} else {
				$extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' GROUP BY vms.moref, v.id";
			}
		break;
	}

	require( 'ssp.customized.class.php' );
	echo json_encode( SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraCondition) );
}
