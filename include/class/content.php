<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: content.php 54 2007-02-07 10:08:48Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

class content
{
  protected $html     = '';
  protected $data     = array();
  protected $session  = null;
  protected $engine   = null;
  protected $template = null;

  public function __construct()
  {
    $this->session  = session::getInstance();
    $this->engine   = engine::getInstance();
    $this->template = template::getInstance(); 
  }

  public function initialize(){}

  public function execute(){}

  public function finalize(){}

  public function __destruct(){}
  
  public function display()
  {
    return $this->html;
  }
  
  public function getData($name)
  {
    if (isset($this->data[$name])) {
      return $this->data[$name];
    }
    return false;
  }
  protected function setData($name,$value = null)
  {
    if (!is_array($name)) {
      $array = array(
        $name => $value
      );
    } else {
      $array = $name;
    }

    foreach ($array as $name => $value) {
      $this->data[$name] = $value;
    }

    return true;
  }
}

?>
