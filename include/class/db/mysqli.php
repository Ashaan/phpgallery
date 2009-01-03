<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: mysqli.php 54 2007-02-07 10:08:48Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

class db_mysqli extends db 
{
  private $mysqli;
  public  $error = null;

  function __construct()
  {
    $this->mysqli = new mysqli(DATABASE_HOST,DATABASE_USER,DATABASE_PASS,DATABASE_NAME);    
  }

  function __destruct()
  {
    unset($this->mysqli);
  }


  function setError($query)
  {
    if ($this->mysqli->errno) {
      $this->error = array(
        'code' => $this->mysqli->errno,
        'mesg' => $this->mysqli->error,
        'query'=> $query
      );
    }
  }

  function select($query)
  {
    $this->error = null;
    
    $query = str_replace('{pre}',DATABASE_PRE,$query);
    $result = $this->mysqli->query($query);
    if (!$result) {
      $this->setError($query);
      return false;
    }
    
    if ($result->num_rows <= 0) {
      return '';
    }

    $data = array();
    while ($rows = $result->fetch_assoc()) {
      $data[] = $rows;
    }
    return $data;
  }

  function query($query)
  {
    $this->error = null;

    $query = str_replace('{pre}',DATABASE_PRE,$query);
    $this->mysqli->query($query);
    $this->setError($query);

    return true;
  }
}

?>
