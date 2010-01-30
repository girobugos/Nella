<?php
/**
 * Nella
 *
 * Copyright (c) 2006, 2010 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the "Nella license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nellacms.com
 *
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @license    http://nellacms.com/license  Nella license
 * @link       http://nellacms.com
 * @category   Nella
 * @package    Nella
 */

use \Nette\Debug;
use \Nette\Environment;
use \Nette\Application\Route;
use \Nette\Application\SimpleRouter;
use \Nette\Application\Presenter;

require_once LIBS_DIR . "/Nette/loader.php";

Debug::enable(Debug::DEVELOPMENT);
Environment::loadConfig();
Environment::setMode(Environment::DEVELOPMENT);

$session = Environment::getSession();
$session->setSavePath(APP_DIR . '/sessions/');

$database = Environment::getConfig('database');
dibi::addSubst('prefix', isset($database['prefix']) ? $database['prefix'] : "");
Inflector::$railsStyle = TRUE;
ActiveMapper::connect($database);

if (!Environment::isProduction())
{
	Debug::$strictMode = TRUE;
	Debug::$showLocation = TRUE;
	Debug::enableProfiler();
	Debug::addColophon(array('dibi', 'getColophon'));
	Presenter::$invalidLinkMode = Presenter::INVALID_LINK_EXCEPTION;
}

\Nella\Nella::loadAdminMenu();

$application = Environment::getApplication();
$application->catchExceptions = FALSE;

$router = $application->getRouter();
$router[] = new Route("index.php", array(
	'module' => "Page",
	'presenter' => "Frontend",
	'action' => "default",
	'slug' => "homepage",
), Route::ONE_WAY);
$router[] = new Route("images/<id [0-9]+>-<width [0-9]+>x<height [0-9]+>.<suffix (jpg|gif|png)>", array(
	'module' => "Media",
	'presenter' => "Frontend",
	'action' => "load",
));
$router = \Nella\Nella::loadRoutes($router);
$router[] = new Route("admin/<action (login|lost-password|lost-password-mail|registration|activation)>/<username>/<key>", array(
	'module' => "Auth",
	'presenter' => "Frontend",
	'username' => NULL,
	'key' => NULL,
	'lang' => "en",
));
$router[] = new Route("admin/<module>/<action>", array(
	'module' => "Dashboard",
	'presenter' => "Backend",
	'action' => "default",
	'lang' => "en",
));
$router[] = new Route("<slug>", array(
	'module' => "Page",
	'presenter' => "Frontend",
	'action' => "default",
	'slug' => "homepage",
));/*, Route::ONE_WAY);*/

$application->run();
