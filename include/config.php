<?php
/**
 * @author    Mathieu Chocat
 * @name      $HeadURL http://svn.sygil.eu/gallery/index.php $
 * @version   $Id: config.php 48 2007-02-07 08:37:09Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

ini_set('error_reporting',E_ALL);
ini_set('display_error',1);


define('DATABASE_HOST','localhost');
define('DATABASE_NAME','dev_gallery');
define('DATABASE_USER','dev');
define('DATABASE_PASS','dev');
define('DATABASE_PRE' ,'');
define('DATABASE_TYPE','mysqli');

define('COOKIE_DOMAIN', '');
define('COOKIE_PATH'  , '');
define('COOKIE_EXPIRE', 3600);
define('COOKIE_PRE'   , '');

// Gestion de l'authent sur une autre DB
define('AUTHENT_COUNT'   ,1);
define('USERDB0_HOST','localhost');
define('USERDB0_NAME','dubois_forum');
define('USERDB0_USER','mathieu');
define('USERDB0_PASS','zm3i7Da5');
define('USERDB0_TYPE','mysqli');
define('USERDB0_TABLE_USER','o2_members');
define('USERDB0_FIELD_NAME','username');
define('USERDB0_FIELD_PASS','password');
define('USERCOOKIE0_DOMAIN','');
define('USERCOOKIE0_PATH'  ,'');
define('USERCOOKIE0_PRE'   ,'o2');


define('LOCAL_URL'  , $_SERVER['SERVER_NAME']);
define('LOCAL_PATH' , $_SERVER['DOCUMENT_ROOT']);
define('LOCAL_DIR'  , substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'/')+1));
define('LOCAL_PROTO', 'http');

define('APP_NAME'   , 'ZenGallery');
define('APP_VERSION', '0.1');
define('APP_BUILD'  , '0');
define('APP_AUTHOR' , '<a href="http://www.sygil.eu/">Sygil</a>');


?>
