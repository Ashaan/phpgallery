<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: video.php 63 2007-02-22 09:41:52Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

require_once('include/class/album.php');

class media_video extends media
{
  private $videoinfo;
  private $filelist;
  private $album;

  protected function initialize()
  {
    if (!isset($this->data['type'])) {
      $this->error('2','Paramettre manquant');
    }

    $this->album = album::getInstance();
    $this->videoinfo  = $this->album->getMediaInfo($this->data['id']);
    if (!$this->videoinfo) {
      $this->error('4','album inexistant');
    }

    $this->pathcache = LOCAL_PATH.LOCAL_DIR.'cache/';
    $this->pathname  = LOCAL_PATH.LOCAL_DIR.'data/';
    $type = $this->data['type'];

    $this->filecache = 'v'.$this->data['id'].'.'.$type;    
  }

  protected function generate()
  {
    if (file_exists($this->pathcache.$this->filecache)) {
      return true;
    }

    switch ($this->data['type']) {
      case 'flv': $this->generateFlv('flv');break;
      default:
        $this->error('1','Type d\'archive incorrecte');
    }
  }

  protected function generateFlv($type)
  {
    ini_set('max_execution_time',600);

    $ff = new ffmpeg_movie($this->pathname.$this->videoinfo['path'].'/'.$this->videoinfo['name']);
    if (!$ff) {
      $this->error('5','Format non supporté');
    }

    $source  = $this->pathname.$this->videoinfo['path'].'/'.$this->videoinfo['name'];
    $source  = str_replace(' ','\ ',$source);
    $source  = str_replace('(','\(',$source);
    $source  = str_replace(')','\)',$source);
    $cible   = $this->pathcache.$this->filecache;
    $command = 'ffmpeg -y -i '.$source.' -acodec mp3 -b 200 -s 300x400 -ar 22050 -f '.$type.' '.$cible;

    passthru($command,$res);
    if($res) {
      $this->error('6','Echec lors de la conversion');
    }

    ini_restore('max_execution_time');

    return true;
  }

  protected function retrieve()
  {
//echo 'Location: http://'.LOCAL_URL.LOCAL_DIR.'cache/v'.$this->data['id'].'.flv';
//    header('Location: http://'.LOCAL_URL.LOCAL_DIR.'cache/v'.$this->data['id'].'.flv'); 
//    exit;
    $mime = parse_ini_file('include/mime.ini');
    $this->mime = $mime[$this->data['type']];
    $handle = fopen($this->pathcache.$this->filecache,'r');
    $this->filesize = filesize($this->pathcache.$this->filecache);
    $this->flux = fread($handle,$this->filesize);
  }
}

?>
