<?php

namespace CodeGen;

use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface;

class Module implements ConsoleUsageProviderInterface
{
    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__.'/src/'.__NAMESPACE__,
                ),
            ),
        );
    }

    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
            // Describe available commands
            'class  [--path=path_to_save_at] [--from-json  [--replace] [--replace-with]]'    => 'Generates class',
            array( '--path',         '(optional) path to where the file is saved'),
            array( '--from-json',    '(optional) loads config from json'),
            array( '--replace',      '(optional) search a string in config'),
            array( '--replace-with', '(optional) replace the searched string with the given string'),

    );
    }
}
