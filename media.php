<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: media.php 59 2007-02-08 16:19:37Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

require_once('include/config.php');
require_once('include/class/db.php');
require_once('include/class/media.php');

$image = media::getInstance();
$image->display();
unset($image);

?>
