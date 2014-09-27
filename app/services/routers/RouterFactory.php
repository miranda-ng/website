<?php

use Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route;

class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public function create()
	{
		$router = new RouteList();

		$lang = "[<lang [a-z]{2}>/]";

		// Admin routers
		$adminRouter = new RouteList('Admin');
		$adminRouter[] = new Route('admin', array(
				'presenter' => "Home",
				'action' => "default",
			), Route::ONE_WAY);
		$adminRouter[] = new Route($lang . 'admin/<presenter>/<action>[/<id>]', 'Home:default');
		$router[] = $adminRouter;

		// Front routers
		$frontRouter = new RouteList('Front');
		$frontRouter[] = new Route('index.php', 'Home:default', Route::ONE_WAY);

		$frontRouter[] = new Route($lang . 'news/<link>', array(
			'presenter' => 'News',
			'action' => 'show',
			//'lang' => "en"
		));

		$frontRouter[] = new Route('p/<id>', array(
			'presenter' => 'Redirect',
			'action' => 'plugin',
		));

		$frontRouter[] = new Route($lang . '<presenter>[/<action>]/page/<vp-page>', array(
				'presenter' => 'Home',
				'action' => 'default',
				'id' => NULL,
				//'lang' => "en"
		));

		$frontRouter[] = new Route($lang . '<presenter>/<action>/<id>', array(
				'presenter' => 'Home',
				'action' => 'default',
				'id' => NULL,
				//'lang' => "en"
		));

		$router[] = $frontRouter;

		return $router;
	}

}
