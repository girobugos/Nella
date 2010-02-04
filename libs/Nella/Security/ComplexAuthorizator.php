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
 * @package    Nella\Security
 */

namespace Nella\Security;

use Nella\Models\UserPrivilege;
use Nella\Nella;

/**
 * Access control list (ACL) functionality and privileges management.
 *
 * @author		Patrik Votoček
 * @copyright	Copyright (c) 2008, 2010 Patrik Votoček
 * @package		Nella\Security
 */
class ComplexAuthorizator extends \Nette\Object implements \Nette\Security\IAuthorizator
{
	/** @var array */
	protected $data = array();
	
	/**
	 * Is allowed?
	 * 
	 * @param	int	in nella not exist role, use this as user id (HACK)
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	public function isAllowed($role = NULL, $resource = NULL, $privilege = NULL)
	{
		$userId = $role;
		if (!isset($this->data) || empty($this->data))
			$this->load($userId);
		
		if (is_array($this->data) && isset($this->data[$resource]))
		{
			if (isset($this->data[$resource][$privilege]))
			{
				return (bool)$this->data[$resource][$privilege];
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Load privileges
	 * 
	 * @param	int	user id
	 * @return	void
	 */
	protected function load($userId)
	{
		$cache = Nella::getCache();
		if (isset($cache['userprivileges-'.$userId]))
			$this->data = $cache['userprivileges-'.$userId];
		else
		{
			$this->data = array();
			$data = UserPrivilege::findAll(array(array("[user_id] = %i", $userId)));
			if (count($data) > 0)
			{
				foreach ($data as $value)
				{
					if (!isset($this->data[$value->resource]))
						$this->data[$value->resource] = array();
					$this->data[$value->resource][$value->privilege] = TRUE;
				}
			}
			$cache->save('userprivileges-'.$userId, $this->data, array(
				'expire' => "+2days",
				'tags' => array('userprivileges-'.$userId)
			));
		}
	}
}