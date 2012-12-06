<?php

namespace AfcCommons;

class Module {
	/**
	 * 
	 * @var current namespace
	 */
	protected $_namespace = __NAMESPACE__;
	/**
	 * 
	 * @var current directory
	 */
	protected $_dir = __DIR__;
	
	/**
	 * 
	 * @var array namesapces
	 */
	protected $_namespaces = array ();
	
	/**
	 * Get namepsaces according to the folder structure
	 *
	 * @return multitype:
	 */
	final protected function _getNamespaces() {
		if (empty ( $this->_namespaces )) {
			// Prepare namespace for autoloading for all the modules
			$namespaces = array ();
			// Get the directory to the source path of the current module
			$dirValue = $this->_dir . '/src/' . $this->_namespace;
			
			// Add to namespace
			$namespaces [$this->_namespace] = $dirValue;
			
			// Add AfcCommons namespace to the loading
			$namespaces ['AfcCommons'] = __DIR__ . '/src/AfcCommons';
			
			$this->_namespaces = $namespaces;
		}
		return $this->_namespaces;
	}
	
	/**
	 * Autoloader configuration
	 *
	 * @return multitype:multitype:string
	 */
	public function getAutoloaderConfig() {
		
		// Get AfcCommons autoload_classmap
		$afcCommonsAutoloadClassMap = require __DIR__ . '/autoload_classmap.php';
		
		// Set the Autoload Classmap file
		$fileAutoloadClassMap = $this->_dir . '/autoload_classmap.php';
		
		$customAutoloadClassMap = array();
		// Check if Autoload classmap for the module exists
		if (file_exists ( $fileAutoloadClassMap )) {
			// If Autoload classmap for the module exists
			$customAutoloadClassMap = require_once $fileAutoloadClassMap;
		}
		
		// Override custom class map with predefind classmap
		$classMapAutoloader = @array_replace_recursive ( $afcCommonsAutoloadClassMap, $customAutoloadClassMap );
		
		$namespaces = $this->_getNamespaces ();
		
		return array (
				'Zend\Loader\ClassMapAutoloader' => $classMapAutoloader,
				'Zend\Loader\StandardAutoloader' => array (
						'namespaces' => $namespaces 
				) 
		);
	}
	
	/**
	 * Load service configuration and factory settings accordingly
	 * @return unknown|multitype:multitype:unknown
	 */
	public function getServiceConfig() {
		$serviceConfig = array ();
		
		// Autoload General Function
		$standardFunctionPath = $this->_dir . '/src/' . $this->_namespace . '/Functions';
		
		if (is_dir ( $standardFunctionPath )) {
			$functions = scandir ( $standardFunctionPath );
			foreach ( $functions as $function ) {
				if (is_file ( $standardFunctionPath . "\\" . $function )) {
					$key = $this->_namespace . "\\Functions\\" . str_replace ( ".php", "", $function );
					$value = function ($sm) use($key) {
						$function = new $key ();
						return $function;
					};
					$serviceConfig [$key] = $value;
				}
			}
		}
		return array (
				'factories' => $serviceConfig 
		);
	}
	
	/**
	 * Load automatic Configuration
	 *
	 * @return multitype:
	 */
	public function getConfig() {
		// Generate invokables array
		$invokables = array ();
		
		// Initialize Route Array
		$routes = array ();
		
		// Initialize Template Array
		$templatePathStack = array ();
		
		$doctrineConfiguration = array (
				'driver' => array (
						$this->_namespace . '_driver' => array (
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'cache' => 'array',
								'paths' => array (
										$this->_dir . '/src/' . $this->_namespace . '/Entity' 
								) 
						),
						'orm_default' => array (
								'drivers' => array (
										$this->_namespace . '\Entity' => $this->_namespace . '_driver' 
								) 
						) 
				) 
		);
		
		// Get all controllers of the current module
		$controllerPath = $this->_dir . '/src/' . $this->_namespace . '/Controller/';
		$allController = array ();
		if (is_dir ( $controllerPath )) {
			$allController = scandir ( $controllerPath );
		}
		
		// Construct all necessary controller invokables list
		foreach ( $allController as $controller ) {
			if (is_file ( $controllerPath . "\\" . $controller )) {
				
				$controllerName = str_replace ( "Controller.php", "", $controller );
				$postfixControllerName = $controllerName . 'Controller';
				$key = $this->_namespace . "\\Controller\\" . $controllerName;
				$value = $this->_namespace . "\\Controller\\" . $postfixControllerName;
				$invokables [$key] = $value;
			}
		}
		
		// Construct Route
		$dashedNamespace = $this->convertToDash ( $this->_namespace );
		$routes [$dashedNamespace] = array (
				'type' => 'segment',
				'options' => array (
						'route' => "/" . $dashedNamespace . '[/:controller[/:action]]',
						'defaults' => array (
								'__NAMESPACE__' => $this->_namespace . "\\Controller",
								'controller' => $this->_namespace . "\\Controller\\Index",
								'action' => 'index' 
						) 
				),
				'may_terminate' => true,
				'child_routes' => array (
						'wildcard' => array (
								'type' => 'wildcard' 
						) 
				) 
		);
		
		// Template Path Stack
		$templatePathStack [$dashedNamespace] = $this->_dir . '/view';
		
		// View manager configurations
		$viewManagerConfigurations = array (
				'display_not_found_reason' => true,
				'display_exceptions' => true,
				'doctype' => 'HTML5',
						/*'template_map' => array (
								'layout/layout' => __DIR__ . '/../../templates/layout/guest.phtml',
								'layout/login' => __DIR__ . '/../../templates/layout/login.phtml',
								'layout/user' => __DIR__ . '/../../templates/layout/user.phtml',
								'layout/error' => __DIR__ . '/../../templates/layout/error.phtml',
								'error/404' => __DIR__ . '/../../templates/error/404.phtml',
								'error/index' => __DIR__ . '/../../templates/error/index.phtml' 
						),*/
						'template_path_stack' => $templatePathStack,
				'strategies' => array (
						'ViewJsonStrategy' 
				) 
		);
		
		$configArray = array (
				// Doctrine Configurations
				'doctrine' => $doctrineConfiguration,
				
				// Invokable Controllers
				'controllers' => array (
						'invokables' => $invokables 
				),
				
				// Route definition
				'router' => array (
						'routes' => $routes 
				),
				
				// View manager configurations
				'view_manager' => $viewManagerConfigurations 
		);
		
		// Check for custom configurations
		$customConfigArray = array ();
		$fileModuleConfig = $this->_dir . '/config/module.config.php';
		if (file_exists ( $fileModuleConfig )) {
			// If custom configurations exists then get the configurations
			$customConfigArray = require_once $fileModuleConfig;
		}
		
		// Merge all the configurations
		$configArray = @array_replace_recursive ( $configArray, $customConfigArray );
		
		return $configArray;
	}
	
	/**
	 * Convert ABC to a-b-c AbcDef to abc-def
	 *
	 * @param string $string        	
	 * @return string
	 */
	private function convertToDash($string) {
		$new_string = strtolower ( $string [0] );
		for($i = 1; $i < strlen ( $string ); $i ++) {
			if (preg_match ( '/^[A-Z]$/', $string [$i] )) {
				$new_string = $new_string . "-" . strtolower ( $string [$i] );
			} else {
				$new_string = $new_string . $string [$i];
			}
		}
		return $new_string;
	}
}