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

use Nella;
use Nette;
use Nette\Application\AppForm;

/**
 * Base Presenter
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Presenters
 */
class Base extends \Nette\Application\Presenter
{
	public $oldLayoutMode = FALSE;
	
	/**
	 * @var string
	 */
	public $lang = "en";
	
	/**
	 * Returns array of classes persistent parameters. They have public visibility and are non-static.
	 * This default implementation detects persistent parameters by annotation @persistent.
	 * 
	 * @return	array
	 *
	public static function getPersistentParams()
	{
		return array_merge(parent::getPersistentParams(), array("lang"));
	}*/
	
	/**
	 * @return	void
	 */
	protected function startup()
	{
		$this->getHttpResponse()->setHeader('X-Powered-By', 'Nette Framework with Nella');
		parent::startup();
		$this->setLayout('layout');
		$this->template->langIso639 = $this->lang;
		$this->template->webname = Nella\Nella::getOption('webname');
		$this->template->gacode = Nella\Nella::getOption('gacode');
		/*if (empty($this->lang))
			$this->redirect('this', array('lang' => "en"));*/
	}
	
	/**
	 * Get form
	 * 
	 * @param	string	unique form name
	 * @return	Nette\Application\AppForm
	 */
	protected function getForm($name)
	{
		$form = new AppForm($this, $name);
		$form->setRenderer(new Nella\FormRenderer($this->createTemplate()));
		
		return $form;
	}
	
	/**
	 * Get datagrid
	 * 
	 * @param	string	unique datagrid name
	 * @param	Nella\Models\IDataSource
	 * @return	Nette\Extras\DataGrid\DataGrid
	 */
	protected function getDataGrid($name, $datasource = NULL)
	{
		$grid = new \DataGrid();
		$grid->keyName = "id";
		if (!empty($datasource))
			$grid->bindDataTable($datasource);
		$this->addComponent($grid, $name);
		
		return $grid;
	}
	
	/**
	 * Create component dialog
	 * 
	 * @param	string	component name
	 */
	public function createComponentDialog($name)
	{
		$dialog = new Nella\Components\YesNoDialog\YesNoDialog($this, $name);
		return $dialog;
	}

	/**
	 * Create component editor
	 *
	 * @param	string	component name
	 */
	public function createComponentEditor($name)
	{
		$editor = new Nella\Components\Editor\Editor($this, $name);
		return $editor;
	}

	/**
	 * Create Google Analytics component
	 *
	 * @param	string	component name
	 */
	public function createComponentGoogleAnalytics($name)
	{
		$ga = new Nella\Components\GoogleAnalytics\GoogleAnalytics($this, $name);
		return $ga;
	}
	
	/**
	 * Get user identity
	 * 
	 * @return	Nette\Security\IIdentity
	 */
	protected function getIdentity()
	{
		return Nette\Environment::getUser()->getIdentity();
	}
	
	/**
	 * Formats layout template file names.
	 * 
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function formatLayoutTemplateFiles($presenter, $layout)
	{
		$path = str_replace(":", "/", substr($presenter, 0, strrpos($presenter, ":")));
		$subPath = substr($presenter, strrpos($presenter, ":")+1);
		$arr = parent::formatLayoutTemplateFiles($presenter, $layout);
		return array_merge($arr, array(
			LIBS_DIR . "/Nella/Modules/$path/Templates/@$layout.phtml",
			LIBS_DIR . "/Nella/Modules/$path/Templates/$subPath/@$layout.phtml",
			LIBS_DIR . "/Nella/Core/$path/Templates/@$layout.phtml",
			LIBS_DIR . "/Nella/Core/$path/Templates/$subPath/@$layout.phtml",
			LIBS_DIR . "/Nella/Templates/@$layout.phtml"));
	}

	/**
	 * Formats view template file names.
	 * 
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function formatTemplateFiles($presenter, $view)
	{
		$path = str_replace(":", "/", substr($presenter, 0, strrpos($presenter, ":")));
		$subPath = substr($presenter, strrpos($presenter, ":")+1);
		$arr = parent::formatTemplateFiles($presenter, $view);
		return array_merge($arr, array(
			LIBS_DIR . "/Nella/Modules/$path/Templates/$subPath.$view.phtml",
			LIBS_DIR . "/Nella/Modules/$path/Templates/$subPath/$view.phtml",
			LIBS_DIR . "/Nella/Core/$path/Templates/$subPath.$view.phtml",
			LIBS_DIR . "/Nella/Core/$path/Templates/$subPath/$view.phtml"));
	}
}
