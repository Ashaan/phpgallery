<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: media.php 63 2007-02-22 09:41:52Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

require_once('include/class/session.php');

class media {
  static private $instance = null;
  public    $data      = array();
  protected $filename  = '';
  protected $filecache = '';
  protected $filesize  = 0;
  protected $pathcache = '';
  protected $pathname  = '';
  protected $info      = array();
  protected $mime      = '';
  protected $flux      = null;

  static public function getInstance()
  {
    if (is_null(media::$instance)) {
      if (!isset($_GET) || !is_array($_GET)) {
        return null;
      }
      foreach($_GET as $name =>$value) {
        if ($name != 'p') {
          $data[$name] = $value;
        }
      }
      if (isset($_GET['p'])) {
        $param = unserialize(base64_decode($_GET['p']));
        foreach($param as $name =>$value) {
          $data[$name] = $value;
        }
      }

      if (!isset($data['mode'])) {
        $mode = media::detectType();
      } else {
        $mode = $data['mode'];
      }


      if(file_exists(LOCAL_PATH.LOCAL_DIR.'include/class/media/'.$mode.'.php')) {
        require_once('include/class/media/'.$mode.'.php');
      }
      $class = 'media_'.$mode;
      $class = new $class($data);
      media::$instance = $class;
    }
    return media::$instance;
    
  }

  static public function detectType()
  {
    return 'image';
  }
  
  function __construct($data)
  {
    $this->data = $data;
    if (!isset($this->data['id'])) {
      if (!isset($this->data['file']) || !isset($this->data['album'])) {
        $this->error('3','Parametre manquant');
      }
    }
  }

  protected function initialize()
  {
  }

  protected function generate()
  {
  }

  protected function retrieve()
  {
  }

  public function display()
  {
    $this->initialize();
    $this->generate();
    $this->retrieve();
    header('Pragma: public');
    header('Expire: 0');
    header('Cache-Control: must-revalidate, post-check=0,pre-check=0');
    header('Cache-Control: public',false);
    header('Content-Description: File Transfer');
    header('Accept-Ranges: bytes');
    header('Content-Length: '.$this->filesize);
    header('Content-Transfer-Encoding: binary');
    header('Content-Disposition: attachment; filename="'.$this->filecache.'"');
    header('Content-type: '.$this->mime);
    echo $this->flux;
  }
  
  protected function error($id,$message = '')
  {
    echo 'Erreur Fatal '.$id.' ('.$message.')';
    exit;
  }
}

?>
