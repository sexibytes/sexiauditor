<?php

class SexiLang
{
  
  private $lang;
  private $langDef;

  public function __construct()
  {
    
    # database instanciation so we can use $db object in this class methods
    require("dbconnection.php");
    $this->db = $db;
    $this->lang = (defined($this->getConfig('lang'))) ? $this->getConfig('lang') : 'en';
    
    switch ($this->lang)
    {
      
      case 'en':
        $lang_file = 'lang.en.php';
      break; # END case 'en':
      
      case 'fr':
        $lang_file = 'lang.fr.php';
      break; # END case 'fr':
      
      default:
      $lang_file = 'lang.en.php';
      
    } # END switch ($this->lang)

    include_once 'locales/'.$lang_file;
    $this->langDef = $lang;
    
  } # END public function __construct()

  private function getConfig($config)
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
    
  } # END private function getConfig($config)
  
  public function getLocaleText($textId)
  {
    // var_dump($textId);
    // var_dump($this->langDef);
    return (array_key_exists($textId, $this->langDef) ? $this->langDef[$textId] : "$textId-undefined");
    
  } # END private function getLocaleText($textId)
  
  public function getAllLocaleText()
  {
    
    return $this->langDef;
    
  } # END public function getAllLocaleText()

} # END class SexiLang

?>
