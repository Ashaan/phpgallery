<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: ajax.php 54 2007-02-07 10:08:48Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

class ajax
{
  private static $instance = null;
  protected $content = null;
  protected $command = false;
  protected $data    = array();
  protected $html;

  static function getInstance()
  {
    if (is_null(ajax::$instance)) {
      $session = session::getInstance();      
      $zone    = $session->getData('zone');
      if (!$zone) {
        $zone = ZONE_DEFAULT;
        $session->setData('zone',$zone);
      } 
      if ($zone && file_exists(LOCAL_PATH.LOCAL_DIR.'module/'.$zone.'/ajax.php')) {
        require_once(LOCAL_PATH.LOCAL_DIR.'module/'.$zone.'/ajax.php');
      }
      if (class_exists('module_'.$zone.'_ajax')) {
        $class = 'module_'.$zone.'_ajax';        
        ajax::$instance = new $class;
      } else {
        ajax::$instance = new ajax();
      }
    }
    return ajax::$instance;
  }

  function __construct()
  {
    if (isset($_GET['command'])) {
      $this->command = $_GET['command'];
      foreach ($_GET as $name => $value) {
        $this->data[$name] = $value;
      }
    } else {
      $this->data = $_GET['rsargs'];
    }
  }

  function initialize()
  {

  }

  function execute()
  {
    if ($this->command) {
      $function = 'com'.ucfirst($this->command);
      $this->$function();
    }
  }

  public function finalize()
  {
    $engine = engine::getInstance();
    if ($this->command) {
      header('Content-Type: text/xml');
//      header('Content-length: '.strlen
    }
    $engine->setResult($this->html);
  }
}

?>
