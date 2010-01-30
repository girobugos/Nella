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

use Nette;
use Nette\Object;
use Nette\Application\IPresenter;

/**
 * Menu node
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella
 */
class Node extends Object
{
	/** @var string */
	protected $name;
	/** @var string */
	protected $plink;
	/** @var array */
	protected $childs = array();
	/** @var array */
	protected $hiddenChilds = array();
	
	/** @var string */
	protected $resource;
	/** @var string */
	protected $privilege;
	
	
	/**
	 * Construct
	 * 
	 * @param	string	display name
	 * @param	string	nette presenter link format
	 * @param	string
	 * @param	string
	 */
	public function __construct($name, $plink, $resource = NULL, $privilege = NULL)
	{
		if (!empty($name))
			$this->setName($name);
		$this->setPlink($plink);
		$this->resource = $resource;
		$this->privilege = $privilege;
	}
	
	/**
	 * Get name
	 * 
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Set name
	 * 
	 * @param	string	display name
	 * @return	Nella\Node
	 * @throws	ArgumentException
	 */
	protected function setName($name)
	{
		if (is_string($name) && strlen($name) > 1)
			$this->name = $name;
		else
			throw new \ArgumentException("Name must be a valid string");
		
		//Fluent
		return $this;
	}
	
	/**
	 * Get presenter link
	 * 
	 * @return	string
	 */
	public function getPlink()
	{
		return $this->plink;
	}
	
	/**
	 * Set presenter link
	 * 
	 * @param	string	nette presenter link format
	 * @return	Nella\Node
	 * @throws	ArgumentException
	 */
	protected function setPlink($plink)
	{
		if (is_string($plink) && strlen($plink) > 1 && strpos($plink, ':') !== FALSE)
			$this->plink = $plink;
		else
			throw new \ArgumentException("Nette link must be a valid nette link format (example: ':Auth:Frontend:login')");
	}
	
	/**
	 * Get childs
	 * 
	 * @return	array
	 */
	public function getChilds()
	{
		return $this->childs;
	}
	
	/**
	 * Add child item
	 * 
	 * @param	string	display name
	 * @param	string	nette presenter link format
	 * @param	string
	 * @param	string
	 * @return	Nella\Node
	 */
	public function addChild($name, $plink, $resource = NULL, $privilege = NULL)
	{
		return $this->childs[$plink] = new static($name, $plink, $resource, $privilege);
	}
	
	/**
	 * Add hidden child item
	 * 
	 * @param	string	nette presenter link format
	 * @param	string
	 * @param	string
	 * @return	Nella\Node
	 */
	public function addHiddenChild($plink)
	{
		return $this->hiddenChilds[$plink] = new static(NULL, $plink);
	}
	
	/**
	 * If current
	 * 
	 * @param	Nette\Application\IPresenter	presenter
	 * @return	bool
	 */
	public function ifCurrent(IPresenter $presenter)
	{
		$presenter->link($this->plink);
		$presenterRequest = $presenter->lastCreatedRequest;
		$params = $presenterRequest->getParams();
		$action = $params['action'];
		unset($params);
		if ($presenterRequest->getPresenterName() == $presenter->getRequest()->getPresenterName() && $action == $presenter->getAction())
			return TRUE;
		else
		{
			if (count($this->childs) > 0)
			{
				foreach ($this->childs as $child)
				{
					if ($child->ifCurrent($presenter))
						return TRUE;
				}
			}
			if (count($this->hiddenChilds) > 0)
			{
				foreach ($this->hiddenChilds as $child)
				{
					if ($child->ifCurrent($presenter))
						return TRUE;
				}
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Has childs
	 * 
	 * @return	bool
	 */
	public function hasChilds()
	{
		if (count($this->childs) > 0)
			return TRUE;
		
		return FALSE;
	}
	
	/**
	 * Is adllowed
	 * 
	 * @return	bool
	 */
	public function isAllowed()
	{
		return Nette\Environment::getUser()->isAllowed($this->resource, $this->privilege);
	}
}
