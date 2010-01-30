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
 * @package    Nella
 */

namespace Nella;

use Nette\Forms\Form;
use Nette\Forms\ConventionalRenderer;
use Nette\Templates\ITemplate;

require_once __DIR__ . "/FormClientScript.php";

/**
 * Nella styled form renderer
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella
 */
class FormRenderer extends ConventionalRenderer
{
	protected $template;
	
	/**
	 * Constructor
	 */
	public function __construct(ITemplate $template)
	{
		$this->template = $template;
	}
	
	/**
	 * Provides complete form rendering.
	 * 
	 * @param  Form
	 * @param  string
	 * @return string
	 */
	public function render(Form $form, $mode = NULL)
	{
		if ($this->form !== $form) {
			$this->form = $form;
			$this->init();
		}
		
		if (empty($mode))
		{
			$this->template->form = $this->form;
			$this->template->setFile(__DIR__ . "/Templates/@forms.phtml");
			return (string)$this->template;
		}
		else
			return parent::render($form, $mode);
	}
	
	/**
	 * Returns JavaScript handler.
	 * 
	 * @return mixed
	 */
	public function getClientScript()
	{
		if ($this->clientScript === TRUE) {
			$this->clientScript = new FormClientScript($this->form);
		}
		return $this->clientScript;
	}
}