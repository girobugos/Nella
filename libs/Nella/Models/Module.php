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
 * @package    Nella\Models
 */

namespace Nella\Models;

use Nella\Nella;

/**
 * Module model for core modules
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Models
 * @property-read	int	$id
 * @property	string	$name
 * @property	string	$namespacename
 * @property	string	$version
 */
class Module extends \ActiveRecord
{
	/** @var string */
	protected static $table = 'modules';
	/** @var string */
	protected static $primary = 'id';

	/**
	 * Get loader class
	 *
	 * @return	string
	 */
	public function getLoaderClass()
	{
		$class = Nella::formatModuleLoaderClass($this->namespacename);
		if (!class_exists($class))
			require_once Nella::formatModuleLoaderFile($this->namespacename);

		return $class;
	}

	/**
	 * Create module
	 * 
	 * @param	string
	 * @param	string	namespace name in app (Backend, Frontend etd. added automaticly)
	 * @param	string
	 * @return	Nella\Models\Module
	 */
	public static function createNew($name, $namespacename, $version)
	{
		return static::create(array(
			'name' => $name,
			'namespacename' => $namespacename,
			'version' => $version,
		));
	}
	
	/**
	 * Get data source for list data grid
	 * 
	 * @return	DibiDataSource
	 */
	public static function getDataSource()
	{
		return parent::getDataSource("SELECT [id],[name],[namespacename],[version] FROM [:prefix:".static::$table."]");
	}
}