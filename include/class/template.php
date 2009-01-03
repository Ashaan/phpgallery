<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: template.php 54 2007-02-07 10:08:48Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

define('VALUE_NOT_FIND','');//UNKNOWN');

class template
{
  static  $instance = null;
  public  $config   = null;
  private $path     = null;
  private $template = array();
  private $template_name = null;
  private $theme_name = null;
  private $data     = array();

  static function getInstance()
  {
    if (is_null(template::$instance)) {
      template::$instance = new template();
    }
    return template::$instance;
  }

  function __construct()
  {
    $session = session::getInstance();
    $this->template_name = $session->getData('template');
    $this->theme_name    = $session->getData('theme');

    $this->path   = LOCAL_PATH.LOCAL_DIR.'template/'.$this->template_name.'/';

    if (file_exists('template/'.$this->template_name.'/config.php')) {
      require_once('template/'.$this->template_name.'/config.php');
    }
    if (file_exists('theme/'.$this->theme_name.'/config.php')) {
      require_once('theme/'.$this->theme_name.'/config.php');
    }
  }

  public function add($tpls)
  {
    if(!is_array($tpls)) $tpls = array($tpls);

    foreach ($tpls as $tpl) {
      if (!isset($this->template[$tpl])) {
        $this->template[$tpl] = null;
      }
    }
  }

  public function load()
  {
    foreach ($this->template as $name => $value) {
      if (is_null($this->template[$name]) && file_exists($this->path.$name.'.tpl') && filesize($this->path.$name.'.tpl') > 0) {
        $handle = fopen($this->path.$name.'.tpl','r');
        $this->template[$name] = fread($handle,filesize($this->path.$name.'.tpl'));
        fclose($handle);
      }
    }
  }

  public function exist($name)
  {
    if (isset($this->template[$name]) && $this->template[$name] != "") {
      return true;
    } else {
      return false;
    }
  }

  public function existValue($name,$value)
  {
    if (isset($this->template[$name]) && $this->template[$name] != '') {
      if (strpos($this->template[$name],'{'.$value.'}') > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  public function setVar($name,$value = false)
  {
    if (is_array($name)) {
      foreach ($name as $n => $v) {
        $this->data[$n] = $v;
      }
    } else {
      $this->data[$name] = $value;
    }
  }
  public function getVar($name)
  {
    if (isset($this->data[$name])) {
      return $this->data[$name];
    }
    return false;
  }
  public function clrVar()
  {
    $this->data = array();
  }

  public function get($name)
  {
    if (!isset($this->template[$name])) {
      return '';
    }

    $template = $this->template[$name];

    $template = preg_replace('/<!--(.*?)-->/', "", $template);
//    $template = preg_replace('/(\s*)([^\n]*)(\s*)\n/', "\\2\n", $template);

    $template = $this->evalBlock($template);
    $template = $this->evalTemplate($template,0);
    $template = $this->evalReplace($template);

    $template = preg_replace('/(\s*)\n/', "\n", $template);
    $template = preg_replace('/(\n*)\n/', "\n", $template);

    return $template;
  }

  private function evalBlock($template)
  {
    $regexp = '/blockbegin\[([^\n]*)\](.*?)\[\1\]blockend/is';
    if(!preg_match_all($regexp, $template, $data)) {
      return $template;
    }
    foreach($data[0] as $id => $value) {
      $template = str_replace('GETBLOCK['.$data[1][$id].']',$data[2][$id],$template);
      $template = str_replace($data[0][$id],'',$template);
    }
    return $template;
  }

  private function evalTemplate($template,$level,$var = false)
  {
    if (!$var) {
      $var = $this->data;
    }

    $template = $this->evalSetVar ($template,$level,$var);
    $template = $this->evalIf     ($template,$level,$var);
    $template = $this->evalForeach($template,$level, $var);
    $template = $this->evalFor    ($template,$level, $var);

    return $template;
  }

  private function evalIf($template,$level,$var = false)
  {
    $regexp = '/if\s([^\n]*)\sthen(?:\s*)begin\['.$level.'\](.*?)\['.$level.'\]end(?(?=\selse\sbegin)\selse\sbegin\['.$level.'\](.*?)\['.$level.'\]end)/is';
    if(!preg_match_all($regexp, $template, $data)) {
      return $template;
    }

    foreach($data[0] as $id => $value) {
      $replace = '';
      if ($this->evalCondition($data[1][$id],$var)) {
        $replace  = $data[2][$id];
      } elseif (isset($data[3][$id])) {
        $replace  = $data[3][$id];
      }

      $replace = $this->evalTemplate($replace,$level+1,$var);
      $template = str_replace($data[0][$id],$replace,$template);
    }
    return $template;
  }

  private function evalForeach($template,$level,$var=false)
  {
    $regexp = '/foreach\s([^\n]*)\sdo(?:\s*)begin\['.$level.'\](.*?)\['.$level.'\]end/is';
    if(!preg_match_all($regexp, $template, $data)) {
      return $template;
    }

    foreach($data[0] as $id => $value) {
      $array = str_replace(' ','',$data[1][$id]);
      $array = str_replace('{','',$array);
      $array = str_replace('}','',$array);
      $replace  = $data[2][$id];
      $result   = '';

      if (isset($var[$array]) && is_array($var[$array])) {
        foreach ( $var[$array] as $subvar) {
          $temp   = $this->evalTemplate($replace,$level+1,$subvar);
          $result .= $this->evalReplace($temp,$subvar);
        }
      }

      $template = str_replace($data[0][$id],$result,$template);
    }

    return $template;
  }

  private function evalFor($template,$level,$var=false)
  {
    $regexp = '/for\s([^\n]*)=([^\n]*)\sto\s([^\n]*)\sdo(?:\s*)begin\['.$level.'\](.*?)\['.$level.'\]end/is';
    if(!preg_match_all($regexp, $template, $data)) {
      return $template;
    }

    foreach($data[0] as $id => $value) {
      $array = str_replace(' ','',$data[1][$id]);
      if ($array) {
        $array = str_replace('{','',$array);
        $array = str_replace('}','',$array);
      }
      $replace  = $data[4][$id];
      $result   = '';
//echo '<br>for ($i='.$this->evalReplace($data[2][$id],$var).';$i<'.$this->evalReplace($data[3][$id],$var).';$i++)';
      if (!$array || (isset($var[$array]) && is_array($var[$array]))) {
        for ($i=$this->evalReplace($data[2][$id],$var);$i<$this->evalReplace($data[3][$id],$var);$i++) {
          if ($array && !isset($var[$array][$i])) {
            break;
          }
          if ($array) {
            $subvar = $var[$array][$i];
          } else {
            $subvar = $var;
          }
          $subvar['i'] = $i;
          $temp   = $this->evalTemplate($replace,$level+1,$subvar);
          $result .= $this->evalReplace($temp,$subvar);
        }
      }

      $template = str_replace($data[0][$id],$result,$template);
    }

    return $template;
  }
  private function evalSetVar($template,$level,$var = false)
  {
    $regexp = '/setvar\['.$level.'\]\[(.*?)\,(.*?)\,(.*?)\]/i';
    if(!preg_match_all($regexp, $template, $data)) {
      return $template;
    }

    foreach($data[0] as $id => $value) {
      $replace = $this->evalReplace($data[2][$id],$var);
      if($data[3][$id] == 'M') {
        eval("\$replace = ($replace);");
      }

      $this->data[$data[1][$id]] = $replace;
      $template = str_replace($data[0][$id],'',$template);
    }
    return $template;
  }

  private function evalCondition($condition,$var = false)
  {
//echo "<br>1 : \$condition = ($condition);";
    $condition = $this->evalReplace($condition,$var);

//echo "<br>3 : \$condition = ($condition);";
    eval("\$condition = ($condition);");

    return $condition;
  }
  private function evalMath($template,$var = false) {
    $regexp = '/MATH\[([ceiroundfl0123456789\*\/\-\+\,\)\(]*)\]/';
    if(!preg_match_all($regexp, $template, $data)) {
      return $template;
    }
    foreach($data[0] as $id => $value) {
      $replace = $data[1][$id];
      eval("\$replace = ($replace);");
      $template = str_replace($data[0][$id],$replace,$template);
    }   
    return $template;
  }
  private function evalReplace($template,$var = false)
  {
    $regexp = '/\{(?(?=lang_)|(.*?)\})/i';
    if(!preg_match_all($regexp, $template, $data)) {
      return $template;
    }

    $mainVar   = false;
    if (!$var) {
      $mainVar = true;
      $var     = $this->data;
    }

    foreach($data[0] as $id => $value) {
      if ($data[1][$id]) {
        if (!isset($var[$data[1][$id]])) {
          if (!$mainVar && isset($this->data[$data[1][$id]])) {
            $var[$data[1][$id]] = $this->data[$data[1][$id]];
          } else {
            $var[$data[1][$id]] = VALUE_NOT_FIND;
          }
        } 
        if (!is_array($var[$data[1][$id]])) {
          $template = str_replace($data[0][$id],$var[$data[1][$id]],$template);
        }
      }
    }
    $template = $this->evalMath($template,$var);
    $template = $this->evalOperator($template,$var);

    return $template;
  }

  private function evalOperator($template,$var)
  {
    $regexp = '/OPERATOR\[(.*?)\]/is';
    if(!preg_match_all($regexp, $template, $data)) {
      return $template;
    }
    foreach($data[0] as $id => $value) {
      $array    = explode(',',$data[1][$id]);
      $function = $array[0];
      if (!isset($array[1])) {
        $replace = $function();
      } else
      if (!isset($array[2])) {
        $replace = $function($array[1]);
      } else
      if (!isset($array[3])) {
        $replace = $function($array[1],$array[2]);
      } else
      if (!isset($array[4])) {
        $replace = $function($array[1],$array[2],$array[3]);
      } else
      if (!isset($array[5])) {
        $replace = $function($array[1],$array[2],$array[3],$array[4]);
      } else
      if (!isset($array[6])) {
        $replace = $function($array[1],$array[2],$array[3],$array[4],$array[5]);
      } else
      if (!isset($array[7])) {
        $replace = $function($array[1],$array[2],$array[3],$array[4],$array[5],$array[6]);
      }
      $template = str_replace($data[0][$id],$replace,$template);
    }
    return $template;
  }

  public function getTheme()
  {
    $path  = LOCAL_PATH.LOCAL_DIR.'theme/';
    if (!is_dir($path)) {
      $this->setVar('themeList', array());
      return false;
    }    

    $theme = array();
    foreach (scandir($path) as $value) {
      if (substr($value,0,1) != '.') {
        $theme[] = array('name' => $value);
      }
    }
    
    $this->setVar('themeList'   , $theme);
    $this->setVar('themePath'   , LOCAL_DIR.'theme/');
    $this->setVar('themeCurrent', $this->theme_name);

    return true;
  }








  public function getVideoCache($id)
  {
    $file = 'v'.$id.'.flv';
    if (file_exists(LOCAL_PATH.LOCAL_DIR.'cache/'.$file)) {
      return 'cache/'.$file;
    }
    return 'image.php?id='.$id.'&video=flv';
  }


  public function updateLang($html)
  {
    $session = session::getInstance();
    if (file_exists('lang/'.$session->getData('lang').'.lang.php')) {
      include('lang/'.$session->getData('lang').'.lang.php');
    }
    if (isset($lang) && is_array($lang)) {
      foreach ($lang as $name => $value) {
        $html = str_replace('{lang_'.$name.'}',$value,$html);
      }
    }
    return $html;
  }
}

?>
