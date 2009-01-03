<?php

require_once('include/config.php');

//header('Location: http://'.LOCAL_URL.LOCAL_DIR.'cache/v'.$_GET['id'].'.flv'); 

require_once('include/class/db.php');
require_once('include/class/media.php');


$_GET['mode'] = 'video';
$_GET['type'] = 'flv';

$image = media::getInstance();
$image->display();
unset($image);

?>
