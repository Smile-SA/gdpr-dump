<?php
// phpcs:ignoreFile
use Composer\Autoload\ClassLoader;

// Define the root directory of the application
define('APP_ROOT', dirname(__DIR__));

/** @var ClassLoader $autoloader */
$autoloader = require APP_ROOT . '/vendor/autoload.php';
$autoloader->addPsr4('Smile\GdprDump\Tests\\Framework\\', __DIR__ . '/framework');
$autoloader->addPsr4('Smile\GdprDump\Tests\\Functional\\', __DIR__ . '/functional');
$autoloader->addPsr4('Smile\GdprDump\Tests\\Unit\\', __DIR__ . '/unit');
