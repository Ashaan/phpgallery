<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: image.php 55 2007-02-07 13:10:01Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

require_once('include/config.php');
require_once('include/class/db.php');
require_once('include/class/image.php');

$image = new image();
$image->display();
unset($image);

?>
