<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: album.php 54 2007-02-07 10:08:48Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

require_once('include/class/content.php');

class module_gallery_album extends content
{
  private $album = null;
  private $id = 0;

  public function initialize()
  {
    $this->template = template::getInstance();
    $this->session  = session::getInstance();
    $this->album    = album::getInstance();
    $this->template->add(array('album'));
    $this->id       = $this->session->getData('album','id');
    $this->page     = $this->session->getData('album','page'); 
  }

  public function execute()
  {
    $html = '';
    $data = $this->album->getInfo($this->id);
    $data['album'] = array();
    $data['parentList']= $this->album->getParent($this->id);
    $data['parentList']= implode(',',$data['parentList']);
    $data['media'] = array();
    $data['url']   = 'http://'.LOCAL_URL.LOCAL_DIR;
    if ($data['count_album'] > 0) {
      foreach ($this->album->getChild($this->id) as $childId) {
        $data['album'][] = $this->getChild($childId);
      }
    } else
    if ($data['count_image'] > 0) {
      $data['media'] = $this->album->getMedia($this->id);
      $data['page_current'] = ($this->page<1)?1:$this->page;
      $data['page_prev']  = $data['page_current']-1;
      $data['page_next']  = $data['page_current']+1;
      foreach ($this->album->getMediaId($this->id) as $value) {
        $data['mediaId'][] = '['.$value[0].',"'.$value[1].'","'.$value[2].'","'.$value[3].'","'.$value[4].'","'.$value[5].'"]';
      }
      $data['mediaId'] = implode($data['mediaId'],',');
    }

    $this->template->setVar($data);
    $this->html = $this->template->get('album');
  }

  private function getChild($id)
  {
    $data = $this->album->getInfo($id); 
    $data['album'] = array();
    $data['media'] = array();

    if ($data['count_album'] > 0) {
      foreach ($this->album->getChild($id) as $childId) {
        $album = $this->album->getInfo($childId); 
        $data['album'][] = $album; 
        
      }
    } else
    if ($data['count_image'] > 0) {
      $data['media'] = $this->album->getMedia($id);
    }
   
    return $data;
  }

}

?>
