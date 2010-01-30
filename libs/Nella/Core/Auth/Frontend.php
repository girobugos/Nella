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
use Nette\Forms\Form;
use Nette\Security\AuthenticationException;

/**
 * Authorization/Auhentication frontend presenter
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Core\Auth
 */
class Frontend extends Nella\Presenters\Base
{
	/**
	 * @return	void
	 */
	protected function startup()
	{
		parent::startup();
		$this->setLayout("auth.frontend");
	}
	
	/**
	 * Create component login form
	 * 
	 * @param	string	component name
	 */
	public function createComponentLoginForm($name)
	{
		$form = $this->getForm($name);
		$form->addText('username', "Username or E-mail: ")
			->addRule(Form::FILLED, "Username or E-mail must be filled")
			->addCondition(~Form::EMAIL)
				->addRule(Form::REGEXP, "Username must be in valid format", "/^[a-z0-9](?:\.?(?:[a-z0-9_-]+\.?)*[a-z0-9])?$/i");
		$form->addPassword('password', "Password: ")
			->addRule(Form::FILLED, "Password must be filled")
			->addRule(Form::MIN_LENGTH, "Password must be at %d characters length", 6);
		$form->addCheckbox('auto', "auto login next time");
			
		$form->addProtection(NULL, 600);
		$form->addSubmit('sub', "Login");
		
		$form->onSubmit[] = array($this, "processLoginForm");
	}
	
	/**
	 * Process login form
	 * 
	 * @param	Nette\Forms\Form	processing form
	 */
	public function processLoginForm(Form $form)
	{
		try
		{
			Nette\Environment::getUser()->authenticate($form['username']->getValue(), $form['password']->getValue());
			if ($form['auto']->getValue())
			{
				$key = Nella\Tools::getRandomString(16);
				$user = Models\User::findByUsername(Nette\Environment::getUser()->getIdentity()->getName());
				$token = Models\UserToken::createNew($user->id, sha1($key.
					$this->getHttpRequest()->getHeader('accept-charset').
					$this->getHttpRequest()->getHeader('accept-encoding').
					$this->getHttpRequest()->getHeader('user-agent')), Models\UserToken::TYPE_AUTOLOGIN);
				$this->getHttpResponse()->setCookie('nella-autologin', $user->username.":".$key, "+2 years");
			}
			$this->redirect(":Dashboard:Backend:default");
		}
		catch (AuthenticationException $e)
		{
			if ($e->getCode() == Nette\Security\IAuthenticator::IDENTITY_NOT_FOUND)
				$form['username']->addError($e->getMessage());
			elseif ($e->getCode() == Nette\Security\IAuthenticator::INVALID_CREDENTIAL)
				$form['password']->addError($e->getMessage());
			else
			{
				$this->flashMessage($e->getMessage(), "warning");
				$this->redirect("this");
			}
		}
	}
	
	/**
	 * Create component registration form
	 * 
	 * @param	string	component name
	 */
	public function createComponentRegistrationForm($name)
	{
		$form = $this->getForm($name);
		$form->addText('username', "Username: ")
			->addRule(Form::FILLED, "Username must be filled")
			->addRule(Form::REGEXP, "Username must be in valid format", "/^[a-z0-9](?:\.?(?:[a-z0-9_-]+\.?)*[a-z0-9])?$/i");
		$form->addPassword('password', "Password: ")
			->addRule(Form::FILLED, "Password must be filled")
			->addRule(Form::MIN_LENGTH, "Password must be at %d characters length", 6);
		$form->addPassword('password2', "Re-enter Password: ")
			->addRule(Form::FILLED, "Re-enter Password must be filled")
			->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);
		$form->addText('mail', "E-mail: ")
			->addRule(Form::FILLED, "E-mail must be filled")
			->addRule(Form::EMAIL, "E-mail must be in valid format");
		if (Nella\Nella::getOption('mailverifyregistration') == "1")
		{
			$form->addText('mail2', "Re-enter E-mail: ")
				->addRule(Form::FILLED, "Re-enter E-mail must be filled")
				->addRule(Form::EQUAL, 'E-mail do not match', $form['mail']);
		}
		if (Nella\Nella::getOption("termsregistration") != NULL)
		{
			$form->addCheckbox('terms', "accept terms")
				->addRule(Form::FILLED, "You must accept terms");
		}
		
		$form->addProtection(NULL, 600);
		$form->addSubmit('sub', "Register");
		
		$form->onSubmit[] = array($this, "processRegistrationFrom");
	}
	
	/**
	 * Process registration form
	 * 
	 * @param	Nette\Forms\Form	processing form
	 */
	public function processRegistrationFrom(Form $form)
	{
		if (Models\User::count("[username] = " . $form['username']->getValue()) > 0)
			$form['username']->addError("This username is exist");
		elseif (Models\User::count("[mail] = " . $form['mail']->getValue()) > 0)
			$form['mail']->addError("This e-mail registered withd another user");
		else
		{
			$status = Models\User::STATUS_ACTIVE;
			if (Nella\Nella::getOption('mailverifyregistration') == "1")
				$status = Models\User::STATUS_UNACTIVE;
			$user = Models\User::createNew($form['username']->getValue(), Nella\Tools::hash($form['password']->getValue()), $form['mail']->getValue(), $status);
			if ($status == 0)
			{
				$key = Nella\Tools::getRandomString(16);
				Models\UserToken::createNew($user->id, $key, Models\UserToken::TYPE_ACTIVATION);
				$this->sendActivationMail($form['mail']->getValue(), $form['username']->getValue(), $form['password']->getValue(), $key);
				$this->flashMessage("Verification e-mail has been send", "info");
			}
			else
				$this->sendRegistrationMail($form['mail']->getValue(), $form['username']->getValue(), $form['password']->getValue());
			
			$this->flashMessage("Reigstration complete", "ok");
			
			$this->redirect('login');
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
	
	/**
	 * Create component activation form
	 * 
	 * @param	string	component name
	 */
	public function createComponentActivationForm($name)
	{
		$form = $this->getForm($name);
		$form->addText('username', "Username or E-mail: ")
			->addRule(Form::FILLED, "Username or e-mail must be filled")
			->addCondition(~Form::EMAIL)
				->addRule(Form::REGEXP, "Username must be in valid format", "/^[a-z0-9](?:\.?(?:[a-z0-9_-]+\.?)*[a-z0-9])?$/i");
		$form->addText('key', "Verification key: ")
			->addRule(Form::FILLED, "Verification key must be filled")
			->addRule(Form::LENGTH, "Verification key must be %s length", 16)
			->addRule(Form::REGEXP, "Verification key must be in valid format", "/^[a-z0-9]*$/i");
			
		$form->addProtection(NULL, 600);
		$form->addSubmit('sub', "Verifi");
		
		$form->onSubmit[] = array($this, "processActivationFrom");
	}
	
	/**
	 * Process activation form
	 * 
	 * @param	Nette\Forms\Form	processing form
	 */
	public function processActivationFrom(Form $form)
	{
		if (strpos($form['username']->getValue(), "@") !== FALSE)
		{
			$user = Models\User::findByMail($form['username']->getValue());
			if (empty($user))
				$form['username']->addError("User with this e-mail not registered");
		}
		else
		{
			$user = Models\User::findByUsername($form['username']->getValue());
			if (empty($user))
				$form['username']->addError("User with this username not registered");
		}
		
		if (!empty($user))
		{
			$token = Models\UserToken::find(array("[user_id] = " . $user->id, "[type] = " . Models\UserToken::TYPE_ACTIVATION));
			if ($user->status != 0)
				$this->flashMessage("This is activated", "info");
			elseif (count($token) <= 0)
				$this->flashMessage("Unable to activate this user", "warning");
			elseif ($token[0]->key != $form['key']->getValue())
				$form['key']->addError("Bad key");
			else
			{
				$user->status = 1;
				$user->save();
				$token[0]->delete();
				$this->flashMessage("Activation complete", "ok");
			}
			
			if (!$form->hasErrors())
				$this->redirect('login');
		}
	}
	
	/**
	 * Action user activation
	 * 
	 * @param	string
	 * @param	string	activation key
	 */
	public function actionActivation($username = NULL, $key = NULL)
	{
		$this['activationForm']->setDefaults(array('username' => $username, 'key' => $key));
	}
	
	/**
	 * Create component form
	 * 
	 * @param	string	component name
	 */
	public function createComponentLostPasswordMailForm($name)
	{
		$form = $this->getForm($name);
		$form->addText('username', "Username or E-mail: ")
			->addRule(Form::FILLED, "Username or e-mail must be filled")
			->addCondition(~Form::EMAIL)
				->addRule(Form::REGEXP, "Username must be in valid format", "/^[a-z0-9](?:\.?(?:[a-z0-9_-]+\.?)*[a-z0-9])?$/i");
		
		$form->addProtection(NULL, 600);
		$form->addSubmit('sub', "Get e-mail");
		
		$form->onSubmit[] = array($this, "processLostPasswordMailFrom");
	}
	
	/**
	 * Process form
	 * 
	 * @param	Nette\Forms\Form	processing form
	 */
	public function processLostPasswordMailFrom(Form $form)
	{
		if (strpos($form['username']->getValue(), "@") !== FALSE)
		{
			$user = Models\User::findByMail($form['username']->getValue());
			if (empty($user))
				$form['username']->addError("User with this e-mail not registered");
		}
		else
		{
			$user = Models\User::findByUsername($form['username']->getValue());
			if (empty($user))
				$form['username']->addError("User with this username not registered");
		}
		
		if (!empty($user))
		{
			$key = Nella\Tools::getRandomString(16);
			Models\UserToken::createNew($user->id, $key, Models\UserToken::TYPE_LOSTPASSWORD);
			$this->sendLostPasswordMail($user->mail, $user->username, $key);
			$this->flashMessage("Password has been send to your e-mail", "ok");
			$this->redirect('login');
		}
	}
	
	/**
	 * Send lost password e-mail
	 * 
	 * @param	string
	 * @param	string
	 * @param	string	plain text password
	 * @param	string	activation key
	 */
	protected function sendLostPasswordMail($email, $username, $key)
	{
		$template = $this->createTemplate();
		$template->langIso639 = $this->lang;
		$template->username = strtolower($username);
		$template->key = $key;
		foreach ($this->formatTemplateFiles("Auth:Emails", "lostPassword") as $file)
		{
			if (file_exists($file))
			{
				$template->setFile($file);
				break;
			}
		}
		$mail = new Nette\Mail\Mail();
		$mail->setFrom(Nella\Nella::getOption('mail'), $template->webname = Nella\Nella::getOption("webname"));
		$mail->setSubject($template->title = "New password - ".$template->webname);
		$mail->addTo($email);
		$mail->setBody($template);
		$mail->send();
	}
	
	/**
	 * Create component loast password form
	 * 
	 * @param	string	component name
	 */
	public function createComponentLostPasswordForm($name)
	{
		$form = $this->getForm($name);
		$form->addText('username', "Username or E-mail: ")
			->addRule(Form::FILLED, "Username or e-mail must be filled")
			->addCondition(~Form::EMAIL)
				->addRule(Form::REGEXP, "Username must be in valid format", "/^[a-z0-9](?:\.?(?:[a-z0-9_-]+\.?)*[a-z0-9])?$/i");
		$form->addText('key', "Verification key: ")
			->addRule(Form::FILLED, "Verification key must be filled")
			->addRule(Form::LENGTH, "Verification key must be %s length", 16)
			->addRule(Form::REGEXP, "Verification key must be in valid format", "/^[a-z0-9]*$/i");
		$form->addPassword('password', "New password: ")
			->addRule(Form::FILLED, "Password must be filled")
			->addRule(Form::MIN_LENGTH, "Password must be at %d characters length", 6);
		$form->addPassword('password2', "Re-enter new password: ")
			->addRule(Form::FILLED, "Re-enter password must be filled")
			->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);
		
		$form->addProtection(NULL, 600);
		$form->addSubmit('sub', "Save");
		
		$form->onSubmit[] = array($this, "processLostPasswordFrom");
	}
	
	/**
	 * Process form lost password
	 * 
	 * @param	Nette\Forms\Form	processing form
	 */
	public function processLostPasswordFrom(Form $form)
	{
		if (strpos($form['username']->getValue(), "@") !== FALSE)
		{
			$user = Models\User::findByMail($form['username']->getValue());
			if (empty($user))
				$form['username']->addError("User with this e-mail not registered");
		}
		else
		{
			$user = Models\User::findByUsername($form['username']->getValue());
			if (empty($user))
				$form['username']->addError("User with this username not registered");
		}
		
		if (!empty($user))
		{
			$token = Models\UserToken::find(array("[user_id] = " . $user->id, "[type] = " . Models\UserToken::TYPE_LOSTPASSWORD));
			if (count($token) <= 0)
				$this->flashMessage("Lost password key not exist", "warning");
			elseif ($token[0]->key != $form['key']->getValue())
				$form['key']->addError("Bad key");
			else
			{
				$user->password = Nella\Tools::hash($form['password']->getValue());
				$user->save();
				$token[0]->delete();
				$this->flashMessage("New password saved", "ok");
			}
			
			if (!$form->hasErrors())
				$this->redirect('login');
		}
	}
	
	/**
	 * Action user lost password
	 * 
	 * @param	string
	 * @param	string	activation key
	 */
	public function actionLostPassword($username = NULL, $key = NULL)
	{
		$this['lostPasswordForm']->setDefaults(array('username' => $username, 'key' => $key));
	}
}