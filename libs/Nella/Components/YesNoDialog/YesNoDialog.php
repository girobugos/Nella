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
 * @package    Nella\Components\YesNoDialog
 */

namespace Nella\Components\YesNoDialog;

use Nette\Application\Control;
use Nette\IComponentContainer;

/**
 * Yes or No dialog
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Components\YesNoDialog
 */
class YesNoDialog extends Control
{
	/** @var string */
	public $question;
	/** @var string */
	public $yesLink;
	/** @var string */
	public $noLink;
	
	/**
	 * Constructor
	 * 
	 * @param	Nette\IComponentContainer
	 * @param	string	unique name
	 * @param	string
	 * @param	string
	 * @param	string
	 */
	public function __construct(IComponentContainer $parent, $name, $question = NULL, $yesLink = NULL, $noLink = NULL)
	{
		parent::__construct($parent, $name);
		$this->question = $question;
		$this->yesLink = $yesLink;
		$this->noLink = $noLink;
	}
	
	/**
	 * Rented dialog
	 * 
	 * @return	string
	 */
	public function render()
	{
		if (empty($this->question))
			throw new \InvalidStateException("Question not set");
		$this->template->question = $this->question;
		if (empty($this->yesLink))
			throw new \InvalidStateException("Yes link not set");
		$this->template->yesLink = $this->yesLink;$this->template->noLink = empty($this->noLink) ? $this->getPresenter()->link('list') : $this->noLink;
		$this->template->setFile(__DIR__ . "/template.phtml");
		$this->template->render();
	}

	/**
	 * Get data
	 *
	 * @return array
	 */
	public function getData()
	{
		if (empty($this->question))
			throw new \InvalidStateException("Question not set");
		if (empty($this->yesLink))
			throw new \InvalidStateException("Yes link not set");
		
		return array(
			'question' => $this->question,
			'yesLink' => $this->yesLink,
			'noLink' => empty($this->noLink) ? $this->getPresenter()->link('list') : $this->noLink,
			'yesText' => "Yes",
			'noText' => "No");
	}
}