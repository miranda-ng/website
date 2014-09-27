<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require 'maintenance.html'; die();

// Let bootstrap create Dependency Injection container.
$container = require __DIR__ . '/app/bootstrap.php';

// Run application.
$container->application->run();
