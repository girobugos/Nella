<?php
/**
 * Nella
 *
 * Copyright (c) 2006, 2010 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the "New BSD License" that is bundled
 * with this package in the file nella.txt.
 *
 * For more information please see http://nellacms.com
 *
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @license    http://nellacms.com/license  New BSD License
 * @link       http://nellacms.com
 * @category   Nella
 * @package    Nella
 */

namespace Nella;

use Nette;

/**
 * The Nella.
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella
 */
final class Nella
{

	/**#@+ Nella version identification */
	const NAME = 'Nella';
	const VERSION = '0.6.0-dev';
	const REVISION = '$WCREV$ released on $WCDATE$';
	const PACKAGE = 'PHP 5.3';
	const DEVELOPMENT = TRUE;
	/**#@-*/

	/** @var array */
	private static $options = array();
	/** @var array */
	private static $adminNodes = array();
	/** @var Nella\Core\Auth\Models\User */
	private static $user = NULL;


	/**
	 * Static class - cannot be instantiated.
	 * 
	 * @throws	LogicException
	 */
	final public function __construct()
	{
		throw new \LogicException("Cannot instantiate static class " . get_class($this));
	}

	/**
	 * Compares current Nella - Content Management System version with given version.
	 * 
	 * @param  string
	 * @return int
	 */
	public static function compareVersion($version)
	{
		return version_compare($version, self::VERSION);
	}

	/**
	 * Nella - Content Management System promotion.
	 * 
	 * @return void
	 */
	public static function promo($xhtml = TRUE)
	{
		echo '<a href="http://nellacms.com/" title="Nella - Content Management System"><img ',
			'src="http://nellacms.com/images/nella-powered.gif" alt="Powered by Nella" width="80" height="15"',
			($xhtml ? ' />' : '>'), '</a>';
	}
	
	/**
	 * Add root admin node
	 * 
	 * @param	string	display name
	 * @param	string	presenter link format
	 * @param	string
	 * @param	string
	 * @return	Nella\Node
	 */
	public static function addRootAdminNode($name, $plink, $resource, $privilege)
	{
		return static::$adminNodes[] = new Node($name, $plink, $resource, $privilege);
	}
	
	/**
	 * Get all admin nodes
	 * 
	 * @return	array
	 */
	public static function getAdminNodes()
	{
		return static::$adminNodes;
	}
	
	/**
	 * Load admin menu
	 * 
	 * @return	void
	 */
	public static function loadAdminMenu()
	{
		$cache = self::getCache();
		if (isset($cache['adminMenu']))
		{
			static::$adminNodes = array_merge(static::$adminNodes, $cache['adminMenu']);
		}
		else
		{
			static::$adminNodes[] = new Node("Dashboard", ":Dashboard:Backend:default", "Dashboard", "default");
			$modules = Core\Settings\Models\Module::findAll();
			if (count($modules) > 0)
			{
				foreach ($modules as $module)
				{
					$class = $module->getLoaderClass();
					$class::loadAdminMenu();
				}
			}

			$node = static::$adminNodes[] = new Node("Media", ":Media:Backend:imageList", "Media", "images");
			$pom = $node->addChild("Images", ":Media:Backend:imageList", "Media", "images");
			$pom->addHiddenChild(":Media:Backend:imageAdd");
			$pom->addHiddenChild(":Media:Backend:imageDelete");
			$node = static::$adminNodes[] = new Node("Users", ":Auth:Backend:list", "Auth", "list");
			$node->addHiddenChild(":Auth:Backend:privilege");
			$node = static::$adminNodes[] = new Node("Settings", ":Settings:Backend:modules", "Settings", "modules");
			$node->addChild("Modules", ":Settings:Backend:modules", "Settings", "modules");
			$node->addChild("Options", ":Settings:Backend:options", "Settings", "options");
			$cache->save('adminMenu', static::$adminNodes, array(
				'expire' => "+2days",
				'tags' => array("modules", "adminmenu")
			));
		}
	}
	
	/**
	 * Load routes
	 * 
	 * @param	array
	 * @return	array
	 */
	public static function loadRoutes(&$router)
	{
		$cache = self::getCache();
		if (!isset($cache['classes']))
			$modules = Core\Settings\Models\Module::findAll();
		else
			$modules = $cache['classes'];
		if (count($modules) > 0)
		{
			$cls = array();
			foreach ($modules as $module)
			{
				if ($module instanceof Core\Settings\Models\Module)
					$class = $module->getLoaderClass();
				else
					$cls[] = $class = $module;
				$router = $class::loadRoutes($router);
			}
		}
		
		if (!isset($cache['classes']))
		{
			$cache->save('classes', $cls, array(
				'expire' => "+2days",
				'tags' => array("modules", "routes")
			));
		}

		return $router;
	}

	/**
	 * Get option
	 * 
	 * @param	string	option key
	 * @return	string
	 * @throws	LogicException
	 */
	public static function getOption($key)
	{
		if (count(static::$options) <= 0)
		{
			$cache = self::getCache();
			if (!isset($cache['options']))
			{
				$rows = Core\Settings\Models\Option::findAll();
				foreach ($rows as $row)
					static::$options[$row->key] = $row->value;

				$cache->save('options', static::$options, array(
					'expire' => "+2days",
					'tags' => array("options")
				));
			}
			else
				static::$options = $cache['options'];
		}
		
		if (isset(static::$options[$key]) || static::$options[$key] === NULL)
			return static::$options[$key];
		else
			throw new \LogicException("Option '".$key."' not exist.");
	}
	
	/**
	 * Format module loader file path
	 * 
	 * @param	string	module namespace
	 * @return	string
	 */
	public static function formatModuleLoaderFile($module, $type = NULL)
	{
		$path = APP_DIR . "/presenters/" . $module . "/Loader.php";
		if (!file_exists($path))
			$path = LIBS_DIR . "/Nella/Modules/" . $module . "/Loader.php";
			
		return $path;
	}
	
	/**
	 * Format module loader class
	 * 
	 * @param	string	module namespace
	 * @return	string
	 */
	public static function formatModuleLoaderClass($module)
	{
		return '\Nella\Modules\\' . $module . '\Loader';
	}

	/**
	 * Get cache
	 *
	 * @return	Nette\Caching\Cache
	 */
	public static function getCache()
	{
		return \Nette\Environment::getCache("Nella.Core");
	}

	/**
	 * Get user
	 *
	 * @return Nella\Core\Auth\Models\User
	 */
	public static function getUser()
	{
		if (empty(static::$user))
			static::$user = Core\Auth\Models\User::findByUsername(Nette\Environment::getUser()->getIdentity()->getName());
		return static::$user;
	}
}