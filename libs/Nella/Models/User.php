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
 * @package    Nella\Models
 */

namespace Nella\Models;

use Nella;

/**
 * User model
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Models
 * @property-read	int	$id
 * @property	string	$username
 * @property	string	$password
 * @property	string	$mail
 * @property	int		$status
 * @hasMany(Nella\Models\UserToken)
 * @hasMany(Nella\Models\UserPrivilege)
 */
class User extends \ActiveRecord
{
	/**#@+ status codes */
	const STATUS_UNACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_BANNED = 2;
	const STATUS_SUSPENDED = 3;
	/**#@-*/

	/** @var string */
	protected static $table = 'users';
	/** @var string */
	protected static $primary = 'id';

	/**
	 * Create user
	 *
	 * @param	string
	 * @param	string	hashed password
	 * @param	string
	 * @param	int		status code
	 * @return Nella\Core\Auth\Models\Items\User
	 */
	public static function createNew($username, $password, $mail, $status/*, $role*/)
	{
		return static::create(array(
			'username' => $username,
			'password' => $password,
			'mail' => $mail,
			'status' => $status
			));
	}

	/**
	 * Password verifycator
	 *
	 * @param	string	raw password
	 * @return	bool
	 */
	public function verifyPassword($password)
	{
		if ($this->password == Nella\Tools::hash($password))
			return TRUE;

		return FALSE;
	}
	
	/**
	 * Get privileges
	 * 
	 * @return	array
	 */
	public function getPrivileges()
	{
		return UserPrivilege::findAll(array("[user_id] = " . $this->id));
	}

	/**
	 * Get privilege
	 *
	 * @param	string
	 * @param	string
	 * @return	Nella\Core\Auth\Models\UserPrivilege|NULL
	 */
	public function getPrivilege($resource, $privilege)
	{
		return UserPrivilege::find(array(
			array("[user_id] = %i", $this->id),
			array("[resource] = %s", $resource),
			array("[privilege] = %s", $privilege)));
	}

	/**
	 * Add use privilege
	 * 
	 * @param	string
	 * @param	string
	 * @return	Nella\Core\Auth\Models\UserPrivilege
	 */
	public function addPrivilege($resource, $privilege)
	{
		return UserPrivilege::createNew($this->id, $resource, $privilege);
	}

	/**
	 * Get data source for list data grid
	 *
	 * @return	DibiDataSource
	 */
	public static function getDataSource()
	{
		return parent::getDataSource("SELECT [id],[username],[mail],[status] FROM [".static::$table."]");
	}
}