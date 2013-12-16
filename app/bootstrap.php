<?php

// Load Nette Framework or autoloader generated by Composer
require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode(TRUE);
//$configurator->setDebugMode(array("90.180.45.360", "90.180.45.361"));
//$configurator->setDebugMode(array("95.82.187.242"));
$configurator->enableDebugger(__DIR__ . '/../log', 'robyer@seznam.cz');

// Specify folder for cache
$configurator->setTempDirectory(__DIR__ . '/../temp');

// Enable RobotLoader - this will load all classes automatically
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../libs')
	->register();

/*$configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
	$compiler->addExtension('arachne.resources', new \Arachne\Resources\ResourcesExtension());
};*/

$baseUri = dirname($_SERVER['SCRIPT_NAME']);
$configurator->addParameters(array('baseUri' => $baseUri));

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon', FALSE);
if ($configurator->isDebugMode()) {
	$configurator->addConfig(__DIR__ . '/config/debug.neon', FALSE);
}

// Load local/server configiration
$local = in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'));
$configurator->addConfig(__DIR__ . '/config/' . ($local ? 'local' : 'server') . '.neon', FALSE);

$container = $configurator->createContainer();

// Setup other functions
require __DIR__ . '/functions.php';

return $container;
