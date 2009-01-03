<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: engine.php 54 2007-02-07 10:08:48Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

require_once('include/function.php');
require_once('include/class/template.php');
require_once('include/class/index.php');
require_once('include/class/ajax.php');

class engine {
  public static $instance = null;
  // Instance d'objet
  private $template = null;
  private $session  = null;
  private $content  = null;
  // Variable
  private $ajax     = false;
  private $result   = '';

  static function getInstance($option ='')
  {
    if (is_null(engine::$instance)) {
      $instance = new engine($option);
      engine::$instance = $instance;
    }
    return engine::$instance;
  }

  public function __construct($option ='')
  {
    if ($option == 'AJAX_SESSION') {
      $this->ajax = true;
    }
    $db = db::getInstance();
    session::getInstance();
  }

  public function initialize()
  {
    $this->template = template::getInstance();
    if ($this->ajax) {
      $this->content  = ajax::getInstance();
    } else {
      $this->content  = index::getInstance();
    }
    $this->content->initialize();
  }

  public function execute()
  {
    $this->template->load();
    $this->content->execute();
  }

  public function finalize()
  {
    $this->content->finalize();

    $session = session::getInstance();
    $session->save();

    if (strstr($_SERVER['HTTP_USER_AGENT'],'W3C_Validator')===false) {
      if (strstr($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip')) {
        ob_start('ob_gzhandler');
      }
    }

    echo $this->getResult();

    unset($this->content);
    unset($this->template);
    unset($this->session);

    $this->template = null;
    $this->session  = null;
    $this->content  = null;

  }

  public function __destruct()
  {
  }

  public function setResult($value)
  {
    $this->result = $value;
  }

  public function getResult()
  {
    return $this->result;
  }
}

?>
