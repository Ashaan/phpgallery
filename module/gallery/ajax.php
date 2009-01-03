<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: ajax.php 54 2007-02-07 10:08:48Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

require_once('include/class/album.php');

class module_gallery_ajax extends ajax
{
  /**
   * Enter description here...
   *
   * @var album
   */
  private $album = null;

  function initialize()
  {
    $this->album = new album();
    $session     = session::getInstance();
    ajax::initialize();
  }

  function execute()
  {
    ajax::execute();
  }


  function comGetAlbum() 
  {
    $xml = '<?xml version="1.0" encoding="UTF-8" ?>
      <RESPONSE>
        <INFO>
          <COMMAND>'.$this->command.'</COMMAND>
          <ID type="int">'.$this->data['id'].'</ID>';
    if (isset($this->data['expand'])) {
      $xml .= '<EXPAND type="array">';
      foreach (explode(',',$this->data['expand']) as $value) {
        $xml .= '<ID type="int">'.$value.'</ID>';
      }
      $xml .= '</EXPAND>';
    }
    $xml .= '
        </INFO>
        <DATA>';
    foreach ($this->album->getChild($this->data['id']) as $value) {
      $album = $this->album->getInfo($value);
      if (!$album['image']) {
        $album['image'] = -1;
      }

      $xml .= '
          <ALBUM>
            <ID>'.$album['id'].'</ID>
            <PID>'.$album['pid'].'</PID>
            <TITLE>'.$album['title'].'</TITLE>
            <DESCRIPTION>'.$album['desc'].'</DESCRIPTION>
            <IMAGE>'.$album['image'].'</IMAGE>
            <DATE>
              <FIRST>'.getMyDate($album['firstdate']).'</FIRST>
              <LAST>'.getMyDate($album['lastdate']).'</LAST>
            </DATE>
            <COUNT>
              <ALBUM>'.$album['count_album'].'</ALBUM>
              <IMAGE>'.$album['count_image'].'</IMAGE>
            </COUNT>
            <USER>
              <ID>'.$album['uid'].'</ID>
              <NAME>'.$album['username'].'</NAME>
              <FIRSTNAME></FIRSTNAME>
              <LASTNAME></LASTNAME>
            </USER>
          </ALBUM>';
    }
    $xml .= '
        </DATA>
      </RESPONSE>';

    $xml = preg_replace('/(\s*)(.*)\n/', "\\2\n", $xml);
    $xml = preg_replace('/>(\s*)\n(\s*)</', "><", $xml);
    $this->html = $xml;
  }

  function comGetImage() 
  {
    if (isset($this->data['page']) && isset($this->data['count'])) {
      $listMedia = $this->album->getMedia($this->data['id'],null,$this->data['page'],$this->data['count']);
    } else {
      $listMedia = $this->album->getMedia($this->data['id']);
      $this->data['page']  = 1;
      $this->data['count'] = count($listMedia);
    }

    $xml = '<?xml version="1.0" encoding="UTF-8" ?>
      <RESPONSE>
        <INFO>
          <COMMAND>'.$this->command.'</COMMAND>
          <ID>'.$this->data['id'].'</ID>
          <PAGE>'.$this->data['page'].'</PAGE>
          <COUNT>'.$this->data['count'].'</COUNT>
        </INFO>
        <DATA>';

    foreach ($listMedia as $value) {
      $mime = explode('/',$value['mime']);
      $xml .= '
        <IMAGE>
          <ID>'.$value['id'].'</ID>
          <ALBUM>'.$value['aid'].'</ALBUM>
          <TITLE>'.$value['title'].'</TITLE>
          <DESCRIPTION>'.$value['desc'].'</DESCRIPTION>
          <WIDTH>'.$value['width'].'</WIDTH>
          <HEIGHT>'.$value['height'].'</HEIGHT>
          <DURATION>'.$value['duration'].'</DURATION>
          <SIZE>'.$value['size'].'</SIZE>
          <DATE>'.$value['firstdate'].'</DATE>
          <MIME>
            <GROUP>'.$mime[0].'</GROUP>
            <TYPE>'.$mime[1].'</TYPE>
          </MIME>
          <USER>
            <ID>'.$value['uid'].'</ID>
            <NAME>'.$value['username'].'</NAME>
            <FIRSTNAME></FIRSTNAME>
            <LASTNAME></LASTNAME>
          </USER>
        </IMAGE>
      ';    
    }
    $xml .= '
        </DATA>
      </RESPONSE>';

    $xml = preg_replace('/(\s*)(.*)\n/', "\\2\n", $xml);
    $xml = preg_replace('/>(\s*)\n(\s*)</', "><", $xml);
    $this->html = $xml;
  }

  function comGetVideoStatus()
  {
    if (file_exists(LOCAL_PATH.LOCAL_DIR.'cache/v'.$this->data['id'].'.flv')) {
      $status = '1';
    } else {
      $status = '0';
    }

    $xml = '<?xml version="1.0" encoding="UTF-8" ?>
      <RESPONSE>
        <INFO>
          <COMMAND>'.$this->command.'</COMMAND>
          <ID>'.$this->data['id'].'</ID>
          <PID>'.$this->data['pid'].'</PID>
          <FLV>'.$status.'</FLV>
        </INFO>
      </RESPONSE>';
    $xml = preg_replace('/(\s*)(.*)\n/', "\\2\n", $xml);
    $xml = preg_replace('/>(\s*)\n(\s*)</', "><", $xml);
    $this->html = $xml;
  }

  function comGenerateVideo()
  {
    require_once('include/class/image.php');
    $image = new image(false);
    $image->video = 'flv';
    $image->id     = $this->data['id'];
    $image->getCacheName();
    $image->getInfo();
    $image->generate();
    $this->html = ' setVideoGenerated()';

    $xml = '<?xml version="1.0" encoding="UTF-8" ?>
      <RESPONSE>
        <INFO>
          <COMMAND>'.$this->command.'</COMMAND>
          <ID>'.$this->data['id'].'</ID>
          <PID>'.$this->data['pid'].'</PID>
        </INFO>
      </RESPONSE>';
    $xml = preg_replace('/(\s*)(.*)\n/', "\\2\n", $xml);
    $xml = preg_replace('/>(\s*)\n(\s*)</', "><", $xml);
    $this->html = $xml;
  }
}
