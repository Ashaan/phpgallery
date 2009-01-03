<?php
/**
 * @author    Mathieu Chocat
 * @version   $Id: index.php 55 2007-02-07 13:10:01Z mathieu $
 * @copyright (c) 2006 Sygil.eu
 **/

define('EXEC_TIME',microtime(true));

require_once('include/config.php');
require_once('include/class/db.php');
require_once('include/class/engine.php');
require_once('include/class/session.php');

$engine = engine::getInstance();

$engine->initialize();
$engine->execute();
$engine->finalize();

unset($engine);


?>
