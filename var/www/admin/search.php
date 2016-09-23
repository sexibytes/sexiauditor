<?php
require_once ('dbconnection.php');
$esxQ = $db->subQuery("hid");
$esxQ->where('host_name', '%' . $_GET['query'] . '%', 'LIKE');
$esxQ->groupBy("moref, vcenter");
$esxQ->get("hosts", null, "MAX(id) as hostid");
$db->join($esxQ, "hid.hostid = hosts.id", "INNER");
$esxhosts = $db->get("hosts", 5, array("id", "'ESX' AS type", "CONCAT('showhost.php?hostid=', id) as urlid", "host_name as name", "lastseen"));

// populate ESX results
$results = array();

foreach ($esxhosts as $row)
{
  
    $results[] = $row;

} # END foreach ($esxhosts as $row)

$vmQ = $db->subQuery("vid");
$vmQ->where('name', '%' . $_GET['query'] . '%', 'LIKE');
$vmQ->groupBy("moref, vcenter");
$vmQ->get("vms", null, "MAX(id) as vmid");
$db->join($vmQ, "vid.vmid = vms.id", "INNER");
$vms = $db->get("vms", 5, array("id", "'VM' AS type", "CONCAT('showvm.php?vmid=', id) as urlid", "name", "lastseen"));

// populate VM results
foreach ($vms as $row)
{
  
    $results[] = $row;
    
} # END foreach ($vms as $row)

$dsQ = $db->subQuery("did");
$dsQ->where('datastore_name', '%' . $_GET['query'] . '%', 'LIKE');
$dsQ->groupBy("moref, vcenter");
$dsQ->get("datastores", null, "MAX(id) as dsid");
$db->join($dsQ, "did.dsid = datastores.id", "INNER");
$ds = $db->get("datastores", 5, array("id", "'DS' AS type", "CONCAT('showdatastore.php?dsid=', id) as urlid", "datastore_name as name", "lastseen"));

// populate datastore results
foreach ($ds as $row)
{
  
    $results[] = $row;

} # END foreach ($ds as $row)

// and return to typeahead
echo json_encode($results);
?>