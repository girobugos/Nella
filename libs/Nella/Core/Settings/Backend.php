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
 * @package    Nella\Core\Settings
 */

namespace Nella\Core\Settings;

use Nella\Nella;
use Nella\Models;
use Nette\Forms\Form;

/**
 * Backend settings
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Core\Settings
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
		$table = array(
			'modules' => "modules",
			'options' => "options",
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
			'install' => "modules",
			'uninstall' => "modules",
			'upgrade' => "modules",
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
		$signals = array('submit');
		return in_array($signal, $signals);
	}
	
	/**
	 * @return	void
	 */
	public function beforeRender()
	{
		$this->template->module = "Settings";
		parent::beforeRender();
	}
	
	/**
	 * Create component list modules data grid
	 * 
	 * @param	string	component name
	 */
	public function createComponentListModulesDataGrid($name)
	{
		$dg = $this->getDataGrid($name, Models\Module::getDataSource());
		$dg->addColumn('name', "Name: ");
		$dg->addColumn('version', "Version: ");
		$dg->addActionColumn("Actions");
		$dg->addAction('list', "List");
	}
	
	/**
	 * Action modules
	 */
	public function actionModules()
	{
		$this->template->action = "Modules";
		$this->template->modules = array();
		$modules = Models\Module::findAll();
		if (count($modules) > 0)
		{
			foreach ($modules as $module)
			{
				$this->template->modules[$module->namespacename] = array(
					'namespace' => $module->namespacename,
					'version' => $module->version,
					'name' => $module->name,
					'installed' => TRUE,
					'id' => $module->id
				);
			}
		}
		$modules = $this->getNoInstalledModules();
		if (count($modules) > 0)
		{
			foreach ($modules as $module)
			{
				if (!array_key_exists($module['namespace'], $this->template->modules))
				{
					$this->template->modules[$module['namespace']] = (object)array(
						'namespace' => $module['namespace'],
						'version' => $module['version'],
						'name' => $module['name'],
						'installed' => FALSE
					);
				}
				else
				{
					$pom = $this->template->modules[$module['namespace']];
					if ($pom['version'] != $module['version'])
						$pom['newversion'] = $module['version'];
					$this->template->modules[$module['namespace']] = (object)$pom;
				}
			}
		}
	}
	
	/**
	 * Get no installed modules
	 * 
	 * @return	array
	 */
	protected function getNoInstalledModules()
	{
		$modules = array();
		if (file_exists(APP_DIR . "/presenters"))
		{
			$dir = dir(APP_DIR . "/presenters");
			while (($path = $dir->read()) !== FALSE)
			{
				if (is_dir(APP_DIR . "/presenters/" . $path) && $path != "." && $path != ".." && file_exists(APP_DIR . "/presenters/" . $path . "/Loader.php"))
				{
					$class = Nella::formatModuleLoaderClass($path);
					$modules[$path] = array('name' => $class::NAME, 'namespace' => $path, 'version' => $class::VERSION);
				}
			}
		}
		$dir = dir(LIBS_DIR . "/Nella/Modules");
		while (($path = $dir->read()) !== FALSE)
		{
			if (is_dir(LIBS_DIR . "/Nella/Modules/" . $path) && $path != "." && $path != ".." && file_exists(LIBS_DIR . "/Nella/Modules/" . $path . "/Loader.php") && !array_key_exists($path, $modules))
			{
				$class = Nella::formatModuleLoaderClass($path);
				$modules[$path] = array('name' => $class::NAME, 'namespace' => $path, 'version' => $class::VERSION);
			}
		}
		return $modules;
	}
	
	/**
	 * Process install signal
	 */
	public function handleInstall($namespace)
	{
		$class = Nella::formatModuleLoaderClass($namespace);
		if (!class_exists($class))
			require_once Nella::formatModuleLoaderFile($namespace);
		$class::install();
		
		Models\Module::createNew($class::NAME, $namespace, $class::VERSION);

		Nella::getCache()->clean(array('tags' => array("modules")));
		$this->flashMessage("Module installed", "ok");
		$this->redirect("modules");
	}
	
	/**
	 * Process uninstall signal
	 */
	public function handleUninstall($id)
	{
		$module = Models\Module::findById($id);
		$namespace = $module->namespacename;
		$class = Nella::formatModuleLoaderClass($namespace);
		if (!class_exists($class))
			require_once Nella::formatModuleLoaderFile($namespace);
		$class::uninstall();
		
		$module->delete();

		Nella::getCache()->clean(array('tags' => array("modules")));
		$this->flashMessage("Module uninstalled", "ok");
		$this->redirect("modules");
	}
	
	/**
	 * Process upgrade signal
	 */
	public function handleUpgrade($id)
	{
		$module = Models\Module::findById($id);
		$namespace = $module->namespacename;
		$class = Nella::formatModuleLoaderClass($namespace);
		if (!class_exists($class))
			require_once Nella::formatModuleLoaderFile($namespace);
		$class::upgrade($module->version);
		
		$module->version = $class::VERSION;
		$module->save();

		Nella::getCache()->clean(array('tags' => array("modules")));
		$this->flashMessage("Module upgraded", "ok");
		$this->redirect("modules");
	}
	
	/**
	 * Create component options form
	 * 
	 * @param	string	component name
	 */
	public function createComponentOptionsForm($name)
	{
		$form = $this->getForm($name);
		$form->addText('webname', "Webname:")->addRule(Form::FILLED, "Webname must be filled");
		$form->addText('mail', "E-mail:")->addRule(Form::FILLED, "E-mail bust be filled")->addRule(Form::EMAIL, "E-mail mus be valie e-mail adress");
		$form->addRadiolist('registrations', "Registrations:", array(0 => "Off", 1 => "On"));
		$form->addRadiolist('mailverifyregistration', "E-mail verification registration:", array(0 => "Off", 1 => "On"));
		$form->addRadiolist('editor', "WYSIWYG editor:", array(0 => "Disable", 'CKEditor' => "CKEditor"/*, 'texyla' => "Texyla!"*/));
		$form->addText('gacode', "Google Analytics key:");
		$form->addTextarea('termsregistration', "Registration terms:");
		
		$form->addSubmit('sub', "Save");
		
		$form->onSubmit[] = array($this, "processOptionsForm");
	}
	
	/**
	 * Action options
	 */
	public function actionOptions()
	{
		$this->template->action = "Options";
		$data = array();
		foreach (Models\Option::findAll() as $row)
		{
			$data[$row->key] = $row->value;
		}
		$this['optionsForm']->setDefaults($data);
	}
	
	/**
	 * Process form
	 * 
	 * @param	Nette\Forms\Form	processing form
	 */
	public function processOptionsForm(Form $form)
	{
		foreach (Models\Option::findAll() as $row)
		{
			if (isset($form[$row->key]))
			{
				$row->value = $form[$row->key]->getValue();
				$row->save();
			}
		}

		Nella::getCache()->clean(array('tags' => array("options")));
		$this->flashMessage('Changes saved', "ok");
		$this->redirect('options');
	}
}