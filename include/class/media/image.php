<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: archive.php 58 2007-02-08 16:19:27Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

require_once('include/class/album.php');

class media_image extends media
{
  private $imageinfo;
  private $gdtype = array(
    'image/gif'  => 'gif',
    'image/jpeg' => 'jpeg',
    'image/png'  => 'png',
    'video/jpeg' => 'jpeg',
  );
  protected function initialize()
  {
    $this->album = album::getInstance();

    if ($this->data['id'] == '-1') {
      $this->imageinfo = array(
        'name'  => 'album.png',
      );
      $session = session::getInstance();
      $this->pathname  = LOCAL_PATH.LOCAL_DIR.'theme/'.$session->getData('theme').'/image/';
    } else {
      $this->imageinfo  = $this->album->getMediaInfo($this->data['id']);
      $this->pathname  = LOCAL_PATH.LOCAL_DIR.'data/'.$this->imageinfo['path'].'/';
    }
    if (!$this->imageinfo) {
      $this->error('4','album inexistant');
    }

    $this->width      = isset($this->data['w']) ? $this->data['w'] : false;
    $this->height     = isset($this->data['h']) ? $this->data['h'] : false;
    $this->mask       = isset($this->data['m']) ? $this->data['m'] : false;
    $this->trans      = isset($this->data['t']) ? $this->data['t'] : false;

    $this->pathcache = LOCAL_PATH.LOCAL_DIR.'cache/';

    $this->filecache = $this->getCacheName();
    $this->filename  = $this->imageinfo['name'];

    $mime = parse_ini_file('include/mime.ini');
    $this->mime = $mime[strtolower(substr($this->imageinfo['name'],strrpos($this->imageinfo['name'],'.')+1,strlen($this->imageinfo['name'])))];

    if (!$this->mime) return false;  
    $this->type = explode('/',$this->mime);
  }

  private function getCacheName()
  {
    $session = session::getInstance();

    $result = 'i'.$this->data['id'];
    if ($this->width) {
      $result .= '_w'.$this->width;
    }
    if ($this->height) {
      $result .= '_h'.$this->height;
    }
    if ($this->mask) {
      $result .= '_m'.$this->mask;
      $result .= '_tpl'.$session->getData('template');
      $result .= '_thm'.$session->getData('theme');
      if ($this->trans) {
        $result .= '.png';
      } else {
        $result .= '.jpg';
      }
    } else {
      $result .= '.jpg';
    }

    return $result;
  }
  private function getBaseName()
  {
    $result = 'i'.$this->data['id'];
    $result .= '.jpg';

    return $result;
  }

  protected function generate()
  {
    if (file_exists($this->pathcache.$this->filecache)) {
      return true;
    }

    if ($this->filecache == $this->getBaseName()) {
      $this->pathcache  = $this->pathname;
      $this->filecache  = $this->filename;
      return true;
    }

    if (!isset($this->gdtype[$this->mime])) {
      if ($this->type[0] == 'image' && function_exists('NewMagickWand')) {
        $this->generateMW();
      } else
      if ($this->type[0] == 'video' && class_exists('ffmpeg_movie')) {
        $this->generateFFMPEG();
      }
    }

    $img = $this->getGD();
    if (!$img) {
      $this->error('7','Type d\'image incorrecte ou inexistante');
    }

    if (!isset($this->info) || is_null($this->info) || count($this->info)<1) {
      $this->info = array(
        'width'     => imagesx($img),
        'height'    => imagesy($img),
        'duration'  => 0,
        'mime'      => $this->mime,
        'size'      => filesize($this->pathname.$this->filename),
      );
    }

    $img = $this->resize($img); 
    if ($this->type[0]=='video') {
      $img = $this->getVideoMask($img);
    }
    if ($this->mask) {
      $img = $this->getMask($img);
      if ($this->trans) {
        $img = $this->getMaskAlpha($img);
      }
    }

    if ($this->trans) {
      imagepng($img,$this->pathcache.$this->filecache);
    } else {
      imagejpeg($img,$this->pathcache.$this->filecache,100);
    }

    $this->updateDB();
  }

  protected function generateMW()
  {
    if (file_exits($this->pathcache.$this->getBaseName())) {
      return true;
    }

    $img = NewMagickWand();
    MagickReadImage($img,$this->pathname.$this->filename);

    $this->info = array(
      'width'     => MagickGetImageWidth($img),
      'height'    => MagickGetImageHeight($img),
      'duration'  => 0,
      'mime'      => $this->mime,
      'size'      => filesize($this->pathname.$this->filename),
    );

    MagickSetImageFormat($img,'JPEG');
    MagickWriteImage($img,$this->pathcache.$this->getBaseName());

    $this->mime     = 'image/jpeg';
    $this->type     = array('image','jpeg');
    $this->pathname = $this->pathcache;
    $this->filename = $this->getBaseName();
    return true;
  }

  public function generateFFMPEG()
  {
    $ff = new ffmpeg_movie($this->pathname.$this->filename);
    if (!$ff) {
      $this->unknown = true;
      return false;
    }

    $this->info = array(
      'width'     => $ff->getFrameWidth(),
      'height'    => $ff->getFrameHeight(),
      'duration'  => $ff->getDuration(),
      'mime'      => $this->mime,
      'size'      => filesize($this->pathname.$this->filename),
    );

    if ($ff->getFrameCount()>20){
      $frame = $frame = $ff->getFrame(20);
    } else {
      $frame = $frame = $ff->getFrame($ff->getFrameCount()-1);
    }

    $gd = $frame->toGDImage();
    imagejpeg($gd,$this->pathcache.$this->getBaseName());

    $this->mime     = 'image/jpeg';
    $this->type     = array('video','jpeg');
    $this->pathname = $this->pathcache;
    $this->filename = $this->getBaseName();
  }

  protected function getGD()
  {
    if (!isset($this->gdtype[$this->mime])) {
      $this->error('8','Format non gerer');
    }
    $function = 'imagecreatefrom'.$this->gdtype[$this->mime];
    return $function($this->pathname.$this->filename);
  }

  protected function resize($img)
  {
    $currentWidth  = $this->info['width'];
    $currentHeight = $this->info['height'];

    if ($this->width && !$this->height) {
      $this->height = $this->width / $currentWidth * $currentHeight;
    }
    if (!$this->width && $this->height) {
      $this->width = $this->height / $currentHeight * $currentWidth;
    }

    if ($currentHeight / $currentWidth * $this->width >= $this->height) {
      $width  = $currentWidth;
      $height = round($currentHeight*($currentWidth/$currentHeight)/($this->width/$this->height));
      $x      = 0;
      $y      = round(($currentHeight - $height) / 2);
    } else
    if ($currentWidth/$currentHeight*$this->height >= $this->width) {
      $width  = round($currentWidth*($currentHeight/$currentWidth)/($this->height/$this->width));
      $height = $currentHeight;
      $y      = 0;
      $x      = round(($currentWidth - $width) / 2);
    } else {
      $width  = $currentWidth;
      $height = $currentHeight;
      $x      = 0;
      $y      = 0;
    }

    $new = imagecreatetruecolor($this->width,$this->height);
    imagecopyresampled($new,$img,0,0,$x,$y,$this->width,$this->height,$width,$height);
    imagedestroy($img);
    return $new;
  }

  function getVideoMask($img)
  {
    $session = session::getInstance();
    if (is_dir(LOCAL_PATH.LOCAL_DIR.'template/'.$session->getData('template').'/mask/')) {
      $path = LOCAL_PATH.LOCAL_DIR.'template/'.$session->getData('template').'/mask/';
    } else
    if (is_dir(LOCAL_PATH.LOCAL_DIR.'theme/'.$session->getData('theme').'/mask/')) {
      $path = LOCAL_PATH.LOCAL_DIR.'theme/'.$session->getData('theme').'/mask/';
    }

    switch ($this->info['mime']) {
      case 'video/quicktime'  :
        if(file_exists($path.'quicktime.png'))
          $mask = imagecreatefrompng($path.'quicktime.png');
        break;
      case 'video/divx' :
        if(file_exists($path.'divx.png'))
          $mask = imagecreatefrompng($path.'divx.png');
        break;
      case 'video/vnd.rn-realvideo' :
        if(file_exists($path.'real.png'))
          $mask = imagecreatefrompng($path.'real.png');
        break;
      default:
        if(file_exists($path.'video.png'))
          $mask = imagecreatefrompng($path.'video.png');
    }

    if (!$mask) {
      return $img;
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
    $session = session::getInstance();
    if (is_dir(LOCAL_PATH.LOCAL_DIR.'template/'.$session->getData('template').'/mask/')) {
      $path = LOCAL_PATH.LOCAL_DIR.'template/'.$session->getData('template').'/mask/';
    } else
    if (is_dir(LOCAL_PATH.LOCAL_DIR.'theme/'.$session->getData('theme').'/mask/')) {
      $path = LOCAL_PATH.LOCAL_DIR.'theme/'.$session->getData('theme').'/mask/';
    }
    if (!file_exists($path.'mask_'.$this->mask.'.png')) {
      return $img;
    }
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
      return $img;
    }

    imagealphablending($img,true);
    imageSaveAlpha($img,true);

    imagealphablending($mask,true);
    imageSaveAlpha($mask,true);

    imagecopy($img,$mask,0,0,0,0,imagesx($mask),imagesy($mask));

    imagedestroy($mask);
    return $img;
  }

  public function getMaskAlpha($img)
  {
    $session = session::getInstance();
    if (is_dir(LOCAL_PATH.LOCAL_DIR.'template/'.$session->getData('template').'/mask/')) {
      $path = LOCAL_PATH.LOCAL_DIR.'template/'.$session->getData('template').'/mask/';
    } else
    if (is_dir(LOCAL_PATH.LOCAL_DIR.'theme/'.$session->getData('theme').'/mask/')) {
      $path = LOCAL_PATH.LOCAL_DIR.'theme/'.$session->getData('theme').'/mask/';
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
      WHERE id='.$this->data['id'].'
    ');

  }

  protected function retrieve()
  {
    if ($this->trans) {
      $this->mime = 'image/png';
    } else {
      $this->mime = 'image/jpeg';
    }
    $handle = fopen($this->pathcache.$this->filecache,'r');
    $this->filesize = filesize($this->pathcache.$this->filecache);
    $this->flux = fread($handle,$this->filesize);
  }


}

?>
