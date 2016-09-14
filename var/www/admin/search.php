<?php
require_once ('dbconnection.php');
$cols = Array ("id", "'ESX' AS type", "CONCAT('showhost.php?hostid=', id) as urlid", "host_name as name");
$db->where('host_name', '%' . $_GET['query'] . '%', 'LIKE');
$esxhosts = $db->get("hosts", 5, $cols);

// populate ESX results
$results = array();
foreach ($esxhosts as $row) {
    $results[] = $row;
}

$cols = Array ("id", "'VM' AS type", "CONCAT('showvm.php?vmid=', id) as urlid", "name");
$db->where('name', '%' . $_GET['query'] . '%', 'LIKE');
$vms = $db->get("vms", 5, $cols);

// populate VM results
foreach ($vms as $row) {
    $results[] = $row;
}

$cols = Array ("id", "'DS' AS type", "CONCAT('showdatastore.php?dsid=', id) as urlid", "datastore_name as name");
$db->where('datastore_name', '%' . $_GET['query'] . '%', 'LIKE');
$vms = $db->get("datastores", 5, $cols);

// populate datastore results
foreach ($vms as $row) {
    $results[] = $row;
}

// and return to typeahead
echo json_encode($results);
?>