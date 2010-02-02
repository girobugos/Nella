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
 * @package    Nella\Modules\Page
 */

namespace Nella\Modules\Page;

use Nella\Nella;

/**
 * Page module loader
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Modules\Page
 */
abstract class Loader extends \Nella\Modules\BaseLoader
{
	/** @var string */
	const NAME = "Pages";
	/** @var string */
	const VERSION = "0.1";
	/** @var array */
	protected static $actionPermissions = array(
			"add" => "create",
			"delete" => "delete",
			"publish" => "publish",
			"unpublish" => "publish",
			"edit" => "edit",
			"backToRevision" => "edit",
			"list" => "list");
	/** @var array */
	protected static $signalPermissions = array(
			"delete" => "delete",
			"publish" => "publish",
			"unpublish" => "publish",
			"backToRevision" => "edit",
			"listDataGrid-order" => "list",
			"listDataGrid-form"	=> "list");
	/** @var array */
	protected static $autoAllowActions = array();
	/** @var array */
	protected static $autoAllowSignal = array("submit");
	
	/**
	 * Load admin menu
	 * 
	 * @return	void
	 */
	public static function loadAdminMenu()
	{
		$node = Nella::addRootAdminNode(self::NAME, ":Page:Backend:list", "Page", "list");
		$node->addHiddenChild(":Page:Backend:add");
		$node->addHiddenChild(":Page:Backend:delete");
		$node->addHiddenChild(":Page:Backend:publish");
		$node->addHiddenChild(":Page:Backend:unpublish");
		$node->addHiddenChild(":Page:Backend:edit");
		$node->addHiddenChild(":Page:Backend:backToRevision");
	}
	
	/**
	 * Load router
	 * 
	 * @param	array	routers
	 * @return	array
	 */
	public static function loadRoutes(&$router)
	{
		//TODO: multilang
		//$router[] = new Route("<lang [a-z]{2}>/<slug>", array(
		$router[] = new Route("<slug>", array(
			'module' => "Page",
			'presenter' => "Frontend",
			'action' => "default",
			'slug' => "homepage",
		));
		
		return $router;
	}
	
	/**
	 * Install module
	 * 
	 * @return	void
	 */
	public static function install()
	{
		Models\Pages::install();
	}
	
	/**
	 * Uninstall module
	 * 
	 * @return	void
	 */
	public static function uninstall()
	{
		Models\Pages::uninstall();
	}
}