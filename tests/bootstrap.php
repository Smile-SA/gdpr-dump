<?php
// Define the root directory of the application
define('APP_ROOT', dirname(__DIR__));

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require APP_ROOT . '/vendor/autoload.php';
$autoloader->addPsr4('Smile\GdprDump\Tests\\', __DIR__);
