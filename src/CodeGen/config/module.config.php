<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'CodeGen\Controller\GenerateClass' => 'CodeGen\Controller\GenerateClassController',
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'class-gen' => array(
                    'options' => array(
                        'route' => 'class [--path=] [--from-json=] [--replace=] [--replace-with=]',
                        'defaults' => array(
                            '__NAMESPACE__' => 'CodeGen\Controller',
                            'controller' => 'CodeGen\Controller\GenerateClass',
                            'action' => 'index',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
