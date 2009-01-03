<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: category.php 54 2007-02-07 10:08:48Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

class album
{
  private static $instance = null;
  // Variable
  private $path   = '';
  private $list   = array();
  private $child  = array();
  private $parent = array();
  private $count  = array(
    'album' => 0,
    'image' => 0,
  );

  public static function getInstance()
  {
    if (is_null(category::$instance)) {
      category::$instance = new category();
    }
    return category::$instance;
  }

  public function __construct()
  {
    $this->path     = LOCAL_PATH.LOCAL_DIR.'data/';
    $this->load();
  }

  public function __destruct()
  {
    unset($this->dir);
  }

  public function load()
  {
    $session  = session::getInstance();

    if ($session->getData('album','list') && $session->getData('album','child') && $session->getData('album','parent')) {
      $this->list   = $session->getData('album','list');
      $this->child  = $session->getData('album','child');
      $this->parent = $session->getData('album','parent');
    } else {
      $this->loadList();
    }

    foreach ($this->child[0] as $index) {
      $this->count['image'] += $this->list[$index]['count_allimage'];
      $this->count['album'] += $this->list[$index]['count_allalbum'];
    }
  }

  private function loadList()
  {
    $list = $this->loadInfo();

    $this->list = array();
    foreach ($list as $value) {
      $this->list[$value['id']] = $value;
    }    
    
    $this->child = array();
    $this->child[0] = $this->loadIndex(0,array());

    $session  = session::getInstance();
    $session->setData('album','list'  ,$this->list);
    $session->setData('album','child' ,$this->child);
    $session->setData('album','parent',$this->parent);    
  }

  private function loadIndex($id,$parent)
  {
    $array    = array();
    $parent[] = $id; 
    foreach ($this->list as $value) {
      if ($value['pid'] == $id) {
        $this->child[$value['id']]  = $this->loadIndex($value['id'],$parent);
        $this->parent[$value['id']] = $parent;
        $this->list[$value['id']]['count_album']    = count($this->child[$value['id']]);
        $this->list[$value['id']]['count_allalbum'] = count($this->child[$value['id']]);

        foreach ($this->child[$value['id']] as $data) {
          $this->list[$value['id']]['count_allimage'] += $this->list[$data]['count_allimage'];
          $this->list[$value['id']]['count_allalbum'] += $this->list[$data]['count_allalbum'];
        }

        $this->list[$value['id']]['is_category']    = ($this->list[$value['id']]['count_album']>0);
        $this->list[$value['id']]['is_album']       = ($this->list[$value['id']]['count_image']>0);

        $array[]    = $value['id'];
      }
    }
    return $array;
  }


  public function getChild($id = 0)
  {
    return $this->child[$id];
  }
  public function getParent($id = 0)
  {
    return $this->parent[$pid];
  }
  public function getInfo($id = 0)
  {
    if ($id == 0) {
      return $this->count;
    } else {
      return $this->list[$id];
    }
  }
  public function getContent($id,$sort=null,$page=1,$count=0)
  {
    $session  = session::getInstance();
    if ($session->getData('image',$id)) {
      $this->loadContent($id);
    }

    $image = $session->getData('image',$id);
    if ($count==0) {
      return $image;
    }
    $array = array();
    for ($i=($page-1)*$count;$i<($page*$count)-1;$i++) {
      if (isset($image[$i])) {
        $array[] = $image[$i];
      }
    }
    return $array;
  }

  private function loadContent($id)
  {
    $db = db::getInstance();
    $list = $db->select('
      SELECT *
      FROM {pre}image
      WHERE aid = '.$id.'
    ');
    $session->setData('image',$id,$list);
  }

  private function loadInfo() 
  {
    $db = db::getInstance();
    $list = $db->select('
      SELECT A.*, COUNT(I.id) AS count_image, COUNT(I.id) AS count_allimage, 0 AS count_album, 0 AS count_allalbum
      FROM {pre}album A
        LEFT JOIN {pre}image I ON I.aid = A.id    
      GROUP BY A.id
    ');

/*
    if ($this->updateAlbum()) {
      $list = $this->loadList();
    }
*/
    return $list;
  }





  private function updateAlbum()
  {
    $db = db::getInstance();

    // Recuperation de la liste des repertoires d'albums
    $dirlist  = scandir($this->path);
    foreach ($dirlist as $id => $dir) {
      if (substr($dir,0,1) == '.' || !is_dir($this->path.$dir)) {
        unset($dirlist[$id]);
      }
    }

    // Suppression des albums qui ne sont pas lier a des repertoires
    $query = array();
    foreach ($dirlist as $dir) {
      $query[] = '"'.$dir.'"';
    }
    $db->query('
      DELETE FROM {pre}image
      WHERE aid IN (
        SELECT id
        FROM {pre}album
        WHERE path NOT IN ('.implode(',',$query).')
          AND path IS NOT NULL
      )
    ');
    if (!is_null($db->error)) {
      echo 'Error - cannot delete old images from database<br/>';
    }
    $db->query('
      DELETE FROM {pre}album
      WHERE path NOT IN ('.implode(',',$query).')
        AND path IS NOT NULL
    ');
    if (!is_null($db->error)) {
      echo 'Error - cannot delete old albums from database<br/>';
    }

    // Recuperation de la liste des albums
    $albums = $db->select('
      SELECT * 
      FROM {pre}album
      WHERE path IS NOT NULL
    ');

    if (!is_null($db->error)) {
      var_dump($db->error);
    }
    if (!is_array($albums)) {
      $albums = array();
    }

    // Mise a jour des albums
    $oldlist = array();
    foreach ($albums as $id => $album) {
      if (in_array($album['path'],$dirlist)) {
        $oldlist[] = $album['path'];
      }
    }
    foreach ($dirlist as $dir) {
      if (!in_array($dir,$oldlist)) {
        $albums[] = $this->addAlbum($dir);
      }
    }
    
    // Mise a jour des images
    foreach ($albums as $id => $album) {
      $this->updateImage($album);
    }
  }

  private function addAlbum($dir)
  {
    $db = db::getInstance();
//exit;
    // Verification de l'album par defaut
    $result = $db->select('
      SELECT id
      FROM {pre}album
      WHERE status = "default"
    ');   

    if (!is_array($result) || count($result)<1) {
      $db->query('
        INSERT INTO {pre}album
        SET `pid`       = 0,
            `title`     = "default",
            `status`    = "default",
            `order`     = 0,
            `image`     = NULL,
            `firstdate` = '.time().',
            `lastdate`  = '.time().'
      ');
      $this->addAlbum($dir);
    } else {
      $pid = $result[0]['id'];
    }

    // Insertion d'un nouvel album
    $db->query('
      INSERT INTO {pre}album
      SET `pid`       = '.$pid.',
          `path`      = "'.$dir.'",
          `title`     = "'.$dir.'",
          `desc`      = NULL,
          `order`     = 0,
          `displayed` = 1,
          `image`     = NULL,
          `firstdate` = '.time().',
          `lastdate`  = '.time().'
    ');
    if (!is_null($db->error)) {
      var_dump($db->error);
    }
    // recuperation et verification du nouvel album 
    $query = $db->select('
      SELECT * 
      FROM {pre}album
      WHERE path = "'.$dir.'"
    ');
    if (!is_null($db->error)) {
      var_dump($db->error);
    }

    return $query[0];
  }

  private function updateImage($album)
  {
    $db       = db::getInstance();
    $mime     = parse_ini_file("include/mime.ini");

    // Recuperation de la liste des fichiers
    $filelist  = scandir($this->path.$album['path']);
    foreach ($filelist as $id => $file) {
      $type = strtolower(substr($file,strrpos($file,'.')+1,strlen($file)));
      if (substr($file,0,1) == '.' || is_dir($this->path.$album['path'].'/'.$file) || !array_key_exists($type, $mime)) {
        unset($filelist[$id]);
      }
    }
   
    // Recuperation de la liste des images
    $images = $db->select('
      SELECT *
      FROM {pre}image
      WHERE aid = '.$album['id'].'
    ');
    if (!is_null($db->error)) {
      var_dump($db->error);
    }
    if (!is_array($images)) {
      $images = array();
    }

    // Suppression des images n'etant plus lier a des fichier
    $query = array();
    foreach ($filelist as $file) {
      $query[] = '"'.$file.'"';
    }
    if (count($query)>0) {
      $db->query('
        DELETE FROM {pre}image
        WHERE name NOT IN ('.implode(',',$query).')
          AND aid = '.$album['id'].'
      ');
      if (!is_null($db->error)) {
        echo 'Error - cannot delete old images from database<br/>';
      }
    }

    // Ajout des nouvelles images
    $oldlist = array();
    foreach ($images as $id => $image) {
      if (in_array($image['name'],$filelist)) {
        $oldlist[] = $image['name'];
      }
    }
    foreach ($filelist as $file) {
      if (!in_array($file,$oldlist)) {
        $extension = strtolower(substr($file,strrpos($file,'.')+1,strlen($file)));
        $images[] = $this->addImage($album,$file,$mime[$extension]);
        if(!$album['image']) $album['image'] = true;
      }
    }
  }

  private function addImage($album,$file,$mime)
  {
    $db = db::getInstance();

    // Insertion d'une nouvelle image
    $db->query('
      INSERT INTO {pre}image
      SET `aid`       = '.$album['id'].',
          `name`      = "'.$file.'",
          `title`     = "'.$file.'",
          `mime`      = "'.$mime.'",
          `width`     = 0,
          `height`    = 0,
          `firstdate` = '.time().'
    ');
    if (!is_null($db->error)) {
      echo 'Error - cannot add new image';
    }

    // recuperation et verification de la nouvelle image
    $image = $db->select('
      SELECT * 
      FROM {pre}image
      WHERE name = "'.$file.'"
        AND aid  = '.$album['id'].'
    ');
    if (!is_null($db->error)) {
      echo 'Error - cannot retreive new image';
    }

    if (!$album['image'] && is_array($image) && $image[0]['id']) {
      $db->query('
        UPDATE {pre}album
        SET image = '.$image[0]['id'].',
            lastdate = '.time().'
        WHERE id = '.$album['id'].'
      ');
      if (!is_null($db->error)) {
        echo 'Error - cannot update album';
      }
    }
  }
}

?>
