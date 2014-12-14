<?php

namespace CodeGen\Controller;

use CodeGen\Service\ClassInput;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Console;

class GenerateClassController extends AbstractActionController
{
    public function indexAction()
    {
    	$path = $this->getRequest()->getParam('path');

        $class = ClassInput::init();
        $source = $class->generate();
        $filename = $class->getClassGenerator()->getName().'.php';

        if ($path) {
        	$savePath = $path;
        	if (strtolower(substr($savePath, -4)) != '.php') {
        		$savePath .= DIRECTORY_SEPARATOR . $filename;
        	}
        }
        else {
        	$savePath = getcwd(). DIRECTORY_SEPARATOR . $filename;
        }

        Console::getInstance()->write(ClassInput::geTheading(array('FILE GENERATED AT: ', $savePath)));
        file_put_contents($savePath, $source);
    }
}
