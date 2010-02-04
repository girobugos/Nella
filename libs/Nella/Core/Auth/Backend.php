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
 * @package    Nella\Core\Auth
 */

namespace Nella\Core\Auth;

use Nette;
use Nella;
use Nella\Models;
use Nette\Forms\Form;

/**
 * The Nella.
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Core\Auth
 */
class Backend extends Nella\Presenters\BackendBase
{
	/**
	 * Get translated privilege from called action
	 * 
	 * @param	string
	 * @return	string
	 */
	protected function getActionTranslatedPrivilege($action)
	{
		$table = array(
			'active' => "status",
			'deactive' => "status",
			'ban' => "status",
			'suspend' => "status",
			'list' => "list",
			'privilege' => "privilege",
			'userAdd' => "add",
		);
		return $table[$action];
	}
	
	/**
	 * Get translated privilege from called signal
	 * 
	 * @param	string
	 * @return	string
	 */
	protected function getSignalTranslatedPermission($signal)
	{
		$table = array(
			'active' => "status",
			'deactive' => "status",
			'ban' => "status",
			'suspend' => "status",
			'privilegeForm' => "privilege",
			'userAddForm' => "add",
		);
		return $table[$signal];
	}
	
	/**
	 * Is called action auto allowed
	 * 
	 * @param	string
	 * @return	bool
	 */
	protected function isActionAutoAllow($action)
	{
		$actions = array('profile', 'logout');
		return in_array($action, $actions);
	}
	
	/**
	 * Is called signal auto allowed
	 * 
	 * @param	string
	 * @return	bool
	 */
	protected function isSignalAutoAllow($signal)
	{
		$signals = array('submit', 'changePasswordForm', 'changeMailForm');
		return in_array($signal, $signals);
	}
	
	/**
	 * @return	void
	 */
	public function beforeRender()
	{
		$this->template->module = "Users";
		parent::beforeRender();
	}
	
	/**
	 * Create component list data grid
	 * 
	 * @param	string	component name
	 */
	public function createComponentListDataGrid($name)
	{
		$grid = $this->getDataGrid($name, Models\User::getDataSource());
		$grid->addNumericColumn('id', "#")->getCellPrototype()->addStyle('text-align: center')->addStyle('width: 40px');
		$grid['id']->addFilter();
		$grid->addColumn('username', "Username")->addFilter();
		$grid->addColumn('mail', "E-mail")->addFilter();
		$grid->addActionColumn('Actions')->getHeaderPrototype()->addStyle('width: 98px');
		$grid->addAction("Active", "active!")->ifCallback = function ($data) {
			if (Nella\Nella::getUser()->id == $data['id'] && !Nette\Environment::getUser()->isAllowed("Auth", "superadmin")) return FALSE;
			if ($data['status'] == Models\User::STATUS_ACTIVE) return FALSE;
			else return TRUE;
		};
		if (Nella\Nella::getOption('mailverifyregistration'))
		{
			$grid->addAction("Deactive", "deactive!")->ifCallback = function ($data) {
				if (Nella\Nella::getUser()->id == $data['id'] && !Nette\Environment::getUser()->isAllowed("Auth", "superadmin")) return FALSE;
				if ($data['status'] == Models\User::STATUS_UNACTIVE) return FALSE;
				else return TRUE;
			};
		}
		$grid->addAction("Ban", "ban!")->ifCallback = function ($data) {
			if (Nella\Nella::getUser()->id == $data['id'] && !Nette\Environment::getUser()->isAllowed("Auth", "superadmin")) return FALSE;
			if ($data['status'] == Models\User::STATUS_BANNED) return FALSE;
			else return TRUE;
		};
		$grid->addAction("Suspend", "suspend!")->ifCallback = function ($data) {
			if (Nella\Nella::getUser()->id == $data['id'] && !Nette\Environment::getUser()->isAllowed("Auth", "superadmin")) return FALSE;
			if ($data['status'] == Models\User::STATUS_SUSPENDED) return FALSE;
			else return TRUE;
		};
		$grid->addAction("Privileges", "privilege", NULL, "ajax-popup")->ifCallback = function ($data) {
			if (Nella\Nella::getUser()->id == $data['id'] && !Nette\Environment::getUser()->isAllowed("Auth", "superadmin")) return FALSE;
			else return TRUE;
		};
	}
	
	/**
	 * Process signal user active
	 * 
	 * @param	int	user id
	 */
	public function handleActive($id)
	{
		$user = Models\User::findById($id);
		if (Nella\Nella::getUser()->id == $id && !Nette\Environment::getUser()->isAllowed("Auth", "superadmin"))
			$this->flashMessage("You can not edit yourself", "error");
		elseif (empty($user))
			$this->flashMessage("User doesn't exist", "error");
		elseif ($user->status == Models\User::STATUS_ACTIVE)
			$this->flashMessage("This user is active", "warning");
		else
		{
			$user->status = Models\User::STATUS_ACTIVE;
			$user->save();
			$this->flashMessage("User activated", "ok");
		}
		$this->redirect("this");
	}
	
	/**
	 * Process signal user deactive
	 * 
	 * @param	int	user id
	 */
	public function handleDeactive($id)
	{
		$user = Models\User::findById($id);
		if (Nella\Nella::getUser()->id == $id && !Nette\Environment::getUser()->isAllowed("Auth", "superadmin"))
			$this->flashMessage("You can not edit yourself", "error");
		elseif (empty($user))
			$this->flashMessage("User doesn't exist", "error");
		elseif ($user->status == Models\User::STATUS_UNACTIVE)
			$this->flashMessage("This user is deactive", "warning");
		else
		{
			$user->status = Models\User::STATUS_UNACTIVE;
			$user->save();
			$this->flashMessage("User deactivated", "ok");
		}
		$this->redirect("this");
	}
	
	/**
	 * Process signal user ban
	 * 
	 * @param	int	user id
	 */
	public function handleBan($id)
	{
		$user = Models\User::findById($id);
		if (Nella\Nella::getUser()->id == $id && !Nette\Environment::getUser()->isAllowed("Auth", "superadmin"))
			$this->flashMessage("You can not edit yourself", "error");
		elseif (empty($user))
			$this->flashMessage("User doesn't exist", "error");
		elseif ($user->status == Models\User::STATUS_BANNED)
			$this->flashMessage("This user is banned", "warning");
		else
		{
			$user->status = Models\User::STATUS_BANNED;
			$user->save();
			$this->flashMessage("User banned", "ok");
		}
		$this->redirect("this");
	}
	
	/**
	 * Process signal user suspend
	 * 
	 * @param	int	user id
	 */
	public function handleSuspend($id)
	{
		$user = Models\User::findById($id);
		if (Nella\Nella::getUser()->id == $id && !Nette\Environment::getUser()->isAllowed("Auth", "superadmin"))
			$this->flashMessage("You can not edit yourself", "error");
		elseif (empty($user))
			$this->flashMessage("User doesn't exist", "error");
		elseif ($user->status == Models\User::STATUS_SUSPENDED)
			$this->flashMessage("This user is suspend", "warning");
		else
		{
			$user->status = Models\User::STATUS_SUSPENDED;
			$user->save();
			$this->flashMessage("User suspended", "ok");
		}
		$this->redirect("this");
	}
	
	/**
	 * Action list
	 */
	public function actionList()
	{
		$this->template->action = "List";
		$this->template->showAdd = Nette\Environment::getUser()->isAllowed("Auth", $this->getActionTranslatedPrivilege('userAdd'));
	}
	
	/**
	 * Create component form change password
	 * 
	 * @param	string	component name
	 */
	public function createComponentChangePasswordForm($name)
	{
		$form = $this->getForm($name);
		$form->addPassword('password', "Password: ")
			->addRule(Form::FILLED, "Password must be filled")
			->addRule(Form::MIN_LENGTH, "Password must be at %d characters length", 6);
		$form->addPassword('newpassword', "New password: ")
			->addRule(Form::FILLED, "New password must be filled")
			->addRule(Form::MIN_LENGTH, "Password must be at %d characters length", 6);
		$form->addPassword('newpassword2', "Re-enter new password: ")
			->addRule(Form::FILLED, "Re-enter new password must be filled")
			->addRule(Form::EQUAL, 'Passwords do not match', $form['newpassword']);
		
		$form->addSubmit('sub', "Save");
		
		$form->onSubmit[] = array($this, "processChangePasswordForm");
	}
	
	/**
	 * Process form
	 * 
	 * @param	Nette\Forms\Form	processing form
	 */
	public function processChangePasswordForm(Form $form)
	{
		$user = Models\User::findByUsername($this->getIdentity()->name);
		if (empty($user))
		{
			Nette\Environment::getUser()->signOut();
			$this->redirect(":Auth:Frontend:login");
		}
		else
		{
			if ($user->verifyPassword($form['password']->getValue()))
			{
				$user->password = Nella\Tools::hash($form['newpassword']->getValue());
				$user->save();
				$this->flashMessage("Password has been changed");
				$this->redirect("this");
			}
			else
			{
				$form['password']->addError("Bad password");
			}
		}
	}
	
	/**
	 * Create component form change e-mail
	 * 
	 * @param	string	component name
	 */
	public function createComponentChangeMailForm($name)
	{
		$form = $this->getForm($name);
		$form->addPassword('password', "Password: ")
			->addRule(Form::FILLED, "Password must be filled")
			->addRule(Form::MIN_LENGTH, "Password must be at %d characters length", 6);
		$form->addText('mail', "E-mail: ")
			->addRule(Form::FILLED, "E-mail must be filled")
			->addRule(Form::EMAIL, "E-mail must be in valid format");
		$form->addText('mail2', "Re-enter E-mail: ")
			->addRule(Form::FILLED, "Re-enter E-mail must be filled")
			->addRule(Form::EQUAL, 'E-mail do not match', $form['mail']);
		
		$form->addSubmit('sub', "Save");
		
		$form->onSubmit[] = array($this, "processChangeMailForm");
	}
	
	/**
	 * Process form change e-mail
	 * 
	 * @param	Nette\Forms\Form	processing form
	 */
	public function processChangeMailFrom(Form $form)
	{
		$user = Models\User::findByUsername($this->getIdentity()->name);
		if (empty($user))
		{
			Nette\Environment::getUser()->signOut();
			$this->redirect(":Auth:Frontend:login");
		}
		else
		{
			if ($user->verifyPassword($form['password']->getValue()))
			{
				$user->mail = $form['mail']->getValue();
				$user->save();
				$this->flashMessage("E-mail has been changed");
				$this->redirect("this");
			}
			else
			{
				$form['password']->addError("Bad password");
			}
		}
	}
	
	/**
	 * Action user profile
	 */
	public function actionProfile()
	{
		$user = Models\User::findByUsername($this->getIdentity()->name);
		if (empty($user))
		{
			Nette\Environment::getUser()->signOut();
			$this->redirect(":Auth:Frontend:login");
		}
		else
		{
			$this->template->mail = $user->mail;
		}
		
		$this->template->action = "Profile";
	}
	
	/**
	 * Action logout
	 */
	public function actionLogout()
	{
		if ($this->getHttpRequest()->getCookie("nella-autologin"))
		{
			list($username, $key) = explode(":", $this->getHttpRequest()->getCookie("nella-autologin"));
			$key = sha1($key.$this->getHttpRequest()->getHeader('accept-charset').
					$this->getHttpRequest()->getHeader('accept-encoding').
					$this->getHttpRequest()->getHeader('user-agent'));
			$user = Models\User::findByUsername(Nette\Environment::getUser()->getIdentity()->getName());
			$tokens = Models\UserToken::findAll(array(
				array("[user_id] = %i", $user->id),
				array("[type] = %i", Models\UserToken::TYPE_AUTOLOGIN),
				array("[key] = %s", $key)));
			if (isset($tokens[0]))
				$tokens[0]->destroy();
			$this->getHttpResponse()->deleteCookie('nella-autologin');
		}
		Nette\Environment::getUser()->signOut();
		$this->flashMessage("Logout complete", "ok");
		$this->redirect(":Auth:Frontend:login");
	}
	
	/**
	 * Create component privilege form
	 * 
	 * @param	string	component name
	 */
	public function createComponentPrivilegeForm($name)
	{
		$form = $this->getForm($name);
		$form->addHidden('user_id');
		$form->addSubmit('sub', "Save");
		$gr = $form->addGroup("Media");
		$gr->add($form->addCheckbox('Media_images', "Images"));
		$gr = $form->addGroup("Users");
		$gr->add($form->addCheckbox('Auth_list', "List"));
		$gr->add($form->addCheckbox('Auth_add', "Add"));
		$gr->add($form->addCheckbox('Auth_privilege', "Privileges"));
		$gr->add($form->addCheckbox('Auth_status', "Status"));
		$gr = $form->addGroup("Settiongs");
		$gr->add($form->addCheckbox('Settings_options', "Options"));
		$gr->add($form->addCheckbox('Settings_modules', "Modules"));
		$gr->add($form->addCheckbox('Dashboard_default', "Admin"));
		$gr->add($form->addCheckbox('Auth_superadmin', "SuperAdmin"));
		$modules = Nella\Models\Module::findAll();
		if (count($modules) > 0)
		{
			foreach ($modules as $module)
			{
				$gr = $form->addGroup($module->name);
				$class = $module->getLoaderCLass();
				$permissions = $class::getAllPermissions();
				if (count($permissions) > 0)
				{
					foreach ($permissions as $permission)
						$gr->add($form->addCheckbox($module->namespacename . "_" . $permission, Nette\String::capitalize($permission)));
				}
			}
		}
		
		$form->onSubmit[] = array($this, "processPrivilegeForm");
	}
	
	/**
	 * Action user privileges
	 * 
	 * @param	int user id
	 */
	public function actionPrivilege($id = NULL)
	{
		$this->template->action = "Privileges";
		$user = Models\User::findById($id);
		if (Nella\Nella::getUser()->id == $id && !Nette\Environment::getUser()->isAllowed("Auth", "superadmin"))
		{
			$this->flashMessage("You can not edit yourself", "error");
			$this->redirect("list");
		}
		elseif (empty($user))
		{
			$this->flashMessage("User doesn't exist", "error");
			$this->redirect("list");
		}
		else
		{
			$defaults = array('user_id' => $user->id);
			if (count($user->userPrivileges) > 0)
			{
				foreach ($user->userPrivileges as $privilege)
				{
					$defaults[$privilege->resource . "_" . $privilege->privilege] = TRUE;
				}
			}
			$this['privilegeForm']->setDefaults($defaults);
		}
		
		if ($this->isAjax())
			$this->invalidateControl("content");
	}
	
	/**
	 * Process privilege form
	 * 
	 * @param	Nette\Forms\Form	processing form
	 */
	public function processPrivilegeForm(Nette\Forms\Form $form)
	{
		$values = $form->getValues();
		$user = Models\User::findById($values['user_id']);
		if (empty($user))
		{
			$this->flashMessage("User doesn't exist", "error");
			$this->redirect("list");
		}
		else
		{
			unset($values['sub']);
			unset($values['user_id']);
			foreach ($values as $key => $value)
			{
				list($resource, $privilege) = explode("_", $key);
				$data = $user->getPrivilege($resource, $privilege);
				if (empty($data) && $value == TRUE)
					$user->addPrivilege($resource, $privilege);
				elseif (!empty($data) && $value == FALSE)
					$data->destroy();
			}

			Nella\Nella::getCache()->clean(array(
				'tags' => array('userprivileges-'.$user->id)
			));
			$this->flashMessage("Privileges saved", "ok");
			$this->redirect("list");
		}
	}
	
	/**
	 * Create component user add form
	 * 
	 * @param	string	component name
	 */
	public function createComponentUserAddForm($name)
	{
		$form = $this->getForm($name);
		$form->addText('username', "Username: ")
			->addRule(Form::FILLED, "Username must be filled")
			->addRule(Form::REGEXP, "Username must be in valid format", "/^[a-z0-9](?:\.?(?:[a-z0-9_-]+\.?)*[a-z0-9])?$/i");
		$form->addPassword('password', "Password: ")
			->addCondition(Form::FILLED)
				->addRule(Form::MIN_LENGTH, "Password must be at %d characters length", 6);
		$form->addPassword('password2', "Re-enter Password: ")
			->addCondition(Form::FILLED)
				->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);
		$form->addText('mail', "E-mail: ")
			->addRule(Form::FILLED, "E-mail must be filled")
			->addRule(Form::EMAIL, "E-mail must be in valid format");
		$status = array(
			Models\User::STATUS_ACTIVE => "active",
			Models\User::STATUS_BANNED => "banned",
			Models\User::STATUS_SUSPENDED => "suspended",
		);
		if (Nella\Nella::getOption('mailverifyregistration') == "1")
			$status[Models\User::STATUS_UNACTIVE] = "unactive";
		$form->addSelect('status', "Status: ", $status);
		
		$form->addSubmit('sub', "Add");
		
		$form->onSubmit[] = array($this, "processUserAddForm");
	}
	
	/**
	 * Action user add
	 */
	public function actionUserAdd()
	{
		$this->template->action = "User add";
		if ($this->isAjax())
			$this->invalidateControl("content");
	}
	
	/**
	 * Process user add form
	 * 
	 * @param	Nette\Forms\Form	processing form
	 */
	public function processUserAddForm(Nette\Forms\Form $form)
	{
		//TODO: fix sqlInjection -> convert to dibi style
		if (Models\User::count("[username] = '" . $form['username']->getValue() ."'") > 0)
			$form['username']->addError("This username is exist");
		elseif (Models\User::count("[mail] = '". $form['mail']->getValue() ."'") > 0)
			$form['mail']->addError("This e-mail registered withd another user");
		else
		{
			$password = $form['password']->getValue();
			if (empty($password))
				$password = Nella\Tools::getRandomString(8);
			
			$status = $form['status']->getValue();
			$user = Models\User::createNew($form['username']->getValue(), Nella\Tools::hash($password), $form['mail']->getValue(), $status);
			if ($status == Models\Items\User::STATUS_UNACTIVE)
			{
				$key = Nella\Tools::getRandomString(16);
				Models\UserToken::createNew($user->id, $key, Models\UserToken::TYPE_ACTIVATION);
				$this->sendActivationMail($form['mail']->getValue(), $form['username']->getValue(), $form['password']->getValue(), $key);
				$this->flashMessage("Verification e-mail has been send", "info");
			}
			else
				$this->sendRegistrationMail($form['mail']->getValue(), $form['username']->getValue(), $form['password']->getValue());
			
			$this->flashMessage("Reigstration complete", "ok");
			if (Nette\Environment::getUser()->isAllowed("Auth", $this->getActionTranslatedPrivilege('privilege')))
				$this->redirect('privilege', array('id' => $user->id));
			
			$this->redirect('list');
		}
	}
	
	/**
	 * Send registration e-mail
	 * 
	 * @param	string
	 * @param	string
	 * @param	string	plain text password
	 */
	protected function sendRegistrationMail($email, $username, $password)
	{
		$template = $this->createTemplate();
		$template->langIso639 = $this->lang;
		$template->username = strtolower($username);
		$template->password = $password;
		foreach ($this->formatTemplateFiles("Auth:Emails", "registration") as $file)
		{
			if (file_exists($file))
			{
				$template->setFile($file);
				break;
			}
		}
		$mail = new Nette\Mail\Mail();
		$mail->setFrom(Nella\Nella::getOption('mail'), $template->webname = Nella\Nella::getOption("webname"));
		$mail->setSubject($template->title = "Registration Complete - ".$template->webname);
		$mail->addTo($email);
		$mail->setBody($template);
		$mail->send();
	}
	
	/**
	 * Send activation e-mail
	 * 
	 * @param	string
	 * @param	string
	 * @param	string	plain text password
	 * @param	string	activation key
	 */
	protected function sendActivationMail($email, $username, $password, $key)
	{
		$template = $this->createTemplate();
		$template->langIso639 = $this->lang;
		$template->username = strtolower($username);
		$template->password = $password;
		$template->key = $key;
		foreach ($this->formatTemplateFiles("Auth:Emails", "activation") as $file)
		{
			if (file_exists($file))
			{
				$template->setFile($file);
				break;
			}
		}
		$mail = new Nette\Mail\Mail();
		$mail->setFrom(Nella\Nella::getOption('mail'), $template->webname = Nella\Nella::getOption("webname"));
		$mail->setSubject($template->title = "User activation - ".$template->webname);
		$mail->addTo($email);
		$mail->setBody($template);
		$mail->send();
	}
}
