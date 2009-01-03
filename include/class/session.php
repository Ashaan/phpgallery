<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: session.php 55 2007-02-07 13:10:01Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/
  
class session 
{
  static $instance = null;
  private $data;
  private $user = null;

  static function getInstance()
  {
    if (is_null(session::$instance)) {
      session::$instance = new session();
    }
    return session::$instance;
  }

  function __construct()
  {
    session_start();
    $this->initialize();
    $this->login();
    $this->logout();
  }
  function initialize()
  {
    if (isset($_SESSION) && is_array($_SESSION) && isset($_SESSION['data']) && count($_SESSION['data'])>0) {
      $this->data = $_SESSION['data'];
    } else {
      $db = db::getInstance();
      $result = $db->select('
        SELECT name,value
        FROM {pre}config
      ');
      if (is_array($result)) {
        foreach ($result as $data) {
          $this->data[$data['name']] = $data['value'];
        }
      }
    }

    if (isset($_SESSION) && is_array($_SESSION) && isset($_SESSION['user']) && count($_SESSION['user'])>0) {
      $this->user = $_SESSION['user'];
    }

    foreach ($_POST as $name => $value) {
      if ($name != 'password') {
        $this->data[$name] = $value;
      }
    }

/**
 *  Faire l'Authent ici
 **/

  }

  function getData($name,$subname=null)
  {
    if(isset($this->data[$name])) {
      if (!$subname) {
        return $this->data[$name];
      }
      if(isset($this->data[$name][$subname])) {
        return $this->data[$name][$subname];
      }
    }
    return false;
  }

  public function getUser($name)
  {
    if(isset($this->user[$name])) {
      return $this->user[$name];
    }
    return false;
  }
  public function getUserId()
  {
    if(isset($this->user['id'])) {
      return $this->user['id'];
    }
    return 0;
  }
  function setData($name,$subname = null,$value = null)
  {
    if (!is_array($name)) {
      if (!is_null($subname) && !is_null($value)) {
        if (!isset($this->data[$name])) {
          $this->data[$name] = array();
        }
        $this->data[$name][$subname] = $value;
      } else {
        $this->data[$name] = $subname;
      }
    } else {
      $array = $name;
      foreach ($array as $name => $value) {
        $this->data[$name] = $value;
      }
    }
  }

  function save()
  {
    if (!session_is_registered('data')) {
      session_register('data');
    }
    $_SESSION['data'] = $this->data;
    
    if ($this->user) {
      if (!session_is_registered('user')) {
        session_register('user');
      }
      $_SESSION['user'] = $this->user;
    }
  }

  function __destruct()
  {
  }


  public function isLogged()
  {
    return !is_null($this->user);
  }

  public function login()
  {
    if ($this->user) {
      return true;
    }

    if (!$this->getData('username') || !isset($_POST['password'])) {
      return false;
    }
/*
    for ($i=0;$i<AUTHENT_COUNT;$i++) {
      $db = db::getInstanceSpecial($i);
      $result = $db->select('
        SELECT '..'name,value
        FROM {pre}config
      ');
    }
*/
    return $this->loginInternal();
  }
  private function loginInternal()
  {
    $db = db::getInstance();
    $result = $db->select('
      SELECT *
      FROM {pre}user
      WHERE (username="'.$this->getData('username').'" OR email="'.$this->getData('username').'")
        AND password="'.md5($_POST['password']).'"
    ');

    if ($result) {
      session_destroy();
      session_start();
      $this->data = array();
      $this->initialize();      
      $this->user = $result[0];
      return true;
    }
    return false;
  }

  private function logout()
  {
    if (isset($this->data['action']) && $this->data['action'] == 'logout') {
      session_destroy();
      session_start();
      $this->data = array();
      $this->user = null;
      $this->initialize();      
    }
  }
}

?>
