<?php

if (isset($_GET['c']))
{

  if ($_GET['c'] != "ROVMINVENTORY")
  {
    
    require("session.php");
    
  } # END if ($_GET['c'] != "ROVMINVENTORY")
  
  require("helper.php");
  $sexihelper = new SexiHelper();
  # SQL server connection information
  # TODO: put these info in external file
  $sql_details = array(
    'user' => 'sexiauditor',
    'pass' => 'Sex!@ud1t0r',
    'db'   => 'sexiauditor',
    'host' => 'localhost'
  );
  $joinQuery = "";
  $extraCondition = "";
  
  # if timestamp not sent, we consider it as latest query
  if (isset($_GET['t']))
  {
    
    $dateToSearch = date("Y-m-d", $_GET['t']);
  
  }
  else
  {
    
    $dateToSearch = date("Y-m-d", time());
    
  } # END if (isset($_GET['t']))
  
  $dateStart = $dateToSearch . " 23:59:59";
  $dateEnd = $dateToSearch . " 00:00:01";

  switch($_GET['c'])
  {
    
    case 'VSANHARDWARECOMPATIBILITY':
    
      $table = 'clustersVSAN';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'c.cluster_name', 'dt' => 0, 'field' => 'cluster_name' ),
        array( 'db' => 'cv.hcldbuptodate', 'dt' => 1, 'field' => 'hcldbuptodate', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.autohclupdate', 'dt' => 2, 'field' => 'autohclupdate', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.controlleronhcl', 'dt' => 3, 'field' => 'controlleronhcl', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.controllerreleasesupport', 'dt' => 4, 'field' => 'controllerreleasesupport', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.controllerdriver', 'dt' => 5, 'field' => 'controllerdriver', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'v.vcname', 'dt' => 6, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} cv INNER JOIN (SELECT cluster_id, MAX(lastseen) AS ts FROM {$table} GROUP BY cluster_id) maxt ON (maxt.cluster_id = cv.cluster_id AND maxt.ts = cv.lastseen) INNER JOIN clusters AS c ON (cv.cluster_id = c.id) INNER JOIN vcenters AS v ON (v.id = c.vcenter)";
      $extraCondition = "(cv.autohclupdate <> 'green' OR cv.hcldbuptodate <> 'green' OR cv.controlleronhcl <> 'green' OR cv.controllerreleasesupport <> 'green' OR cv.controllerdriver <> 'green') AND cv.firstseen < '" . $dateStart . "' AND cv.lastseen > '" . $dateEnd . "'";
            
    break; # END case 'VSANHARDWARECOMPATIBILITY':
    
    case 'VSANNETWORK':
    
      $table = 'clustersVSAN';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'c.cluster_name', 'dt' => 0, 'field' => 'cluster_name' ),
        array( 'db' => 'cv.clusterpartition', 'dt' => 1, 'field' => 'clusterpartition', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.vmknicconfigured', 'dt' => 2, 'field' => 'vmknicconfigured', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.matchingsubnets', 'dt' => 3, 'field' => 'matchingsubnets', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.matchingmulticast', 'dt' => 4, 'field' => 'matchingmulticast', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'v.vcname', 'dt' => 5, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} cv INNER JOIN (SELECT cluster_id, MAX(lastseen) AS ts FROM {$table} GROUP BY cluster_id) maxt ON (maxt.cluster_id = cv.cluster_id AND maxt.ts = cv.lastseen) INNER JOIN clusters AS c ON (cv.cluster_id = c.id) INNER JOIN vcenters AS v ON (v.id = c.vcenter)";
      $extraCondition = "(cv.clusterpartition <> 'green' OR cv.vmknicconfigured <> 'green' OR cv.matchingsubnets <> 'green' OR cv.matchingmulticast <> 'green') AND cv.firstseen < '" . $dateStart . "' AND cv.lastseen > '" . $dateEnd . "'";
      
    break; # END case 'VSANNETWORK':
    
    case 'VSANPHYSICALDISK':
    
      $table = 'clustersVSAN';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'c.cluster_name', 'dt' => 0, 'field' => 'cluster_name' ),
        array( 'db' => 'cv.physdiskoverall', 'dt' => 1, 'field' => 'physdiskoverall', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.physdiskmetadata', 'dt' => 2, 'field' => 'physdiskmetadata', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.physdisksoftware', 'dt' => 3, 'field' => 'physdisksoftware', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.physdiskcongestion', 'dt' => 4, 'field' => 'physdiskcongestion', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'v.vcname', 'dt' => 5, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} cv INNER JOIN (SELECT cluster_id, MAX(lastseen) AS ts FROM {$table} GROUP BY cluster_id) maxt ON (maxt.cluster_id = cv.cluster_id AND maxt.ts = cv.lastseen) INNER JOIN clusters AS c ON (cv.cluster_id = c.id) INNER JOIN vcenters AS v ON (v.id = c.vcenter)";
      $extraCondition = "(cv.physdiskoverall <> 'green' OR cv.physdiskmetadata <> 'green' OR cv.physdisksoftware <> 'green' OR cv.physdiskcongestion <> 'green') AND cv.firstseen < '" . $dateStart . "' AND cv.lastseen > '" . $dateEnd . "'";
      
    break; # END case 'VSANPHYSICALDISK':
    
    case 'VSANCLUSTER':
    
      $table = 'clustersVSAN';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'c.cluster_name', 'dt' => 0, 'field' => 'cluster_name' ),
        array( 'db' => 'cv.healthversion', 'dt' => 1, 'field' => 'healthversion', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.advcfgsync', 'dt' => 2, 'field' => 'advcfgsync', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.clomdliveness', 'dt' => 3, 'field' => 'clomdliveness', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.diskbalance', 'dt' => 4, 'field' => 'diskbalance', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.upgradesoftware', 'dt' => 5, 'field' => 'upgradesoftware', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'cv.upgradelowerhosts', 'dt' => 6, 'field' => 'upgradelowerhosts', 'formatter' => function( $d, $row ) { global $alarmStatus; return $alarmStatus[$d]; } ),
        array( 'db' => 'v.vcname', 'dt' => 7, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} cv INNER JOIN (SELECT cluster_id, MAX(lastseen) AS ts FROM {$table} GROUP BY cluster_id) maxt ON (maxt.cluster_id = cv.cluster_id AND maxt.ts = cv.lastseen) INNER JOIN clusters AS c ON (cv.cluster_id = c.id) INNER JOIN vcenters AS v ON (v.id = c.vcenter)";
      $extraCondition = "(cv.healthversion <> 'green' OR cv.advcfgsync <> 'green' OR cv.clomdliveness <> 'green' OR cv.diskbalance <> 'green' OR cv.upgradesoftware <> 'green' OR cv.upgradelowerhosts <> 'green') AND cv.firstseen < '" . $dateStart . "' AND cv.lastseen > '" . $dateEnd . "'";

    break; # END case 'VSANCLUSTER':
    
    case 'VCPERMISSIONREPORT':
    
      $table = 'permissions';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'permissions.inventory_path', 'dt' => 0, 'field' => 'inventory_path' ),
        array( 'db' => 'permissions.principal', 'dt' => 1, 'field' => 'principal', 'formatter' => function( $d, $row ) { if ($row[4] == '1') { return "<i class=\"icon-user\"></i> $d"; } else { return "<i class=\"icon-groups-friends\"></i> $d"; } } ),
        array( 'db' => 'permissions.role_name', 'dt' => 2, 'field' => 'role_name' ),
        array( 'db' => 'v.vcname', 'dt' => 3, 'field' => 'vcname' ),
        array( 'db' => 'permissions.isGroup', 'dt' => 4, 'field' => 'isGroup' )
      );
      $joinQuery = "FROM {$table} INNER JOIN vcenters AS v ON (permissions.vcenter = v.id)";
      $extraCondition = "permissions.firstseen < '" . $dateStart . "' AND permissions.lastseen > '" . $dateEnd . "'";
      
    break; # END case 'VCPERMISSIONREPORT':
    
    case 'CLUSTERTPSSAVINGS':
    
      $table = 'hosts';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'c.cluster_name', 'dt' => 0, 'field' => 'cluster_name' ),
        array( 'db' => 'SUM(h.memory) as memory', 'dt' => 1, 'field' => 'memory', 'formatter' => function( $d, $row ) { return human_filesize($d,0); } ),
        array( 'db' => 'SUM(hm.sharedmemory) as sharedmemory', 'dt' => 2, 'field' => 'sharedmemory', 'formatter' => function( $d, $row ) { return human_filesize($d*1024,0); } ),
        array( 'db' => 'ROUND(100*1024*SUM(hm.sharedmemory)/SUM(h.memory)) as savedmemory', 'dt' => 3, 'field' => 'savedmemory', 'formatter' => function( $d, $row ) { return "$d %"; } ),
        array( 'db' => 'v.vcname', 'dt' => 4, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} h INNER JOIN clusters AS c ON (h.cluster = c.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id) INNER JOIN hostMetrics AS hm ON (h.id = hm.host_id)";
      $extraCondition = "h.firstseen < '" . $dateStart . "' AND h.lastseen > '" . $dateEnd . "' AND hm.id IN (SELECT MAX(id) FROM hostMetrics WHERE firstseen < '" . $dateStart . "' AND lastseen > '" . $dateEnd . "' GROUP BY host_id) GROUP BY c.cluster_name";
      
    break; # END case 'CLUSTERTPSSAVINGS':
    
    case 'CLUSTERADMISSIONCONTROL':
    
      $table = 'clusters';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'c.cluster_name', 'dt' => 0, 'field' => 'cluster_name' ),
        array( 'db' => 'c.isAdmissionEnable', 'dt' => 1, 'field' => 'isAdmissionEnable', 'formatter' => function( $d, $row ) { if ($d) { return "<i class=\"glyphicon glyphicon-ok-sign text-success\"></i>"; } else { return "<i class=\"glyphicon glyphicon-remove-sign text-danger\"></i>"; } } ),
        array( 'db' => 'c.admissionThreshold', 'dt' => 2, 'field' => 'admissionThreshold', 'formatter' => function( $d, $row ) { if ($row[1] == '0') { return "N/A"; } else { return $d; } } ),
        array( 'db' => 'c.admissionValue', 'dt' => 3, 'field' => 'admissionValue', 'formatter' => function( $d, $row ) { if ($row[1] == '0') { return "N/A"; } else { return $d; } } ),
        array( 'db' => 'v.vcname', 'dt' => 4, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} c INNER JOIN vcenters AS v ON (c.vcenter = v.id)";
      $extraCondition = "c.firstseen < '" . $dateStart . "' AND c.lastseen > '" . $dateEnd . "' AND c.dasenabled = 1 AND (c.isAdmissionEnable = 0 OR (c.isAdmissionEnable = 1 AND c.admissionValue < c.admissionThreshold))";
      
    break; # END case 'CLUSTERADMISSIONCONTROL':
    
    case 'HOSTSSHSHELL':

      $table = 'hosts';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'h.host_name', 'dt' => 0, 'field' => 'host_name' ),
        array( 'db' => 'h.ssh_policy', 'dt' => 1, 'field' => 'ssh_policy', 'formatter' => function( $d, $row ) { global $servicePolicyChoice; return $servicePolicyChoice[$d]; } ),
        array( 'db' => 'h.ssh_policy', 'dt' => 2, 'field' => 'ssh_policy', 'formatter' => function( $d, $row ) { global $servicePolicyChoice; global $sexihelper; return $servicePolicyChoice[$sexihelper->getConfig('hostSSHPolicy')]; } ),
        array( 'db' => 'h.shell_policy', 'dt' => 3, 'field' => 'shell_policy', 'formatter' => function( $d, $row ) { global $servicePolicyChoice; return $servicePolicyChoice[$d]; } ),
        array( 'db' => 'h.shell_policy', 'dt' => 4, 'field' => 'shell_policy', 'formatter' => function( $d, $row ) { global $servicePolicyChoice; global $sexihelper;  return $servicePolicyChoice[$sexihelper->getConfig('hostShellPolicy')]; } ),
        array( 'db' => 'v.vcname', 'dt' => 5, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} h INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "h.firstseen < '" . $dateStart . "' AND h.lastseen > '" . $dateEnd . "' AND h.ssh_policy <> '". $sexihelper->getConfig('hostSSHPolicy') . "' OR h.shell_policy <> '" . $sexihelper->getConfig('hostSSHPolicy'). "' GROUP BY h.host_name";
      
    break; # END case 'HOSTSSHSHELL':
    
    case 'HOSTPOWERMANAGEMENTPOLICY':

      $table = 'hosts';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'h.host_name', 'dt' => 0, 'field' => 'host_name' ),
        array( 'db' => 'h.powerpolicy', 'dt' => 1, 'field' => 'powerpolicy', 'formatter' => function( $d, $row ) { global $powerChoice; return $powerChoice[$d]; } ),
        array( 'db' => 'h.ssh_policy', 'dt' => 2, 'field' => 'ssh_policy', 'formatter' => function( $d, $row ) { global $powerChoice; global $sexihelper; return $powerChoice[$sexihelper->getConfig('powerSystemInfo')]; } ),
        array( 'db' => 'v.vcname', 'dt' => 3, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} h INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "h.firstseen < '" . $dateStart . "' AND h.lastseen > '" . $dateEnd . "' AND h.powerpolicy <> '". $sexihelper->getConfig('powerSystemInfo') . "'";
      
    break; # END case 'HOSTPOWERMANAGEMENTPOLICY':
    
    case 'DATASTORESPACEREPORT':
    
      $table = 'datastores';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'd.datastore_name', 'dt' => 0, 'field' => 'datastore_name' ),
        array( 'db' => 'dm.size', 'dt' => 1, 'field' => 'size', 'formatter' => function( $d, $row ) { return human_filesize($d,0); } ),
        array( 'db' => 'dm.freespace', 'dt' => 2, 'field' => 'freespace', 'formatter' => function( $d, $row ) { return human_filesize($d,0); } ),
        array( 'db' => 'ROUND(100*(dm.freespace/dm.size)) as pct_free', 'dt' => 3, 'field' => 'pct_free', 'formatter' => function( $d, $row ) { return "$d %"; } ),
        array( 'db' => 'v.vcname', 'dt' => 4, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} d INNER JOIN datastoreMetrics AS dm ON (d.id = dm.datastore_id) INNER JOIN vcenters AS v ON (d.vcenter = v.id)";
      $extraCondition = "d.firstseen < '" . $dateStart . "' AND d.lastseen > '" . $dateEnd . "' AND dm.id IN (SELECT MAX(id) FROM datastoreMetrics WHERE firstseen < '" . $dateStart . "' AND lastseen > '" . $dateEnd . "' GROUP BY datastore_id) AND ROUND(100*(dm.freespace/dm.size)) < " . $sexihelper->getConfig('datastoreFreeSpaceThreshold') . " GROUP BY d.datastore_name, d.vcenter";
      
    break; # END case 'DATASTORESPACEREPORT':  
    
    case 'DATASTOREORPHANEDVMFILESREPORT':

      $table = 'orphanFiles';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'v.vcname', 'dt' => 0, 'field' => 'vcname' ),
        array( 'db' => 'o.filePath', 'dt' => 1, 'field' => 'filePath' ),
        array( 'db' => 'o.fileSize', 'dt' => 2, 'field' => 'fileSize', 'formatter' => function( $d, $row ) { return human_filesize($d,0); } ),
        array( 'db' => 'o.fileModification', 'dt' => 3, 'field' => 'fileModification' )
      );
      $joinQuery = "FROM {$table} o INNER JOIN vcenters AS v ON (o.vcenter = v.id)";
      $extraCondition = "o.firstseen < '" . $dateStart . "' AND o.lastseen > '" . $dateEnd . "'";
      
    break; # END case 'DATASTOREORPHANEDVMFILESREPORT':
    
    case 'DATASTOREOVERALLOCATION':
    
      $table = 'datastores';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'd.datastore_name', 'dt' => 0, 'field' => 'datastore_name' ),
        array( 'db' => 'dm.size', 'dt' => 1, 'field' => 'size', 'formatter' => function( $d, $row ) { return human_filesize($d,0); } ),
        array( 'db' => 'dm.freespace', 'dt' => 2, 'field' => 'freespace', 'formatter' => function( $d, $row ) { return human_filesize($d,2); } ),
        array( 'db' => 'dm.uncommitted', 'dt' => 3, 'field' => 'uncommitted', 'formatter' => function( $d, $row ) { return human_filesize($d,2); } ),
        array( 'db' => 'ROUND(100*((dm.size-dm.freespace+dm.uncommitted)/dm.size)) as pct_overallocation', 'dt' => 4, 'field' => 'pct_overallocation', 'formatter' => function( $d, $row ) { return round(100*(($row[1]-$row[2]+$row[3])/$row[1])) . " %"; } ),
        array( 'db' => 'v.vcname', 'dt' => 5, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} d INNER JOIN datastoreMetrics dm ON (d.id = dm.datastore_id) INNER JOIN vcenters AS v ON (d.vcenter = v.id)";
      $extraCondition = "d.firstseen < '" . $dateStart . "' AND d.lastseen > '" . $dateEnd . "' AND dm.id IN (SELECT MAX(id) FROM datastoreMetrics WHERE firstseen < '" . $dateStart . "' AND lastseen > '" . $dateEnd . "' GROUP BY datastore_id) AND ROUND(100*((dm.size-dm.freespace+dm.uncommitted)/dm.size)) > ". $sexihelper->getConfig('datastoreOverallocation');
      
    break; # END case 'DATASTOREOVERALLOCATION':
    
    case 'VMSNAPSHOTSAGE':
    
      $table = 'snapshots';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
        array( 'db' => 's.id', 'dt' => 1, 'field' => 'id', 'formatter' => function( $d, $row ) { return ( ($row[7] == 0) ? "<i class=\"glyphicon glyphicon-remove-sign alarm-red\"></i>" : "<i class=\"glyphicon glyphicon-ok-sign alarm-green\"></i>" ) . ( ($row[6] == "poweredOff") ? '<i class="glyphicon glyphicon-stop"></i>' : '<i class="glyphicon glyphicon-play"></i>' ); } ),
        array( 'db' => 's.name as snapshot_name', 'dt' => 2, 'field' => 'snapshot_name' ),
        array( 'db' => 's.description', 'dt' => 3, 'field' => 'description' ),
        array( 'db' => 'DATEDIFF(\'' . $dateToSearch . '\', s.createTime) as age', 'dt' => 4, 'field' => 'age' ),
        array( 'db' => 'v.vcname', 'dt' => 5, 'field' => 'vcname' ),
        array( 'db' => 's.state', 'dt' => 6, 'field' => 'state' ),
        array( 'db' => 's.quiesced', 'dt' => 7, 'field' => 'quiesced' )
      );
      $joinQuery = "FROM {$table} s INNER JOIN vms ON (s.vm = vms.id) INNER JOIN hosts h ON (vms.host = h.id) INNER JOIN vcenters v ON (h.vcenter = v.id)";
      $extraCondition = "s.firstseen < '" . $dateStart . "' AND s.lastseen > '" . $dateEnd . "' AND DATEDIFF('" . $dateToSearch . "', s.createTime) > " . $sexihelper->getConfig('vmSnapshotAge');
      
    break; # END case 'VMSNAPSHOTSAGE':
    
    case 'VMCPURAMHDDRESERVATION':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
        array( 'db' => 'vms.cpuReservation', 'dt' => 1, 'field' => 'cpuReservation', 'formatter' => function( $d, $row ) { return "$d MHz"; } ),
        array( 'db' => 'vms.memReservation', 'dt' => 2, 'field' => 'memReservation', 'formatter' => function( $d, $row ) { return "$d MB"; } ),
        array( 'db' => 'v.vcname', 'dt' => 3, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND (vms.cpuReservation > 0 OR vms.memReservation > 0) GROUP BY vms.moref, v.id";
      
    break; # END case 'VMCPURAMHDDRESERVATION':
    
    case 'VMCONSOLIDATIONNEEDED':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
        array( 'db' => 'v.vcname', 'dt' => 1, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND vms.consolidationNeeded = 1 GROUP BY vms.moref, v.id";
      
    break; # END case 'VMCONSOLIDATIONNEEDED':
    
    case 'VMPHANTOMSNAPSHOT':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
        array( 'db' => 'v.vcname', 'dt' => 1, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND vms.phantomSnapshot > 0 GROUP BY vms.moref, v.id";
      
    break; # END case 'VMPHANTOMSNAPSHOT':
    
    case 'VMCPURAMHDDLIMITS':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
        array( 'db' => 'vms.cpuLimit', 'dt' => 1, 'field' => 'cpuLimit', 'formatter' => function( $d, $row ) { if ($d != '-1') { return "$d MHz"; } else { return "$d"; } } ),
        array( 'db' => 'vms.memLimit', 'dt' => 2, 'field' => 'memLimit', 'formatter' => function( $d, $row ) { if ($d != '-1') { return "$d MB"; } else { return "$d"; } } ),
        array( 'db' => 'v.vcname', 'dt' => 3, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND (vms.cpuLimit > 0 OR vms.memLimit > 0) GROUP BY vms.moref, v.id";
      
    break; # END case 'VMCPURAMHDDLIMITS':
    
    case 'VMCPURAMHOTADD':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
        array( 'db' => 'vms.cpuHotAddEnabled', 'dt' => 1, 'field' => 'cpuHotAddEnabled', 'formatter' => function( $d, $row ) { if ($d == 1) { return "<i class=\"glyphicon glyphicon-ok-sign alarm-green\"></i>"; } else { return "<i class=\"glyphicon glyphicon-remove-sign alarm-red\"></i>"; } } ),
        array( 'db' => 'vms.memHotAddEnabled', 'dt' => 2, 'field' => 'memHotAddEnabled', 'formatter' => function( $d, $row ) { if ($d == 1) { return "<i class=\"glyphicon glyphicon-ok-sign alarm-green\"></i>"; } else { return "<i class=\"glyphicon glyphicon-remove-sign alarm-red\"></i>"; } } ),
        array( 'db' => 'v.vcname', 'dt' => 3, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND (vms.cpuHotAddEnabled = 1 OR vms.memHotAddEnabled = 1) GROUP BY vms.moref, v.id";
      
    break; # END case 'VMCPURAMHOTADD':
    
    case 'VMBALLOONZIPSWAP':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
        array( 'db' => 'vmm.balloonedMemory', 'dt' => 1, 'field' => 'balloonedMemory', 'formatter' => function( $d, $row ) { return human_filesize($d); } ),
        array( 'db' => 'vmm.compressedMemory', 'dt' => 2, 'field' => 'compressedMemory', 'formatter' => function( $d, $row ) { return human_filesize($d); } ),
        array( 'db' => 'vmm.swappedMemory', 'dt' => 3, 'field' => 'swappedMemory', 'formatter' => function( $d, $row ) { return human_filesize($d); } ),
        array( 'db' => 'v.vcname', 'dt' => 4, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} INNER JOIN vcenters AS v ON (vms.vcenter = v.id) INNER JOIN vmMetrics AS vmm ON (vmm.vm_id = vms.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND vmm.id IN (SELECT MAX(id) FROM vmMetrics WHERE firstseen < '" . $dateStart . "' AND lastseen > '" . $dateEnd . "' GROUP BY vm_id) AND (vmm.swappedMemory > 0 OR vmm.balloonedMemory > 0 OR vmm.compressedMemory > 0) GROUP BY vms.moref, v.id";
      
    break; # END case 'VMBALLOONZIPSWAP':
    
    case 'VMMULTIWRITERMODE':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
        array( 'db' => 'v.vcname', 'dt' => 1, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND vms.multiwriter = 1 GROUP BY vms.moref, v.id";
      
    break; # END case 'VMMULTIWRITERMODE':
    
    case 'VMSCSIBUSSHARING':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
        array( 'db' => 'vms.powerState', 'dt' => 1, 'field' => 'powerState' ),
        array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND vms.sharedBus = 1 GROUP BY vms.moref, v.id";
      
    break; # END case 'VMSCSIBUSSHARING':
    
    case 'VMINVALIDORINACCESSIBLE':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
        array( 'db' => 'vms.connectionState', 'dt' => 1, 'field' => 'connectionState' ),
        array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND vms.connectionState NOT LIKE 'connected' GROUP BY vms.moref, v.id";
      
    break; # END case 'VMINVALIDORINACCESSIBLE':
    
    case 'VMINCONSISTENT':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
        array( 'db' => 'vms.vmxpath', 'dt' => 1, 'field' => 'vmxpath' ),
        array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND vms.vmxpath NOT LIKE CONCAT('%', vms.name, '/', vms.name, '.vmx') GROUP BY vms.moref, v.id";
      
    break; # END case 'VMINCONSISTENT':
    
    case 'VMREMOVABLECONNECTED':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.id', 'dt' => 0, 'field' => 'id', 'formatter' => function( $d, $row ) { return "<i class=\"glyphicon glyphicon-floppy-disk alarm-red\"></i> - <i class=\"glyphicon glyphicon-cd alarm-red\"></i>"; } ),
        array( 'db' => 'vms.name', 'dt' => 1, 'field' => 'name' ),
        array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND vms.removable = 1 GROUP BY vms.moref, v.id";
      
    break; # END case 'VMREMOVABLECONNECTED':
    
    case 'ALARMSVM':
    
      $table = 'alarms';
      $primaryKey = 'id';
      $columns = array(
      array( 'db' => 'a.status', 'dt' => 0, 'field' => 'status', 'formatter' => function( $d, $row ) { switch($d) { case "unknown": return '<i class="glyphicon glyphicon-question-sign"></i>'; case "green": return '<i class="glyphicon glyphicon-ok-sign alarm-green"></i>'; case "yellow": return '<i class="glyphicon glyphicon-exclamation-sign alarm-yellow"></i>'; case "red": return '<i class="glyphicon glyphicon-remove-sign alarm-red"></i>'; } } ),
        array( 'db' => 'a.alarm_name', 'dt' => 1, 'field' => 'alarm_name' ),
        array( 'db' => 'a.time', 'dt' => 2, 'field' => 'time' ),
        array( 'db' => 'vms.name', 'dt' => 3, 'field' => 'name' ),
        array( 'db' => 'v.vcname', 'dt' => 4, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} a INNER JOIN vcenters v ON a.vcenter = v.id INNER JOIN vms ON a.entityMoRef = vms.moref";
      $extraCondition = "a.firstseen < '" . $dateStart . "' AND a.lastseen > '" . $dateEnd . "' AND a.entityMoRef LIKE 'VirtualMachine%'";
      
    break; # END case 'ALARMSVM':
    
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
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND vms.guestId <> 'Not Available' AND vms.guestId <> vms.configGuestId GROUP BY vms.moref, v.id";
      
    break; # END case 'VMGUESTIDMISMATCH':
    
    case 'VMPOWEREDOFF':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
        array( 'db' => 'v.vcname', 'dt' => 1, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND vms.powerState = 'poweredOff' GROUP BY vms.moref, v.id";
      
    break; # END case 'VMPOWEREDOFF':
    
    case 'VMMISNAMED':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.name', 'dt' => 0, 'field' => 'name' ),
        array( 'db' => 'vms.fqdn', 'dt' => 1, 'field' => 'fqdn' ),
        array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' )
      );
      $joinQuery = "FROM {$table} INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND vms.fqdn <> 'Not Available' AND vms.fqdn NOT LIKE CONCAT(vms.name, '%')  GROUP BY vms.moref, v.id";
      
    break; # END case 'VMMISNAMED':
    
    case 'VMINVENTORY':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.id', 'dt' => 0, 'field' => 'id'),
        array( 'db' => 'vms.name', 'dt' => 1, 'field' => 'name', 'formatter' => function( $d, $row ) { return '<a href=\'showvm.php?vmid=' . $row[0] . '\' rel="modal">' . $d . '</a>'; } ),
        array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' ),
        array( 'db' => 'c.cluster_name', 'dt' => 3, 'field' => 'cluster_name' ),
        array( 'db' => 'h.host_name', 'dt' => 4, 'field' => 'host_name', 'formatter' => function( $d, $row ) { return '<a href=\'showhost.php?hostid=' . $row[16] . '\' rel="modal">' . $d . '</a>'; } ),
        array( 'db' => 'vms.vmxpath', 'dt' => 5, 'field' => 'vmxpath' ),
        array( 'db' => 'vms.portgroup', 'dt' => 6, 'field' => 'portgroup', 'formatter' => function( $d, $row ) { return str_ireplace(',','<br/>',$d); } ),
        array( 'db' => 'vms.ip', 'dt' => 7, 'field' => 'ip', 'formatter' => function( $d, $row ) { return str_ireplace(',','<br/>',$d); } ),
        array( 'db' => 'vms.numcpu', 'dt' => 8, 'field' => 'numcpu' ),
        array( 'db' => 'vms.memory', 'dt' => 9, 'field' => 'memory' ),
        array( 'db' => 'vmm.commited', 'dt' => 10, 'field' => 'commited' ),
        array( 'db' => 'vms.provisionned', 'dt' => 11, 'field' => 'provisionned' ),
        array( 'db' => 'd.datastore_name', 'dt' => 12, 'field' => 'datastore_name' ),
        array( 'db' => 'vms.vmpath', 'dt' => 13, 'field' => 'vmpath' ),
        array( 'db' => 'vms.mac', 'dt' => 14, 'field' => 'mac', 'formatter' => function( $d, $row ) { return str_ireplace(',','<br/>',$d); } ),
        array( 'db' => 'vms.powerState', 'dt' => 15, 'field' => 'powerState' ),
        array( 'db' => 'h.id', 'dt' => 16, 'field' => 'id' )
      );
      $joinQuery = "FROM {$table} INNER JOIN vmMetrics AS vmm ON (vms.id = vmm.vm_id) INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN clusters c ON h.cluster = c.id INNER JOIN vcenters AS v ON (h.vcenter = v.id) INNER JOIN datastores AS d ON (vms.datastore = d.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND vmm.id IN (SELECT MAX(id) FROM vmMetrics WHERE firstseen < '" . $dateStart . "' AND lastseen > '" . $dateEnd . "' GROUP BY vm_id) GROUP BY vms.moref, v.id";
      
    break; # END case 'VMINVENTORY':
    
    case 'ROVMINVENTORY':
    
      $table = 'vms';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'vms.id', 'dt' => 0, 'field' => 'id'),
        array( 'db' => 'vms.name', 'dt' => 1, 'field' => 'name', 'formatter' => function( $d, $row ) { return '<a href=\'showvm.php?vmid=' . $row[0] . '\' rel="modal">' . $d . '</a>'; } ),
        array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' ),
        array( 'db' => 'c.cluster_name', 'dt' => 3, 'field' => 'cluster_name' ),
        array( 'db' => 'h.host_name', 'dt' => 4, 'field' => 'host_name', 'formatter' => function( $d, $row ) { return '<a href=\'showhost.php?hostid=' . $row[16] . '\' rel="modal">' . $d . '</a>'; } ),
        array( 'db' => 'vms.vmxpath', 'dt' => 5, 'field' => 'vmxpath' ),
        array( 'db' => 'vms.portgroup', 'dt' => 6, 'field' => 'portgroup', 'formatter' => function( $d, $row ) { return str_ireplace(',','<br/>',$d); } ),
        array( 'db' => 'vms.ip', 'dt' => 7, 'field' => 'ip', 'formatter' => function( $d, $row ) { return str_ireplace(',','<br/>',$d); } ),
        array( 'db' => 'vms.numcpu', 'dt' => 8, 'field' => 'numcpu' ),
        array( 'db' => 'vms.memory', 'dt' => 9, 'field' => 'memory' ),
        array( 'db' => 'vmm.commited', 'dt' => 10, 'field' => 'commited' ),
        array( 'db' => 'vms.provisionned', 'dt' => 11, 'field' => 'provisionned' ),
        array( 'db' => 'd.datastore_name', 'dt' => 12, 'field' => 'datastore_name' ),
        array( 'db' => 'vms.vmpath', 'dt' => 13, 'field' => 'vmpath' ),
        array( 'db' => 'vms.mac', 'dt' => 14, 'field' => 'mac', 'formatter' => function( $d, $row ) { return str_ireplace(',','<br/>',$d); } ),
        array( 'db' => 'vms.powerState', 'dt' => 15, 'field' => 'powerState' ),
        array( 'db' => 'h.id', 'dt' => 16, 'field' => 'id' )
      );
      $joinQuery = "FROM {$table} INNER JOIN vmMetrics AS vmm ON (vms.id = vmm.vm_id) INNER JOIN hosts AS h ON (vms.host = h.id) INNER JOIN clusters c ON h.cluster = c.id INNER JOIN vcenters AS v ON (h.vcenter = v.id) INNER JOIN datastores AS d ON (vms.datastore = d.id)";
      $extraCondition = "vms.firstseen < '" . $dateStart . "' AND vms.lastseen > '" . $dateEnd . "' AND vmm.id IN (SELECT MAX(id) FROM vmMetrics WHERE firstseen < '" . $dateStart . "' AND lastseen > '" . $dateEnd . "' GROUP BY vm_id) GROUP BY vms.moref, v.id";
      
    break; # END case 'ROVMINVENTORY':
    
    case 'HOSTINVENTORY':
    
      $table = 'hosts';
      $primaryKey = 'id';
      $columns = array(
        array( 'db' => 'h.id', 'dt' => 0, 'field' => 'id'),
        array( 'db' => 'h.host_name', 'dt' => 1, 'field' => 'host_name' ),
        array( 'db' => 'v.vcname', 'dt' => 2, 'field' => 'vcname' ),
        array( 'db' => 'c.cluster_name', 'dt' => 3, 'field' => 'cluster_name' ),
        array( 'db' => 'h.numcpu', 'dt' => 4, 'field' => 'numcpu' ),
        array( 'db' => 'h.numcpucore', 'dt' => 5, 'field' => 'numcpucore' ),
        array( 'db' => 'h.memory', 'dt' => 6, 'field' => 'memory', 'formatter' => function( $d, $row ) { return human_filesize($d,0); } ),
        array( 'db' => 'h.model', 'dt' => 7, 'field' => 'model' ),
        array( 'db' => 'h.cputype', 'dt' => 8, 'field' => 'cputype' ),
        array( 'db' => 'h.cpumhz', 'dt' => 9, 'field' => 'cpumhz', 'formatter' => function( $d, $row ) { return (round($d/1000,2)) . " GHz"; } ),
        array( 'db' => 'h.esxbuild', 'dt' => 10, 'field' => 'esxbuild' )
      );
      $joinQuery = "FROM {$table} h INNER JOIN clusters c ON h.cluster = c.id INNER JOIN vcenters AS v ON (h.vcenter = v.id)";
      $extraCondition = "h.firstseen < '" . $dateStart . "' AND h.lastseen > '" . $dateEnd . "' GROUP BY h.moref, v.id";
      
    break; # END case 'HOSTINVENTORY':
    
  } # END switch($_GET['c'])

  require( 'class/SSP.class.php' );
  echo json_encode( SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraCondition) );

} # END if (isset($_GET['c']))
