<?php

$basePath = dirname(__DIR__);

$autoload = null;
foreach ([$basePath . '/vendor/autoload.php', $basePath . '/../../autoload.php'] as $file) {
    if (file_exists($file)) {
        $autoload = require $file;
        break;
    }
}

if (!$autoload) {
    die(
        'You need to set up the project dependencies using the following commands:' . PHP_EOL .
        'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL
    );
}

return $autoload;
