<?php
return array(
    'modules' => array(
        'Application',
    	'Album',
    	'DoctrineModule',
    	'DoctrineORMModule',
    	'ZendDeveloperTools',
    	'ZfcBase',
    	'ZfcUser',
    	'ZfcUserDoctrineORM',
    	'BjyProfiler',
    	'FileBank',
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            'config/autoload/{,*.}{global,local}.php',
        ),
        'module_paths' => array(
            './module',
            './vendor',
        ),
    ),
);
