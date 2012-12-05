<?php

namespace FileBank;

return array(
    __NAMESPACE__ => array(
        'params' => array(
            'filebank_folder'  => '/data/filebank/', 
            'default_is_active' => true,
            'chmod'           => 0755,
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            __NAMESPACE__ . '\Controller\File' => __NAMESPACE__ . '\Controller\FileController',
        ),
    ),
    'router' => array(
        'routes' => array(
            __NAMESPACE__ => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/files',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\File',
                        'action'     => 'index',
                    ),
                ),
            ),
            __NAMESPACE__ . '-Download' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/files/:id',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\File',
                        'action'     => 'download',
                    ),
                ),
            ),
        ),
    ),
);