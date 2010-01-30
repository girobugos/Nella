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
 * @package    Nella\Modules
 */

namespace Nella\Modules;

/**
 * Base module loader
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Modules
 */
abstract class BaseLoader extends \Nette\Object
{
	/** @var string */
	const NAME = "";
	/** @var string */
	const VERSION = "";
	/** @var array */
	protected static $actionPermissions = array();
	/** @var array */
	protected static $signalPermissions = array();
	/** @var array */
	protected static $autoAllowActions = array();
	/** @var array */
	protected static $autoAllowSignal = array("submit");
	
	/**
	 * Get all permissions
	 * 
	 * @return	array
	 */
	public static function getAllPermissions()
	{
		$permissions = array();
		if (count(static::$actionPermissions) > 0)
		{
			foreach (static::$actionPermissions as $action)
			{
				if (!in_array($action, $permissions))
					$permissions[] = $action;
			}
		}
		if (count(static::$signalPermissions) > 0)
		{
			foreach (static::$signalPermissions as $action)
			{
				if (!in_array($action, $permissions))
					$permissions[] = $action;
			}
		}
		return $permissions;
	}
	
	/**
	 * Is action automaticly allowed?
	 * 
	 * @param	string
	 * @return	bool
	 */
	public static function isAutoAllowAction($action)
	{
		return array_key_exists($action, static::$autoAllowActions);
	}
	
	/**
	 * Is signal automaticly allowed?
	 * 
	 * @param	string
	 * @return	bool
	 */
	public static function isAutoAllowSignal($signal)
	{
		return array_key_exists($signal, static::$autoAllowSignal);
	}
	
	/**
	 * Translate action to permission
	 * 
	 * @param	string
	 * @return	string
	 */
	public static function translateActionPermission($action)
	{
		return array_key_exists($action, static::$actionPermissions) ? static::$actionPermissions[$action] : NULL;
	}
	
	/**
	 * Translate signal to permission
	 * 
	 * @param	string
	 * @return	string
	 */
	public static function translateSignalPermission($signal)
	{
		return array_key_exists($signal, static::$signalPermissions) ? static::$signalPermissions[$signal] : NULL;
	}
	
	/**
	 * Load admin menu
	 * 
	 * @return	void
	 */
	public static function loadAdminMenu() { }
	
	/**
	 * Get routes
	 * 
	 * @return	array
	 */
	public static function getRoutes() { }
	
	/**
	 * Install
	 */
	public static function install()
	{
		
	}
	
	/**
	 * Uninstall
	 */
	public static function uninstall()
	{
		
	}
	
	/**
	 * Upgrade
	 * 
	 * @param	string
	 */
	public static function upgrade($previousVersion)
	{
		
	}
}