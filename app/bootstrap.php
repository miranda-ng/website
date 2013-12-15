<?php

use Nette\Diagnostics\Debugger,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\RouteList;

// Load Nette Framework
require LIBS_DIR . '/Nette/loader.php';


// Configure application
$configurator = new Nette\Config\Configurator;

// Enable Nette Debugger for error visualisation & logging
//$configurator->setProductionMode($configurator::AUTO);
$configurator->enableDebugger(__DIR__ . '/../temp/log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(LIBS_DIR)
	->register();

$baseUri = dirname($_SERVER['SCRIPT_NAME']);
$configurator->addParameters(array('baseUri' => $baseUri));
	
// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');
$container = $configurator->createContainer();

// Setup routers
require APP_DIR . '/routers.php';

// Setup other functions
require APP_DIR . '/functions.php';

// Configure and run the application!
$container->application->run();
