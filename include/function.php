<?php
/**
 * @author    Mathieu Chocat
 * @name      $HeadURL http://svn.sygil.eu/gallery/index.php $
 * @version   $Id: function.php 59 2007-02-08 16:19:37Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

function getMyDate($datetime)
{
  $session = session::getInstance();

  if ($datetime > time()-60*60*24) {
    $datetime = date($session->getData('time'),$datetime);
  } else
  if ($datetime > time()-60*60*24*2) {
    $datetime = '{lang_yesterday} '.date($session->getData('time'),$datetime);
  } else
  if ($datetime > time()-60*60*24*30) {
    $datetime = date($session->getData('shortdate').' '.$session->getData('time'),$datetime);
  } else {
    $datetime = date($session->getData('shortdate'),$datetime);
  }

  return $datetime;
}

function getMySize($size) 
{
  if ($size > 1024*1024*1024) {
    return round($size / (1024*1024*1024),2).'Go';
  } else
  if ($size > 1024*1024) {
    return round($size / (1024*1024),2).'Mo';
  } else
  if ($size > 1024) {
    return round($size / (1024),2).'ko';
  } else {
    return $size.'o';
  }
}

function getMyDuration($sec) 
{
  $data = '';
  if ($sec >= 60*60*24) {
    $j = floor($sec/60*60*24);
    $data .= $j.'{lang_shortday}';
    $sec = $sec - ($j*60*60*24);
    if ($sec>0) $data .= '';
  }
  if ($sec >= 60*60) {
    $h = floor($sec/60*60);
    $data .= $h.'{lang_shorthour}';
    $sec = $sec - ($h*60*60);
    if ($sec>0) $data .= '';
  }
  if ($sec >= 60) {
    $m = floor($sec/60);
    $data .= $m.'{lang_shortminute}';
    $sec = $sec - ($m*60);
    if ($sec>0) $data .= '';
  }

  return $data.$sec.'{lang_shortsecond}';
}

function getMyUrlEncode ($url)
{
  if (strpos($url,'?') !== false) {
    $url   = explode('?',$url);
    $param = explode('&',$url[1]);
    $encode = array();
    foreach($param as $value) {
      $value = explode('=',$value);    
      $encode[$value[0]] = $value[1];
    }
    $url = $url[0].'?p='.base64_encode(serialize($encode)); 
  }

  if (strpos($url,'http://') === false) {
    $url = 'http://'.LOCAL_URL.LOCAL_DIR.$url;
  } 

  return $url;
}

function getImageLink($id,$width = 0,$height = 0, $mask = '', $trans = false)
{
  $file = 'i'.$id;
  $link = 'image.php?id='.$id;
  if ($width) {
    $file .= '_w'.$width;
    $link .= '&w='.$width;
  }
  if ($height) {
    $file .= '_h'.$height;
    $link .= '&h='.$height;
  }
  if ($mask) {
    $file .= '_m'.$mask;
    $link .= '&m='.$mask;
  }

  $session = session::getInstance();
  $file .= '_tpl'.$session->getData('template');
  $file .= '_thm'.$session->getData('theme');

  if ($trans) {
    $file .= '.png';
    $link .= '&t=1';
  } else {
    $file .= '.jpg';
  }

  if (file_exists(LOCAL_PATH.LOCAL_DIR.'cache/'.$file)) {
    return getMyUrlEncode('cache/'.$file);
  }
  return getMyUrlEncode($link);
}

function getArchiveLink($id,$type,$version)
{
//  $file = 'a'.$id.'_'.$version.'.'.$type;
//  if (file_exists(LOCAL_PATH.LOCAL_DIR.'cache/'.$file)) {
//    return getMyUrlEncode('cache/'.$file);
//  }
  return getMyUrlEncode('media.php?mode=archive&type='.$type.'&version='.$version.'&id='.$id);
}
?>
