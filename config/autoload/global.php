<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

$dbParams = array (
		'database' => 'lms',
		'username' => 'root',
		'password' => '',
		'hostname' => 'localhost',
		'port' => 3306 
);

return array (
		'doctrine' => array (
				'connection' => array (
						'orm_default' => array (
								'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
								'params' => array (
										'host' => $dbParams ['hostname'],
										'port' => $dbParams ['port'],
										'user' => $dbParams ['username'],
										'password' => $dbParams ['password'],
										'dbname' => $dbParams ['database'] 
								) 
						) 
				) 
		),
		'service_manager' => array (
				'factories' => array (
						'Zend\Db\Adapter\Adapter' => function ($sm) use($dbParams) {
							$adapter = new BjyProfiler\Db\Adapter\ProfilingAdapter ( array (
									'driver' => 'pdo',
									'dsn' => 'mysql:dbname=' . $dbParams ['database'] . ';host=' . $dbParams ['hostname'],
									'database' => $dbParams ['database'],
									'username' => $dbParams ['username'],
									'password' => $dbParams ['password'],
									'hostname' => $dbParams ['hostname'] 
							) );
							
							$adapter->setProfiler ( new BjyProfiler\Db\Profiler\Profiler () );
							$adapter->injectProfilingStatementPrototype ();
							return $adapter;
						} 
				) 
		) 
);
