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
 * @package    Nella\Core\Auth\Models
 */

namespace Nella\Core\Auth\Models;

/**
 * User privileges model
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Core\Auth\Models
 * @property-read	int $id
 * @property	string	$resource
 * @property	string	$privilege
 * @belongsTo(Nella\Core\Auth\Models\User)
 */
class UserPrivilege extends \ActiveRecord
{
	/** @var string */
	protected static $table = 'userprivileges';
	/** @var string */
	protected static $primary = 'id';

	/**
	 * Create user privilege
	 *
	 * @param	int		user id
	 * @param	string
	 * @param	string
	 * @return	Nella\Core\Auth\Models\UserPrivilege
	 */
	public static function createNew($userId, $resource, $privilege)
	{
		return static::create(array(
			'user_id' => $userId,
			'resource' => $resource,
			'privilege' => $privilege,
			));
	}
}