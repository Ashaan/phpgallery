<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: view.php 54 2007-02-07 10:08:48Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

class view 
{
  private $id   = null;
  private $list = null;
  private $info = null;

  function __construct($id,$info=null)
  {
    $this->id   = $id;
    $this->info = $info;
  }

  function load()
  {
    $this->loadImage();
    $this->loadElement();
  }

  function loadImage()
  {
    $db = db::getInstance();

    if (is_null($this->info)) {
      $info = $db->select('
        SELECT I.*, A.title AS album, A.path AS path, C.id AS cid, C.title AS category
        FROM {pre}image I
          LEFT JOIN {pre}album A ON A.id=I.aid
          LEFT JOIN {pre}category C ON C.id=A.cid
        WHERE I.id = '.$this->id.'
        GROUP BY A.id
      ');
      $this->info = $info[0];
    }
  }

  function loadElement()
  {
    $db = db::getInstance();
    $this->list = $db->select('
      SELECT id
      FROM {pre}image 
      WHERE aid = (
        SELECT aid
        FROM {pre}image
        WHERE id='.$this->id.'
      )
    ');
  }

  public function getList()
  {
    return $this->list;
  }

  public function getInfo()
  {
    return $this->info;
  }  

  public function getData($name)
  {
    $this->info[$name];
  }
}

?>
