<?php
require_once ('dbconnection.php');
$cols = Array ("id", "'ESX' AS type", "host_name as name");
$db->where('host_name', '%' . $_GET['query'] . '%', 'LIKE');
$esxhosts = $db->get("hosts", 5, $cols);

// populate results
$results = array();
foreach ($esxhosts as $row) {
    $results[] = $row;
}

$cols = Array ("id", "'VM' AS type", "name");
$db->where('name', '%' . $_GET['query'] . '%', 'LIKE');
$vms = $db->get("vms", 5, $cols);
foreach ($vms as $row) {
    $results[] = $row;
}

// and return to typeahead
echo json_encode($results);
?>
