<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: db.php 54 2007-02-07 10:08:48Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

class db {
  static $instance = null;

  static function getInstance()
  {
    if (is_null(db::$instance)) {
      if ( file_exists('include/class/db/'.DATABASE_TYPE.'.php') ) {
        require_once('include/class/db/'.DATABASE_TYPE.'.php');
        $class = 'db_'.DATABASE_TYPE;
        db::$instance = new $class();
      }
    }
    return db::$instance;
  }

  function __construct()
  {
  }

  function __destruct()
  {
  }
} 

?>
