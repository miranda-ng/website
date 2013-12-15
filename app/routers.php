<?php

use Nette\Diagnostics\Debugger,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\RouteList;

// Setup router
$router = $container->router;

// Admin routers
$container->router[] = $adminRouter = new RouteList('Admin');
$adminRouter[] = new Route('admin', array(
		'presenter' => "Home",
		'action' => "default",
	), Route::ONE_WAY);
$adminRouter[] = new Route('/admin/<presenter>/<action>[/<id>]', 'Home:default');

// Front routers
$router[] = new Route('index.php', 'Home:default', Route::ONE_WAY);

$router[] = new Route('news/<link>', array(
        'presenter' => 'News',
        'action' => 'show',
));

$router[] = new Route('p/<id>', array(
	'presenter' => 'Redirect',
	'action' => 'plugin',
));

$router[] = new Route('<presenter>[/<action>]/page/<vp-page>', array(
        'presenter' => 'Home',
        'action' => 'default',
        'id' => NULL
));

$router[] = new Route('<presenter>/<action>/<id>', array(
        'presenter' => 'Home',
        'action' => 'default',
        'id' => NULL
));

