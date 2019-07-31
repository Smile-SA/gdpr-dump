<?php
// phpcs:ignoreFile
use Composer\Autoload\ClassLoader;

// Define the root directory of the application
define('APP_ROOT', dirname(__DIR__));

/** @var ClassLoader $autoloader */
$autoloader = require APP_ROOT . '/vendor/autoload.php';
$autoloader->addPsr4('Smile\GdprDump\Tests\\', __DIR__);
