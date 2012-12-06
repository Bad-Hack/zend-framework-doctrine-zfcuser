<?php
return array (
		'modules' => array (
				'Application',
				'DoctrineModule',
				'DoctrineORMModule',
				'BjyProfiler',
				'ZendDeveloperTools',
				'ZfcBase',
				'ZfcUser',
				'ZfcUserDoctrineORM',
				'FileBank',
				'Album',
				'ModuleManager' 
		),
		'module_listener_options' => array (
				'config_glob_paths' => array (
						'config/autoload/{,*.}{global,local}.php' 
				),
				'module_paths' => array (
						'./module',
						'./vendor' 
				) 
		) 
);
