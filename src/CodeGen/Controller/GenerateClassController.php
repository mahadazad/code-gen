<?php

namespace CodeGen\Controller;

use CodeGen\Service\ClassInput;
use Zend\Mvc\Controller\AbstractActionController;


class GenerateClassController extends AbstractActionController
{
    public function indexAction()
    {
        $class = ClassInput::init();
        echo $class->generate();
    }
}
