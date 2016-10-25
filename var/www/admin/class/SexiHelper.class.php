<?php

class SexiHelper
{
  
  private $powerChoice;
  private $servicePolicyChoice;
  private $alarmStatus;
  private $achievementFile;
  private $db;

  public function __construct()
  {
    
    global $achievementFile;
    $this->achievementFile = $achievementFile;
    # database instanciation so we can use $db object in this class methods
    require("dbconnection.php");
    $this->db = $db;
    global $powerChoice;
    global $alarmStatus;
    global $servicePolicyChoice;
    $this->powerChoice = $powerChoice;
    $this->alarmStatus = $alarmStatus;
    $this->servicePolicyChoice = $servicePolicyChoice;
    $dateAvailable = $this->dbGetDate();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST["selectedDate"]))
    {
      
      $this->selectedDate = $_POST["selectedDate"];
      
    }
    else
    {
      
      if (count($dateAvailable) > 0)
      {
      
        $this->selectedDate = DateTime::createFromFormat('Y-m-d H:i:s', $dateAvailable[0]['date'])->format('Y/m/d');
        
      }
      else
      {
        
        $this->selectedDate = NULL;
        
      }
      
    } # END if ($_SERVER['REQUEST_METHOD'] == 'POST')
    
  } # END public function __construct()

  public function getModuleSchedule($module)
  {
    
    $this->db->where('module', $module);
    $resultSchedule = $this->db->getOne("modules", "schedule");
    return $resultSchedule['schedule'];
    
  } # END public function getModuleSchedule($module)

  public function getConfig($config)
  {
    
    $this->db->where('configid', $config);
    $resultConfig = $this->db->getOne("config", "value");
    
    if ($this->db->count > 0)
    {
      
      return $resultConfig['value'];
      
    }
    else
    {
      
      return "undefined";
      
    } # END if ($this->db->count > 0)
    
  } # END public function getConfig($config)

  public function getUserAgent($useragentPattern)
  {
    
    global $userAgent;
    
    if ($useragentPattern == 'VI Perl')
    {
      
      return $userAgent['Perl'];
      
    }
    elseif (preg_match("/^VMware \w* Client/", $useragentPattern))
    {
      
      return $userAgent['Client'];
      
    }
    elseif (preg_match("/^Mozilla/", $useragentPattern))
    {
      
      return $userAgent['Mozilla'];
      
    }
    elseif (preg_match("/^VMware vim-java/", $useragentPattern))
    {
      
      return $userAgent['java'];
      
    }
    elseif (preg_match("/^PowerCLI/", $useragentPattern))
    {
      
      return $userAgent['PowerCLI'];
      
    }
    else
    {
      
      return "undefined";
      
    } # END if ($useragentPattern == 'VI Perl')
    
  } # END public function getUserAgent($useragentPattern)

  public function getServicePolicyChoice()
  {
    
    return $this->servicePolicyChoice;
    
  } # END public function getServicePolicyChoice()

  public function getSelectedPath()
  {
    
    return $this->xmlStartPath.$this->xmlSelectedPath;
    
  } # END public function getSelectedPath()

  public function getSelectedDate()
  {
    
    return $this->selectedDate;
    
  } # END public function getSelectedDate()

  public function getVMInfos($vmID)
  {
    
    $this->db->join("hosts h", "vms.host = h.id", "INNER");
    $this->db->join("clusters c", "h.cluster = c.id", "INNER");
    $this->db->join("vcenters v", "vms.vcenter = v.id", "INNER");
    $this->db->join("vmMetrics vmm", "vms.id = vmm.vm_id", "INNER");
    $this->db->where('vms.id', $vmID);
    $resultVM = $this->db->getOne("vms", "vms.*, vmm.swappedMemory, vmm.compressedMemory, vmm.commited, vmm.balloonedMemory, vmm.uncommited, vms.host as hostid, c.cluster_name as cluster, h.host_name as host, v.vcname as vcenter, v.id as vcenterID");

    if ($this->db->count > 0)
    {
      
      return $resultVM;
      
    }
    else
    {
      
      return "undefined";
      
    } # END if ($this->db->count > 0)
    
  } # END public function getVMInfos($vmID)

  public function getDatastoreInfos($datastoreID)
  {
    
    $this->db->join("datastoreMetrics dm", "datastores.id = dm.datastore_id", "INNER");
    $this->db->join("vcenters v", "datastores.vcenter = v.id", "INNER");
    $this->db->where('datastores.id', $datastoreID);
    $resultVM = $this->db->getOne("datastores", "datastores.*, dm.*, ROUND(100*(freespace/size)) as pct_free, v.vcname as vcenter");
    
    if ($this->db->count > 0)
    {
      
      return $resultVM;
      
    }
    else
    {
      
      return "undefined";
      
    } # END if ($this->db->count > 0)
    
  } # END public function getDatastoreInfos($datastoreID)

  public function getHostInfos($hostID)
  {
    
    $this->db->join("clusters c", "hosts.cluster = c.id", "INNER");
    $this->db->join("vcenters v", "hosts.vcenter = v.id", "INNER");
    $this->db->where('hosts.id', $hostID);
    $resultHost = $this->db->getOne("hosts", "hosts.*, c.cluster_name as cluster, v.vcname as vcenter");
    
    if ($this->db->count > 0)
    {
      
      return $resultHost;
      
    }
    else
    {
      
      return "undefined";
      
    } # END if ($this->db->count > 0)
    
  } # END public function getHostInfos($hostID)
  
  private function dbGetDate()
  {
    
    $this->db->orderBy("date","desc");
    $this->db->groupBy("DATE(executiontime.date)");
    $resultDate = $this->db->get('executiontime', NULL, 'date');
    return $resultDate;
    
  } # END private function dbGetDate()
  
  public function buildSqlQueryCPGroup($CPGroupMembers)
  {
    
    $sqlQuery = " (";
    $firstMember = true;

    if (count($CPGroupMembers) == 0)
    {
      
      return "$sqlQuery TRUE )";
      
    } # END if (count($CPGroupMembers) == 0)
    
    foreach (explode (";", $CPGroupMembers) as $CPGroupMember)
    {
      
      if ($firstMember)
      {
        
        $firstMember = false;
        $sqlQuery = $sqlQuery . "c.cluster_name LIKE '" . $CPGroupMember . "'";
        
      }
      else
      {
        
        $sqlQuery = $sqlQuery . " OR c.cluster_name LIKE '" . $CPGroupMember . "'";
        
      } # END if ($firstMember)
      
    } # END foreach (explode (";", $CPGroupMembers) as $CPGroupMember)
    
    return $sqlQuery . ")";
    
  } # END public function buildSqlQueryCPGroup()

} # END class SexiHelper

?>
