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
 * @package    Nella\Components\AdminMenu
 */

namespace Nella\Components\AdminMenu;

use Nella\Nella;
use Nette\Application\Control;

/**
 * Admin menu component
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Components\AdminMenu
 */
class AdminMenu extends Control
{
	/**
	 * Render admin menu
	 * 
	 * @return	string
	 */
	public function render()
	{
		$this->template->nodes = Nella::getAdminNodes();
		$this->template->setFile(__DIR__ . "/template.phtml");
		$this->template->render();
	}
}