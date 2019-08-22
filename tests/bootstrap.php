<?php
// phpcs:ignoreFile
use Composer\Autoload\ClassLoader;

/** @var ClassLoader $autoloader */
$autoloader = require dirname(__DIR__) . '/app/bootstrap.php';
$autoloader->addPsr4('Smile\GdprDump\Tests\\Framework\\', __DIR__ . '/framework');
$autoloader->addPsr4('Smile\GdprDump\Tests\\Functional\\', __DIR__ . '/functional');
$autoloader->addPsr4('Smile\GdprDump\Tests\\Unit\\', __DIR__ . '/unit');
