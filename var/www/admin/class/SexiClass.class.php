<?php

class SexiClass
{
  
  private $checkType = "";
  private $title;
  private $description;
  private $thead = array();
  private $tbody = array();
  private $order;
  private $columnDefs;
  private $powerChoice;
  private $servicePolicyChoice;
  private $alarmStatus;
  private $header;
  private $body;
  private $footer;
  private $graph;
  private $achievementFile;
  private $selectedDate;
  // private $lang;
  // private $langDef;
  private $db;

  public function __construct()
  {
    
    global $achievementFile;
    $this->achievementFile = $achievementFile;
    # database instanciation so we can use $db object in this class methods
    require("dbconnection.php");
    $this->db = $db;
    // $this->lang = (defined($this->getConfig('lang'))) ? $this->getConfig('lang') : 'en';
    // 
    // switch ($this->lang)
    // {
    //   
    //   case 'en':
    //     $lang_file = 'lang.en.php';
    //   break; # END case 'en':
    //   
    //   case 'fr':
    //     $lang_file = 'lang.fr.php';
    //   break; # END case 'fr':
    //   
    //   default:
    //   $lang_file = 'lang.en.php';
    //   
    // } # END switch ($this->lang)

    // include_once 'locales/'.$lang_file;
    // require_once("class/SexiLang.class.php");
    // $classLang = new SexiLang();
    // global $classLang;
    // $this->langDef = $classLang->getAllLocaleText();
    global $powerChoice;
    global $alarmStatus;
    global $servicePolicyChoice;
    $this->powerChoice = $powerChoice;
    $this->alarmStatus = $alarmStatus;
    $this->servicePolicyChoice = $servicePolicyChoice;
    
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

} # END class SexiClass

?>
