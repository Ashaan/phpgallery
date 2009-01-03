<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: archive.php 58 2007-02-08 16:19:27Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

require_once('include/class/album.php');

class media_archive extends media
{
  private $albuminfo;
  private $filelist;
  private $album;

  protected function initialize()
  {
    if (!isset($this->data['type']) || !isset($this->data['version'])) {
      $this->error('2','Paramettre manquant');
    }

    $this->album = album::getInstance();
    $this->albuminfo  = $this->album->getInfo($this->data['id']);
    if (!$this->albuminfo) {
      $this->error('4','album inexistant');
    }

    $this->pathcache = LOCAL_PATH.LOCAL_DIR.'cache/';
    $this->pathname  = LOCAL_PATH.LOCAL_DIR.'data/';
    $this->filecache = 'a'.$this->data['id'].'_'.$this->data['version'].'.'.$this->data['type'];    
  }

  protected function generate()
  {
    if (file_exists($this->pathcache.$this->filecache)) {
      return true;
    }

    $this->filelist = $this->album->getMedia($this->data['id']);

    switch ($this->data['type']) {
      case 'zip': $this->generateZip();break;
      default:
        $this->error('1','Type d\'archive incorrecte');
    }
  }

  protected function generateZip()
  {
    ini_set('max_execution_time',600);

    $mime = parse_ini_file('include/mime.ini');
    $zip  = new ZipArchive();
    if ($zip->open($this->pathcache.$this->filecache,ZIPARCHIVE::CREATE)!==true) {
      return false;
    }

    foreach ($this->filelist as $file) {
      $ext  = strtolower(substr($file['name'], strrpos($file['name'], '.')+1));
      $type = explode('/',$mime[$ext]);
      if ($type[0] != 'image') {
        continue;
      }
      $zip->addFile($this->pathname.$file['path'].'/'.$file['name'],$file['name']);
    }

    $zip->close();
    $size = filesize($this->pathcache.$this->filecache);

    $archive = array();
    if ($this->albuminfo['archive']) {
      $archive = $this->albuminfo['archive'];
    }
    $archive[$this->data['type']] = $size;

    $this->album->setInfo($this->data['id'],'archive',$archive);

    ini_restore('max_execution_time');

    return true;
  }

  protected function retrieve()
  {
    $mime = parse_ini_file('include/mime.ini');
    $this->mime = $mime[$this->data['type']];
    $handle = fopen($this->pathcache.$this->filecache,'r');
    $this->filesize = filesize($this->pathcache.$this->filecache);
    $this->flux = fread($handle,$this->filesize);
  }
}

?>
