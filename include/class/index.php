<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: index.php 54 2007-02-07 10:08:48Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

define('ZONE_DEFAULT','gallery');
class index
{
  private static $instance = null;
  protected $content = null;
  protected $data    = array();

  static function getInstance()
  {
    if (is_null(index::$instance)) {
      $session = session::getInstance();      
      $zone    = $session->getData('zone');
      if (!$zone) {
        $zone = ZONE_DEFAULT;
        $session->setData('zone',$zone);
      } 
      if ($zone && file_exists(LOCAL_PATH.LOCAL_DIR.'module/'.$zone.'/index.php')) {
        require_once(LOCAL_PATH.LOCAL_DIR.'module/'.$zone.'/index.php');
      }
      if (class_exists('module_'.$zone.'_index')) {
        $class = 'module_'.$zone.'_index';        
        index::$instance = new $class;
      } else {
        index::$instance = new index();
      }
    }
    return index::$instance;
  }

  function __construct()
  {
  }

  function initialize()
  {
    $template = template::getInstance();
    $template->add(array('index'));

    if (!is_null($this->content)) {
      $this->content->initialize();
    }
  }

  function execute()
  {
    $session  = session::getInstance();
    $tpl      = template::getInstance();

    if (!is_null($this->content)) {
      $this->content->execute();
    }

    $tpl->clrVar();
    $tpl->setVar('title'    ,$session->getData('title')     );
    $tpl->setVar('fulltitle',$session->getData('title')     );
    $tpl->setVar('url'      ,'?');
    $tpl->getTheme();

    $this->loadUserMenu();

  }

  public function finalize()
  {
    $template = template::getInstance();
    $this->html = $template->get('index');
    $html = $this->html;
    if (!is_null($this->content)) {
      $this->content->finalize();
//      $html = $this->content->display();
    }

    $html = $template->updateLang($html);

    $html = str_replace('EXEC_TIME'  ,microtime(true) - EXEC_TIME,$html);
    $html = str_replace('APP_NAME'   ,APP_NAME   ,$html);
    $html = str_replace('APP_VERSION',APP_VERSION,$html);
    $html = str_replace('APP_BUILD'  ,APP_BUILD  ,$html);
    $html = str_replace('APP_AUTHOR' ,APP_AUTHOR ,$html);

    unset($this->module);
    $this->module = null;

    $engine = engine::getInstance();
    $engine->setResult($html);
  }

  private function loadUserMenu()
  {
    $session  = session::getInstance();
    $tpl      = template::getInstance();

    if ($session->isLogged()) {
      $tpl->setVar('isLogged' ,true);
      $tpl->setVar('username' ,$session->getUser('username'));
      
      $menu = array();
      $menu[] = array(
        'name'  => 'logout',
      );
      $tpl->setVar('userMenu',$menu);
    } else {
      $tpl->setVar('isLogged' ,false);
    }
  }

  public function getLogin()
  {
    $session  = session::getInstance();
    $template = template::getInstance();

    if ($session->isLogged()) {
      $param = array(
        'username'      => $session->getUser('username'),
        'admin_update'  => '',
      );
      if ($session->getUser('right') == 'admin') {
//        $param['admin_update'] = $this->template->get('admin_update',array());
      }
      return $template->get('index_logout',$param);
    } else {
      return $template->get('index_login',array());
    }
  }

/*
  private function loadMainModule()
  {
    if ($this->session->getData('mode')) {
      $mode = $this->session->getData('mode');
    } else {
      $mode = MODE_CATEGORY;
      $this->session->setData('mode',$mode);
    }

    switch ($mode) {
      case MODE_ALBUM :
        require_once('module/gallery/album.php');
        $this->module = new content_album();
        break;
      case MODE_VIEW :
        require_once('module/gallery/view.php');
        $this->module = new content_view();
        break;
    }

    $this->module->initialize();
  }

  public function getInfo()
  {
    if (isset($this->element) && is_array($this->element)) {
      return true;
    }

    $this->element = array();

    if ($this->getData('mode') == 'gallery') {
      $this->element[] = array(
        'title'   => $this->session->getData('title'),
        'id'      => 0,
        'link'    => getMyUrlEncode('?mode=gallery'),
        'current' => true,
      );
      if ($this->getData('lastCategoryId')) {
        $this->element[] = array(
          'title'   => $this->getData('lastCategoryTitle'),
          'id'      => $this->getData('lastCategoryId'),
          'link'    => getMyUrlEncode('?mode=category&id='.$this->getData('lastCategoryId')),
          'current' => false,
        );
      }
      if ($this->getData('lastAlbumId')) {
        $this->element[] = array(
          'title'   => $this->getData('lastAlbumTitle'),
          'id'      => $this->getData('lastAlbumId'),
          'link'    => getMyUrlEncode('?mode=album&id='.$this->getData('lastAlbumId')),
          'current' => false,
        );
      }
      if ($this->getData('lastViewId')) {
        $this->element[] = array(
          'title'   => $this->getData('lastViewTitle'),
          'id'      => $this->sgetData('lastViewId'),
          'link'    => getMyUrlEncode('?mode=view&id='.$this->getData('lastViewId')),
          'current' => false,
        );
      }
    } else {
      $this->element[] = array(
        'title'   => $this->getData('title'),
        'id'      => 0,
        'link'    => getMyUrlEncode('?mode=category&id=0'),
        'current' => false,
      );
    }

    if ($this->getData('mode') == 'category') {
      $this->element[] = array(
        'title'   => $this->module->getData('title'),
        'id'      => $this->module->getData('id'),
        'link'    => getMyUrlEncode('?mode=category&id='.$this->module->getData('id')),
        'current' => true,
      );

      if ($this->module->getData('id') != $this->getData('lastCategoryId')) {
        $this->session->setData('lastCategoryId',null);
        $this->session->setData('lastCategoryTitle',null);
        $this->session->setData('lastAlbumId',null);
        $this->session->setData('lastAlbumTitle',null);
        $this->session->setData('lastViewId',null);
        $this->session->setData('lastViewTitle',null);
      }

      if ($this->getData('lastAlbumId')) {
        $this->element[] = array(
          'title'   => $this->getData('lastAlbumTitle'),
          'id'      => $this->getData('lastAlbumId'),
          'link'    => getMyUrlEncode('?mode=album&id='.$this->getData('lastAlbumId')),
          'current' => false,
        );
      }
      if ($this->getData('lastViewId')) {
        $this->element[] = array(
          'title'   => $this->getData('lastViewTitle'),
          'id'      => $this->getData('lastViewId'),
          'link'    => getMyUrlEncode('?mode=view&id='.$this->getData('lastViewId')),
          'current' => false,
        );
      }

      $this->session->setData('lastCategoryId',$this->module->getData('id'));
      $this->session->setData('lastCategoryTitle',$this->module->getData('title'));
    }

    if ($this->getData('mode') == 'album') {
      $this->element[] = array(
        'title'   => $this->module->getData('category'),
        'id'      => $this->module->getData('cid'),
        'link'    => getMyUrlEncode('?mode=category&id='.$this->module->getData('cid')),
        'current' => false,
      );

      $this->element[] = array(
        'title'   => $this->module->getData('title'),
        'id'      => $this->module->getData('id'),
        'link'    => getMyUrlEncode('?mode=album&id='.$this->module->getData('id')),
        'current' => true,
      );

      if ($this->module->getData('id') != $this->getData('lastAlbumId')) {
        $this->session->setData('lastCategoryId',null);
        $this->session->setData('lastCategoryTitle',null);
        $this->session->setData('lastAlbumId',null);
        $this->session->setData('lastAlbumTitle',null);
        $this->session->setData('lastViewId',null);
        $this->session->setData('lastViewTitle',null);
      }

      if ($this->getData('lastViewId')) {
        $this->element[] = array(
          'title'   => $this->getData('lastViewTitle'),
          'id'      => $this->getData('lastViewId'),
          'link'    => getMyUrlEncode('?mode=view&id='.$this->getData('lastViewId')),
          'current' => false,
        );
      }

      $this->session->setData('lastCategoryId',$this->module->getData('cid'));
      $this->session->setData('lastCategoryTitle',$this->module->getData('category'));
      $this->session->setData('lastAlbumId',$this->module->getData('id'));
      $this->session->setData('lastAlbumTitle',$this->module->getData('title'));
    }

    if ($this->getData('mode') == 'view') {
      $this->element[] = array(
        'title'   => $this->module->getData('category'),
        'id'      => $this->module->getData('cid'),
        'link'    => getMyUrlEncode('?mode=category&id='.$this->module->getData('cid')),
        'current' => false,
      );

      $this->element[] = array(
        'title'   => $this->module->getData('album'),
        'id'      => $this->module->getData('aid'),
        'link'    => getMyUrlEncode('?mode=album&id='.$this->module->getData('aid')),
        'current' => false,
      );

      $this->element[] = array(
        'title'   => $this->module->getData('title'),
        'id'      => $this->module->getData('id'),
        'link'    => getMyUrlEncode('?mode=view&id='.$this->module->getData('id')),
        'current' => true,
      );

      $this->session->setData('lastCategoryId',$this->module->getData('cid'));
      $this->session->setData('lastCategoryTitle',$this->module->getData('category'));
      $this->session->setData('lastAlbumId',$this->module->getData('aid'));
      $this->session->setData('lastAlbumTitle',$this->module->getData('album'));
      $this->session->setData('lastViewId',$this->module->getData('id'));
      $this->session->setData('lastViewTitle',$this->module->getData('title'));
    }
  }

  public function getTitle()
  {
    $this->getInfo();

    $array = array();
    foreach ($this->element as $element) {
      $array[] = $element['title'];
    }
    return implode(' - ',$array);
  }
  public function getNavigator()
  {
    $this->getInfo();
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

*/
}

?>
