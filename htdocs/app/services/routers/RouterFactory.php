<?php

use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{

	/**
	 * @return IRouter
	 */
	public function create()
	{
		$router = new RouteList();

		$lang = "[<lang [a-z]{2}>/]";
		$domain = "//[!www.]%domain%/";

		$router[] = new Route($domain . 'texyla/<action>/<id>', array(
				'presenter' => 'Texyla',
				'action' => 'default',
				'id' => NULL,
		), Route::SECURED);

		// Admin routers
		$adminRouter = new RouteList('Admin');
		$adminRouter[] = new Route($lang . 'admin', array(
				'presenter' => "Home",
				'action' => "default",
			), Route::ONE_WAY | Route::SECURED);

		$adminRouter[] = new Route($domain . $lang . 'admin/<presenter>/<action>[/<id>]', 'Home:default', Route::SECURED);
		$router[] = $adminRouter;

		// Front routers
		$frontRouter = new RouteList('Front');
		$frontRouter[] = new Route($domain . $lang . 'index.php', 'Home:default', Route::ONE_WAY, Route::SECURED);

		$frontRouter[] = new Route($domain . $lang . 'news/<link>', array(
			'presenter' => 'News',
			'action' => 'show',
			//'lang' => "en"
		), Route::SECURED);

		$frontRouter[] = new Route($domain . 'p/<id>', array(
			'presenter' => 'Redirect',
			'action' => 'plugin',
		), Route::SECURED);

		$frontRouter[] = new Route('//vi.%domain%/' . $lang . '<action>[/<id>]', array(
			'presenter' => 'Versioninfo',
			'action' => 'default',
			//'lang' => "en"
		), Route::SECURED);

		$frontRouter[] = new Route('//addons.%domain%/' . $lang . '<action>[/<id>]', array(
			'presenter' => 'Addons',
			'action' => 'default',
			//'lang' => "en"
		), Route::SECURED);

		$frontRouter[] = new Route('//www.%domain%/verification', array(
			'presenter' => 'Oauth',
			'action' => 'verification',
			//'lang' => "en"
		), Route::SECURED);
		
		$frontRouter[] = new Route($domain . $lang . '<presenter>[/<action>]/page/<vp-page>', array(
				'presenter' => 'Home',
				'action' => 'default',
				'id' => NULL,
				//'lang' => "en"
		), Route::SECURED);

		$frontRouter[] = new Route($domain . $lang . '<presenter>/<action>/<id>', array(
				'presenter' => 'Home',
				'action' => 'default',
				'id' => NULL,
				//'lang' => "en"
		), Route::SECURED);

		$router[] = $frontRouter;

		return $router;
	}

}
