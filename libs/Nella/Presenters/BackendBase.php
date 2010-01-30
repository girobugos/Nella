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
 * @package    Nella\Presenters
 */

namespace Nella\Presenters;

use Nette;
use Nella;

/**
 * Backend Base Presenter with automatic permission verification
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Presenters
 */
class BackendBase extends Base
{
	/**
	 * @return	void
	 */
	protected function startup()
	{
		parent::startup();
		$this->setLayout('layout.backend');
		if (!Nette\Environment::getUser()->isAuthenticated() && $this->getHttpRequest()->getCookie("nella-autologin"))
		{
			list($username, $key) = explode(":", $this->getHttpRequest()->getCookie("nella-autologin"));
			$key = sha1($key.$this->getHttpRequest()->getHeader('accept-charset').
					$this->getHttpRequest()->getHeader('accept-encoding').
					$this->getHttpRequest()->getHeader('user-agent'));
			try
			{
				Nette\Environment::getUser()->authenticate($username, $key, "auto");
			} catch (Nette\Security\AuthenticationException $e) {}
		}
		
		if (Nette\Environment::getUser()->isAuthenticated())
		{
			if ($this->isAllow())
				$this->template->user = $this->getIdentity()->name;
			elseif ($this->getName() == "Dashboard:Backend" && $this->getAction() == "default")
			{
				$this->flashMessage("No permission for this action", "safety");
				$this->redirect(":Auth:Frontend:login");
			}
			else
			{
				$this->flashMessage("No permission for this action", "safety");
				$this->redirect(":Dashboard:Backend:default");
			}
		}
		else
			$this->redirect(":Auth:Frontend:login");
	}
	
	/**
	 * Create component admin menu
	 * 
	 * @param	string	component name
	 */
	public function createComponentAdminMenu($name)
	{
		$menu = new Nella\Components\AdminMenu\AdminMenu($this, $name);
	}
	
	/**
	 * Get translated privilege from called action
	 * 
	 * @param	string
	 * @return	string
	 */
	protected function getActionTranslatedPrivilege($action)
	{
		$pom = explode("\\", get_called_class());
		unset($pom[count($pom) - 1]);
		$class = implode("\\", $pom) . "\\Loader";
		return $class::translateActionPermission($action);
	}
	
	/**
	 * Get translated privilege from called signal
	 * 
	 * @param	string
	 * @return	string
	 */
	protected function getSignalTranslatedPermission($signal)
	{
		$pom = explode("\\", get_called_class());
		unset($pom[count($pom) - 1]);
		$class = implode("\\", $pom) . "\\Loader";
		return $class::translateSignalPermission($signal);
	}
	
	/**
	 * Is called action auto allowed
	 * 
	 * @param	string
	 * @return	bool
	 */
	protected function isActionAutoAllow($action)
	{
		$pom = explode("\\", get_called_class());
		unset($pom[count($pom) - 1]);
		$class = implode("\\", $pom) . "\\Loader";
		return $class::isAutoAllowAction($action);
	}
	
	/**
	 * Is called signal auto allowed
	 * 
	 * @param	string
	 * @return	bool
	 */
	protected function isSignalAutoAllow($signal)
	{
		$pom = explode("\\", get_called_class());
		unset($pom[count($pom) - 1]);
		$class = implode("\\", $pom) . "\\Loader";
		return $class::isAutoAllowSignal($signal);
	}
	
	/**
	 * Is signals autoallow
	 * 
	 * @return	bool
	 */
	protected function isSignalsAllow()
	{
		$pom = explode("\\", get_called_class());
		$namespacename = $pom[count($pom) - 2];
		foreach ($this->getSignal() as $signal)
		{
			if (!empty($signal) && !$this->isSignalAutoAllow($signal) && strpos("-", $signal) !== FALSE)
			{
				if (!Nette\Environment::getUser()->isAllowed($namespacename, $this->getSignalTranslatedPermission($signal)))
					return FALSE;
			}
		}
		
		return TRUE;
	}
	
	/**
	 * Is allow called operation
	 * 
	 * @return	bool
	 */
	protected function isAllow()
	{
		$pom = $this->getSignal();
		if (is_array($pom) && count($pom) > 0)
		{
			if (!$this->isSignalsAllow())
				return FALSE;
		}
		$pom = explode("\\", get_called_class());
		$namespacename = $pom[count($pom) - 2];
		if (!$this->isActionAutoAllow($this->getAction()))
		{
			if (!Nette\Environment::getUser()->isAllowed($namespacename, $this->getActionTranslatedPrivilege($this->getAction())))
				return FALSE;
		}
		
		return TRUE;
	}
	
	/**
	 * @return	void
	 */
	public function beforeRender()
	{
		$title = "";
		if (isset($this->template->action))
			$title .= $this->template->action;
		if (isset($this->template->module))
			$title .= " - " . $this->template->module;
			
		if (!empty($title))
			$this->template->title = $title;
	}
	
	/**
	 * Get action permissions transformation array
	 * 
	 * @return	array
	 */
	protected function atctionPermissions()
	{
		return array(
			'list' => "read",
			'add' => "create",
			'edit' => "update",
			'delete' => "delete",
		);
	}
	
	/**
	 * Get signal permissions transformation array
	 * 
	 * @return	array
	 */
	protected function signalPermissions()
	{
		return array(
			'submit' => "submit",
			'delete' => "delete",
		);
	}
}
