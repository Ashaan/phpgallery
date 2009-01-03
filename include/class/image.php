<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: image.php 54 2007-02-07 10:08:48Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

define('jpg,jpeg','');
require_once('include/class/session.php');

class image {

  public $path       = '';
  public $file       = null;
  public $id         = null;
  public $album      = null;
  public $cache_path = '';
  public $cache_file = null;

  public $width      = 0;
  public $height     = 0;
  public $mask       = '';
  public $trans      = false;

  public $cached     = false;
  public $error      = false;
  public $data       = null;
  public $mime       = null;
  public $unknown    = false;
  public $info       = array();
  public $archive    = null;
  public $video      = false;
  public $size       = 0;
  private $session   = null;
  function __construct($auto = true)
  {
    $this->session = session::getInstance();
    $this->path       = LOCAL_PATH.LOCAL_DIR.'data/';
    $this->cache_path = LOCAL_PATH.LOCAL_DIR.'cache/';

    if ($auto) {
      if (isset($_GET['p'])) {
        $param = unserialize(base64_decode($_GET['p']));
        foreach($param as $pname =>$pvalue) {
          $_GET[$pname] = $pvalue;
        }
      }

      if (isset($_GET['w'])) {
        $this->width      = $_GET['w'];
      }
      if (isset($_GET['h'])) {
        $this->height     = $_GET['h'];
      }
      if (isset($_GET['m'])) {
        $this->mask       = $_GET['m'];
      }
      if (isset($_GET['t']) && $_GET['t']) {
        $this->trans = true;
      }
      if (isset($_GET['archive'])) {
        $this->archive = $_GET['archive'];
      }
      if (isset($_GET['video'])) {
        $this->video    = $_GET['video'];
      }
      if (isset($_GET['id'])) {
        $this->id       = $_GET['id'];
        $this->getCacheName();
      }

      if (isset($_GET['file']) && isset($_GET['album'])) {
        $this->file     = $_GET['file'];
        $this->album    = $_GET['album'];
      }

      if (!$this->cached && ( ($this->album && $this->file) || $this->id) && !$this->archive) {
        $this->getInfo();
      } else
      if (!$this->cached && !$this->archive && !$this->video) {
        $this->error = true;
      }
    }
  }

  public function getCacheName()
  {
    if($this->archive) {
      $this->cache_file = 'a'.$this->id.'_'.$_GET['version'].'.'.$this->archive;
    } else
    if($this->video) {
      $this->cache_file = 'v'.$this->id.'.'.$this->video;
    } else {
      $this->cache_file = 'i'.$this->id;
      if ($this->width) {
        $this->cache_file .= '_w'.$this->width;
      }
      if ($this->height) {
        $this->cache_file .= '_h'.$this->height;
      }
      if ($this->mask) {
        $this->cache_file .= '_m'.$this->mask;
      }

      $this->cache_file .= '_tpl'.$this->session->getData('template');
      $this->cache_file .= '_thm'.$this->session->getData('theme');
      if ($this->trans) {
        $this->cache_file .= '.png';
      } else {
        $this->cache_file .= '.jpg';
      }
    }

    if (file_exists($this->cache_path.$this->cache_file)) {
      $this->cached = true;
    }
  }

  function getInfo()
  {
    $db    = db::getInstance();
    $image = false;
    $sql = '
        SELECT I.id as id,I.name as name,I.aid as album,A.path as path
        FROM {pre}image I
          LEFT JOIN {pre}album A ON A.id=I.aid
    ';
    if ($this->id) {
      $image = $db->select($sql.'
        WHERE I.id = '.$this->id.'
      ');
    } elseif ($this->album && $this->file) {
      $image = $db->select($sql.'
        WHERE I.album = '.$this->album.'
          AND I.name  = '.$this->file.'
      ');
    }

    if ($db->error || !$image) {
      $this->error = true;
    } else {
      $this->data = $image[0];
      if(!file_exists($this->path.$this->data['path'].'/'.$this->data['name'])){
        $this->error = true;
      } else {
        $this->size = filesize($this->path.$this->data['path'].'/'.$this->data['name']);
      }
    }
  }

  function display()
  {
    if (!$this->cached && !$this->error) {
      $this->generate();
    }

    if($this->error) {
      echo 'use error image';
    } elseif ($this->unknown) {
      echo 'use unknown image';
    } else {
      header('location: cache/'.$this->cache_file);
    }
  }

  public function generate()
  {
    $this->getCacheName();
    $this->getMimeType();

    if ($this->archive) {
      $this->generateArchive();
    } else
    if ($this->video) {
      $this->generateVideo();
    } else
    if (!$this->mime) {
      $this->unknown = true;
    } else
    if (substr($this->mime,0,strpos($this->mime,'/')) == 'image') {
      $this->generateGD();
    } elseif (substr($this->mime,0,strpos($this->mime,'/')) == 'video') {
      $this->generateFFMPEG();
    } elseif (substr($this->mime,0,strpos($this->mime,'/')) == 'sound') {
      $this->unknown = true;
    } elseif (substr($this->mime,0,strpos($this->mime,'/')) == 'application') {
      $this->unknown = true;
    } else {
      $this->unknown = true;
    }
  }

  public function getMimeType()
  {
    $mime      = parse_ini_file("include/mime.ini");
    $extension = strtolower(substr($this->data['name'], strrpos($this->data['name'], ".")+1));

    if (array_key_exists($extension, $mime)) {
      $type = $mime[$extension];
    } else {
      $type = null;
    }

    $this->mime = $type;
  }


  public function generateGD()
  {
    $ext = substr($this->mime,strpos($this->mime,'/')+1,strlen($this->mime));
    if ($ext=='bmp') $ext = 'wbmp';
    $function = 'imagecreatefrom'.$ext;
    if (!function_exists($function)) {
      $this->unknown = true;
      return false;
    }
    $gd = $function($this->path.$this->data['path'].'/'.$this->data['name']);
    if (!$gd) {
      $this->unknown = true;
      return false;
    }

    $this->info = array(
      'width'     => imagesx($gd),
      'height'    => imagesy($gd),
      'duration'  => 0,
      'mime'      => $this->mime,
      'size'      => filesize($this->path.$this->data['path'].'/'.$this->data['name']),
    );

    $this->generateGDimage($gd,'image');
    $this->updateDB();
  }

  public function generateFFMPEG()
  {
    $ff = new ffmpeg_movie($this->path.$this->data['path'].'/'.$this->data['name']);
    if (!$ff) {
      $this->unknown = true;
      return false;
    }

    $this->info = array(
      'width'     => $ff->getFrameWidth(),
      'height'    => $ff->getFrameHeight(),
      'duration'  => $ff->getDuration(),
      'mime'      => $this->mime,
      'size'      => filesize($this->path.$this->data['path'].'/'.$this->data['name']),
    );

    if ($ff->getFrameCount()>20){
      $frame = $frame = $ff->getFrame(20);
    } else {
      $frame = $frame = $ff->getFrame($ff->getFrameCount()-1);
    }

    $gd = $frame->toGDImage();
    $this->generateGDimage($gd,'video');
    $this->updateDB();
  }

  public function generateGDimage($img,$type)
  {
    $img = $this->getResized($img);

    if ($this->mask) {
      $img = $this->getMask($img);
    }
    if ($type=='video') {
      $img = $this->getVideoMask($img);
    }

    if ($this->trans) {
      imagepng($img,$this->cache_path.$this->cache_file);
    } else {
      imagejpeg($img,$this->cache_path.$this->cache_file,100);
    }
    imagedestroy($img);
  }

  function updateDB()
  {
    $db = db::getInstance();
    $result = $db->query('
      UPDATE {pre}image
      SET width     = '.$this->info['width'].',
          height    = '.$this->info['height'].',
          duration  = '.$this->info['duration'].',
          mime      = "'.$this->info['mime'].'",
          size      = '.$this->info['size'].'
      WHERE id='.$this->id.'
    ');

  }

  function getResized($img)
  {
    $current_width  = imagesx($img);
    $current_height = imagesy($img);

    if ($this->width && !$this->height) {
      $this->height = $this->width / $current_width * $current_height;
    }
    if (!$this->width && $this->height) {
      $this->width = $this->height / $current_height * $current_width;
    }

    if ($current_height / $current_width * $this->width >= $this->height) {
      $old_width  = $current_width;
      $old_height = round($current_height*($current_width/$current_height)/($this->width/$this->height));
      $old_x      = 0;
      $old_y      = round(($current_height - $old_height) / 2);
    } else
    if ($current_width/$current_height*$this->height >= $this->width) {
      $old_width  = round($current_width*($current_height/$current_width)/($this->height/$this->width));
      $old_height = $current_height;
      $old_x      = round(($current_width - $old_width) / 2);
      $old_y      = 0;
    } else {
      $this->error = true;
      return false;
    }

    $new = imagecreatetruecolor($this->width,$this->height);
    imagecopyresampled($new,$img,0,0,$old_x,$old_y,$this->width,$this->height,$old_width,$old_height);
    imagedestroy($img);

    return $new;
  }

  function getVideoMask($img)
  {
    if (is_dir(LOCAL_PATH.LOCAL_DIR.'template/'.$this->session->getData('template').'/mask/')) {
      $path = LOCAL_PATH.LOCAL_DIR.'template/'.$this->session->getData('template').'/mask/';
    } else
    if (is_dir(LOCAL_PATH.LOCAL_DIR.'theme/'.$this->session->getData('theme').'/mask/')) {
      $path = LOCAL_PATH.LOCAL_DIR.'theme/'.$this->session->getData('theme').'/mask/';
    }

    $ext = substr($this->mime,strpos($this->mime,'/')+1,strlen($this->mime));

    switch ($ext) {
      case 'quicktime'  :
        $mask = imagecreatefrompng($path.'quicktime.png');
        break;
      case 'divx' :
        $mask = imagecreatefrompng($path.'divx.png');
        break;
      case 'vnd.rn-realvideo' :
        $mask = imagecreatefrompng($path.'real.png');
        break;
      default:
        $mask = imagecreatefrompng($path.'video.png');
    }

    if (!$mask) {
      return false;
    }

    imagealphablending($img,true);
    imageSaveAlpha($img,true);

    imagealphablending($mask,true);
    imageSaveAlpha($mask,true);

    imagecopy($img,$mask,($this->width/2)-(imagesx($mask)/2),($this->height/2)-(imagesy($mask)/2),0,0,imagesx($mask),imagesy($mask));

    imagedestroy($mask);
    return $img;
  }

  function getMask($img)
  {
    if (is_dir(LOCAL_PATH.LOCAL_DIR.'template/'.$this->session->getData('template').'/mask/')) {
      $path = LOCAL_PATH.LOCAL_DIR.'template/'.$this->session->getData('template').'/mask/';
    } else
    if (is_dir(LOCAL_PATH.LOCAL_DIR.'theme/'.$this->session->getData('theme').'/mask/')) {
      $path = LOCAL_PATH.LOCAL_DIR.'theme/'.$this->session->getData('theme').'/mask/';
    }

    $ext = substr($this->mime,strpos($this->mime,'/')+1,strlen($this->mime));
    $mask = imagecreatefrompng($path.'mask_'.$this->mask.'.png');
    if (imagesx($mask)!=$this->width || imagesy($mask)!=$this->height) {
      $mask2 = imagecreatetruecolor($this->width,$this->height);
      imagealphablending($mask2 ,false);
      $col = imagecolorallocatealpha($mask2 ,0,0,0,127);
      imagefilledrectangle($mask2 ,0,0,$this->width,$this->height,$col);
      imagealphablending($mask2 ,true);
      imagecopyresampled($mask2,$mask,0,0,0,0,$this->width,$this->height,imagesx($mask),imagesy($mask));
      $mask = $mask2;
    }
    if (!$mask) {
      return false;
    }

    imagealphablending($img,true);
    imageSaveAlpha($img,true);

    imagealphablending($mask,true);
    imageSaveAlpha($mask,true);

    imagecopy($img,$mask,0,0,0,0,imagesx($mask),imagesy($mask));

    if ($this->trans) {
      $img = $this->getMaskAlpha($img);
    }

    imagedestroy($mask);
    return $img;
  }

  public function getMaskAlpha($img)
  {
    if (is_dir(LOCAL_PATH.LOCAL_DIR.'template/'.$this->session->getData('template').'/mask/')) {
      $path = LOCAL_PATH.LOCAL_DIR.'template/'.$this->session->getData('template').'/mask/';
    } else
    if (is_dir(LOCAL_PATH.LOCAL_DIR.'theme/'.$this->session->getData('theme').'/mask/')) {
      $path = LOCAL_PATH.LOCAL_DIR.'theme/'.$this->session->getData('theme').'/mask/';
    }

    $ext = substr($this->mime,strpos($this->mime,'/')+1,strlen($this->mime));
    $mask = imagecreatefrompng($path.'alpha_'.$this->mask.'.png');
    if (imagesx($mask)!=$this->width || imagesy($mask)!=$this->height) {
      $mask2 = imagecreatetruecolor($this->width,$this->height);
      imagecopyresampled($mask2,$mask,0,0,0,0,$this->width,$this->height,imagesx($mask),imagesy($mask));
      $mask = $mask2;
    }

    $new  = imagecreatetruecolor($this->width,$this->height);
    imagealphablending($new,false);
    $col = imagecolorallocatealpha($new,0,0,0,127);
    imagefilledrectangle($new,0,0,$this->width,$this->height,$col);
    imagealphablending($new,true);

    for ($x=0;$x<$this->width;$x++) {
      for ($y=0;$y<$this->height;$y++) {
        $alpha = imagecolorat($mask,$x,$y);
        $alpha = floor( ( 255 - ($alpha >> 16) & 0xFF )/2);
        if($alpha>127) $alpha = 127;
        $color = imagecolorat($img ,$x,$y);
        $color = array(
          'r' => ($color >> 16) & 0xFF,
          'g' => ($color >> 8)  & 0xFF,
          'b' => $color & 0xFF,
        );

        $cnew = imagecolorallocatealpha($img,$color['r'],$color['g'],$color['b'],$alpha);
        imagesetpixel($new,$x,$y,$cnew);
      }
    }

    imagealphablending($new,false);
    imagesavealpha($new,true);
    return $new;
  }

  public function generateArchive()
  {
    $db = db::getInstance();
    $result = $db->select('
      SELECT *
      FROM {pre}album
      WHERE id='.$this->id.'
    ');
    $this->info = $result[0];

    $result = false;
    if($this->archive=='zip') {
      $result = $this->generateArchiveZip();
    } else
    if($this->archive=='rar') {

    } else
    if($this->archive=='tar.gz') {

    } else
    if($this->archive=='tar.bz') {

    }

    if ($result) {
      $archive = array();
      if ($this->info['archive']) {
        $archive = unserialize(base64_decode($this->info['archive']));
      }
      $archive[$this->archive] = $result;
      $archive = base64_encode(serialize($archive));

      $db->query('
        UPDATE {pre}album
        SET archive = "'.$archive.'"
        WHERE id = '.$this->id.'
      ');
    }
  }

  public function generateArchiveZip()
  {
    ini_set('max_execution_time',10*60);
    $path  = LOCAL_PATH.LOCAL_DIR.'data/'.$this->info['path'].'/';
    if (is_dir($path)) {
      $list = scandir($path);

      $zip = new ZipArchive();
      if ($zip->open($this->cache_path.$this->cache_file,ZIPARCHIVE::CREATE)!==true) {
        return false;
      }

      $mime      = parse_ini_file("include/mime.ini");
      foreach($list as $file) {
        if (substr($file,0,1) != '.') {
          $ext = strtolower(substr($file, strrpos($file, ".")+1));

          if (array_key_exists($ext, $mime)) {
            $type = explode('/',$mime[$ext]);
            if ($type[0] == 'image') {
              $zip->addFile(LOCAL_PATH.LOCAL_DIR.'data/'.$this->info['path'].'/'.$file,$file);
            }
          } else {
            $type = null;
          }

        }
      }
      $zip->close();
      $size = filesize($this->cache_path.$this->cache_file);
    } else {
      return false;
    }
    ini_restore('max_execution_time');

    return $size;
  }

  public function generateVideo()
  {
    ini_set('max_execution_time',10*60);
    $ff = new ffmpeg_movie($this->path.$this->data['path'].'/'.$this->data['name']);
    if (!$ff) {
      $this->unknown = true;
      return false;
    }

    $source  = $this->path.$this->data['path'].'/'.$this->data['name'];
    $source  = str_replace(' ','\ ',$source);
    $source  = str_replace('(','\(',$source);
    $source  = str_replace(')','\)',$source);
    $cible   = $this->cache_path.$this->cache_file;
    $command = 'ffmpeg -y -i '.$source.' -acodec mp3 -b 200 -ar 22050 -f '.$this->video.' '.$cible;
 //   $command = 'ffmpeg -i '.$source.' -s '.$ff->getFrameWidth().'x'.$ff->getFrameHeight().' -acodec mp3 -ab 56 -r 12 -ac 1 -b 200 -ar 22050 -f '.$this->video.' '.$cible;
    passthru($command,$res);
    if($res) {
//      echo '<br>'.$source;
//      echo '<br>'.$cible;
//      echo '<br>'.$command.'<br>';
//      var_dump($res);
      exit;
    }
//    echo '<br>'.$command.'<br>';
    ini_restore('max_execution_time');
  }


  function __destruct()
  {
  }

}

?>
