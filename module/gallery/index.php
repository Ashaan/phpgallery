<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: index.php 54 2007-02-07 10:08:48Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

require_once('include/class/album.php');

class module_gallery_index extends index
{
  private $album = null;

  function initialize()
  {
    $this->album = new album();
    $this->loadParam();
    $session     = session::getInstance();

    if ($session->getData('mode') == 'view') {
      require_once('module/gallery/view.php');
      $this->content = new content_view();
    } else {
      require_once('module/gallery/album.php');
      $this->content = new module_gallery_album();
      if (!$session->getData('mode')) {
        $session->setData('mode','album');
      }
    }

    index::initialize();
  }

  function execute()
  {
    index::execute();
    $tpl = template::getInstance();

    if ($tpl->existValue('index','navigator') && $tpl->existValue('index','fulltitle')) {
      $this->loadNavInfo();
      $tpl->setVar('fulltitle',$this->getFullTitle());
      $tpl->setVar('navigator',$this->navInfo);
    }
    $tpl->setVar('url',getMyUrlEncode('?mode=album&id=0'));
    $tpl->setVar('content',$this->content->display());
  }

  public function finalize()
  {
    index::finalize();
  }

  private function loadParam()
  {
    $param = $_GET;
    if (isset($param['p'])) {
      $data = unserialize(base64_decode($param['p']));
      foreach($data as $name => $value) {
        $param[$name] = $value;
      }
    }

    $session  = session::getInstance();
    $album    = album::getInstance();

    if (isset($param['theme'])) {
      $session->setData('theme',$param['theme']);
    }
    if (isset($param['template'])) {
      $session->setData('template',$param['template']);
    }
    if (isset($param['template']) || isset($param['theme'])) {
      $session->save();
      header('location: http://'.LOCAL_URL.LOCAL_DIR);
      echo 1;
    }

    if (isset($param['mode']) && isset($param['id'])) {
      if ($session->getData($param['mode'],'id') != $param['id']) {
        if($param['mode'] == 'album') {
          $parent  = $this->album->getParent($session->getData('album','id'));
          $parent2 = $this->album->getParent($session->getData('album','lastId'));
          if (!in_array($param['id'],$parent) && !in_array($param['id'],$parent2)) {
            $session->setData('album','lastId',$param['id']);
          }
        }

        $session->setData($param['mode'],'page',1);
        $session->setData($param['mode'],'id',$param['id']);
      }
    }

    if (isset($param['mode']) && $param['mode'] != $session->getData('mode')) {
      $session->setData('mode',$param['mode']);
    }

    if (isset($param['page'])) {
      $session->setData($session->getData('mode'),'page',$param['page']);
    }
  }


  private function loadNavInfo()
  {
    $session   = session::getInstance();
    $parent   = $this->album->getParent($session->getData('album','lastId'));
    $parent[] = $session->getData('album','lastId');


    $this->navInfo = array();
    foreach ($parent as $id) {
      $navInfo = $this->album->getInfo($id);
      $navInfo['mode']    = 'album';
      if ($session->getData('album','id') == $id) {
        $navInfo['current'] = '1';
      } else {
        $navInfo['current'] = '0';
      }
      if(!isset($navInfo['title'])) $navInfo['title'] = $session->getData('title');
      $this->navInfo[] = $navInfo;
    }
  }

  public function getFullTitle()
  {
    $array = array();
    foreach ($this->navInfo as $element) {
      $array[] = $element['title'];
    }
    return implode(' - ',$array);
  }

  public function getNavigator()
  {
    $array = array();
    foreach ($this->element as $element) {
      if ($element['current']) {
        $array[] = $this->template->get('content_navigator_selected',$element);
      } else {
        $array[] = $this->template->get('content_navigator',$element);
      }
    }
    return implode(' &raquo; ',$array);
  }
}
