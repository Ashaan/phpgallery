<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: album.php 63 2007-02-22 09:41:52Z mathieu $
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
    if (is_null(album::$instance)) {
      album::$instance = new album();
    }
    return album::$instance;
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

  private function testParent($pid,$list) {
    if ($pid=='0') {
      return true;
    }
    foreach($list as $value) {
      if ($pid == $value['id']) {
        return $this->testParent($value['pid'],$list);
      }
    }
  }

  private function loadList()
  {
    $list = $this->loadInfo();

    $this->list = array();
    foreach ($list as $value) {
      if ($this->testParent($value['pid'],$list)) {
        $value['archive'] = $this->loadArchive($value['archive']);
        $this->list[$value['id']] = $value;
      }
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
    if (isset($this->parent[$id])) {
      return $this->parent[$id];
    }
    return array();
  }
  public function getInfo($id = 0)
  {
    if ($id == 0) {
      return array(
        'id'              => 0,
        'pid'             => 0,
        'count_album'     => count($this->child[0]),
        'count_image'     => 0,
        'count_allalbum'  => $this->count['album'],
        'count_allimage'  => $this->count['image'],
      );
    } else
    if (isset($this->list[$id])) {
      return $this->list[$id];
    } else {
      return false;
    }
  }

  public function setInfo($id,$name,$value)
  {
    if (!isset($this->list[$id])) {
      return false;
    }

    $this->list[$id][$name] = $value;
    
    if ($name=='archive') {
      $value = base64_encode(serialize($value));
    }
    $db = db::getInstance();
    $db->query('
      UPDATE {pre}album
      SET '.$name.' = "'.$value.'"
      WHERE id = '.$id.'
    ');
  }

  public function getMedia($id,$sort=null,$page=1,$count=0)
  {
    $session  = session::getInstance();
    if (!$session->getData('image',$id)) {
      $this->loadMedia($id);
    }

    $image = $session->getData('image',$id);
    if ($count==0) {
      return $image;
    }

    $array = array();
    for ($i=($page-1)*$count;$i<($page*$count);$i++) {
      if (isset($image[$i])) {
        $array[] = $image[$i];
      }
    }
    return $array;
  }

  public function getMediaInfo($id)
  {
    $session  = session::getInstance();
    $index = $session->getData('image','index');
    if (!isset($index[$id])) {
      $db = db::getInstance();
      $image = $db->select('
        SELECT aid
        FROM {pre}image
        WHERE id = '.$id.'
      '); 

      if (!is_array($image) || !isset($image[0])) {
        $index[$id] = false;
        $session->setData('image','index',$index);
        return false;
      } else {
        $this->loadMedia($image[0]['aid']);
      }

      $index = $session->getData('image','index');
    }

    if (!isset($index[$id]) || !is_array($index[$id])) {
      $index[$id] = false;
      $session->setData('image','index',$index);
      return false;
    }

    $medias = $this->getMedia($index[$id]['album']);
    return $medias[$index[$id]['index']];
    
  }

// beurk a virer des que possible
  public function getMediaId($id)
  {
    $session  = session::getInstance();
    if (!$session->getData('image',$id)) {
      $this->loadMedia($id);
    }
    $image = $session->getData('image',$id);
    $id    = array();
    foreach ($image as $value) {
      $mime = explode('/',$value['mime']);
      if ($mime[0] == 'image') {
        $code = 'I';
      } else
      if ($mime[0] == 'video') {
        $code = 'V';
      } else {
        $code = 'U';
      }
      $id[] = array(
        $value['id'],
        $code,
        $value['width'],
        $value['height'],
        getMyDuration($value['duration']),
        $value['title'],
      );
    }
    return $id;
  }

  private function loadMedia($id)
  {
    $session  = session::getInstance();
    $db = db::getInstance();
    $list = $db->select('
      SELECT I.*, U.username, IF(I.rid,B.path,A.path) AS path
      FROM {pre}image I
        LEFT JOIN {pre}user U ON U.id=I.uid 
        LEFT JOIN {pre}album A ON A.id=I.aid 
        LEFT JOIN {pre}album B ON B.id=I.rid 
      WHERE aid = '.$id.'
    ');
//echo $id;
//var_dump($list);
    if ($list) {
      $index = $session->getData('image','index');
      $list2 = array();
      foreach($list as $pos => $element) {
        $index[$element['id']] = array(
          'index' => $pos,
          'album' => $element['aid'],
        );
      }
    }
    $session->setData('image',$id,$list);
    $session->setData('image','index',$index);
  }

  private function loadInfo()
  {
    $db = db::getInstance();
    $session  = session::getInstance();
    $list = $db->select('
      SELECT A.*, COUNT(I.id) AS count_image, COUNT(I.id) AS count_allimage, 0 AS count_album, 0 AS count_allalbum, U.username AS username
      FROM {pre}album A
        LEFT JOIN {pre}user  U ON U.id = A.uid
        LEFT JOIN {pre}image I ON I.aid = A.id
      WHERE A.right <> "PRIVATE" 
         OR (A.right = "PRIVATE" AND A.uid='.$session->getUserId().')
      GROUP BY A.id
      ORDER BY A.`order`
    ');



//    if ($this->updateAlbum()) {
//      $list = $this->loadList();
//    }

    return $list;
  }

  private function loadArchive($archive)
  {
    $session  = session::getInstance();
    $types    = explode(',',$session->getData('archive'));

    $result = array();
    foreach ($types as $type) {
      $temp = false;
      if ($type == 'zip') {
        $temp = class_exists('ZipArchive');
      } else
      if ($type == 'rar') {
        $temp = false;//function_exists('rar_open');
      } else
      if ($type == 'tar.gz') {
        $temp = false;//function_exists('gzopen');
      } else
      if ($type == 'tar.bz') {
        $temp = false;//function_exists('bzopen');
      } else {
        $temp = false;
      }

      if ($temp) {
        $result[] = array(
          'type' => $type,
          'size' => 0,
        );
      }
    }

    if ($archive) {
      $archive = unserialize(base64_decode($archive));
      foreach($result as $id => $data) {
        if (isset($archive[$data['type']])) {
          $result[$id]['size'] = $archive[$data['type']];
        }
      }
    }
    return $result;
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
      WHERE status = "private"
        AND pid = 0
    ');

    if (!is_array($result) || count($result)<1) {
      $db->query('
        INSERT INTO {pre}album
        SET `pid`       = 0,
            `title`     = "default",
            `status`    = "private",
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
