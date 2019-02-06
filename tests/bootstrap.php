<?php
define('SRC_ROOT', dirname(__DIR__));

require SRC_ROOT . '/Piko.php';
$autoloader = require(SRC_ROOT . '/vendor/autoload.php');
// $autoloader->addPsr4('piko\\', [SRC_ROOT]);
$autoloader->addPsr4('tests\\', [__DIR__]);
