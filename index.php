#!/usr/bin/env php
<?php
$basePath = getcwd();

// load autoloader
if (\Phar::running()) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists("$basePath/vendor/autoload.php")) {
    require_once "$basePath/vendor/autoload.php";
} elseif (file_exists("$basePath/init_autoload.php")) {
    require_once "$basePath/init_autoload.php";
} elseif (\Phar::running()) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    echo 'Error: I cannot find the autoloader of the application.' . PHP_EOL;
    echo "Check if $basePath contains a valid ZF2 application." . PHP_EOL;
    exit(2);
}

$appConfig = array(
    'modules' => array(
        'CodeGen',
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            'config/autoload/{,*.}{global,local}.php',
        ),
        'module_paths' => array(
            '.',
            './vendor',
        ),
    ),
);

Zend\Mvc\Application::init($appConfig)->run();
