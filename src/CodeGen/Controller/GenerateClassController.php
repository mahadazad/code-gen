<?php

namespace CodeGen\Controller;

use CodeGen\Service\ClassBuilder;
use CodeGen\Loader\ConsoleLoader;
use CodeGen\Loader\JsonLoader;
use CodeGen\Loader\ReplaceLoader;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Console;

class GenerateClassController extends AbstractActionController
{
    public function indexAction()
    {
        $path = $this->getRequest()->getParam('path');
        $fromJson = $this->getRequest()->getParam('from-json');
        $replace = $this->getRequest()->getParam('replace');
        $replaceWith = $this->getRequest()->getParam('replace-with');

        $savedFiles = array('FILE GENERATED AT: ');

        if ($fromJson) {
            $loader = new JsonLoader($fromJson);
            if ($replace && $replaceWith) {
                $loader = new ReplaceLoader($loader, $replace, $replaceWith);
            }
        } else {
            $loader = new ConsoleLoader();
        }

        $classes = $loader->load();

        if (is_array($classes)) {
            foreach ($classes as $class) {
                $savedFiles[] = $this->saveFile($class, $path);
            }
        } else {
            $savedFiles[] = $this->saveFile($classes, $path);
        }

        Console::getInstance()->write(ConsoleLoader::getHeading($savedFiles));
    }

    /**
     * @param ClassBuilder $class
     * @param string       $basePath
     */
    protected function saveFile(ClassBuilder $class, $basePath = '')
    {
        $source = '<'.'?php'.PHP_EOL.$class->generate();
        $savePath = $this->getSavePath($class, $basePath);
        $path = dirname($savePath);
        @mkdir($path, 0777, true);
        file_put_contents($savePath, $source);

        return $savePath;
    }

    /**
     * @param ClassBuilder $class
     * @param string       $basePath
     */
    protected function getSavePath(ClassBuilder $class, $basePath = '')
    {
        $classSavePath = $class->getSavePath();

        $savePath = '';
        if (!empty($basePath) && !empty($classSavePath)) {
            $savePath = $basePath.DIRECTORY_SEPARATOR.$classSavePath.DIRECTORY_SEPARATOR;
        } elseif (!empty($classSavePath)) {
            $savePath = $classSavePath.DIRECTORY_SEPARATOR;
        } elseif (!empty($basePath)) {
            $savePath = rtrim($basePath, '\/').DIRECTORY_SEPARATOR;
        }

        if (substr(strtolower(rtrim($savePath, DIRECTORY_SEPARATOR)), -4) !== '.php') {
            $savePath .= $class->getClassGenerator()->getName().'.php';
        }

        return $savePath;
    }
}
