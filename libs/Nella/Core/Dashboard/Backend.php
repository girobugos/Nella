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
 * @package    Nella\Core\Dashboard
 */

namespace Nella\Core\Dashboard;

/**
 * The Nella.
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Core\Dashboard
 */
class Backend extends \Nella\Presenters\BackendBase
{
	/**
	 * Get translated privilege from called action
	 * 
	 * @param	string
	 * @return	string
	 */
	protected function getActionTranslatedPrivilege($action)
	{
		return $action;
	}
	
	/**
	 * Get translated privilege from called signal
	 * 
	 * @param	string
	 * @return	string
	 */
	protected function getSignalTranslatedPermission($signal)
	{
		return $signal;
	}
	
	/**
	 * Is called action auto allowed
	 * 
	 * @param	string
	 * @return	bool
	 */
	protected function isActionAutoAllow($action)
	{
		return FALSE;
	}
	
	/**
	 * Is called signal auto allowed
	 * 
	 * @param	string
	 * @return	bool
	 */
	protected function isSignalAutoAllow($signal)
	{
		return FALSE;
	}
	
	/**
	 * @return	void
	 */
	public function actionDefault()
	{
		$this->template->module = "Dashboard";
		$this->template->action = "Nevim co sem dat... :-(";
	}
}
