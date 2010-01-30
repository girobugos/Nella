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
 * User tokens model
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Core\Auth\Models
 * @property-read	int	$id
 * @property	string		$key
 * @property	DateTime	$created
 * @property	int			$type
 * @belongsTo(Nella\Core\Auth\Models\User)
 */
class UserToken extends \ActiveRecord
{
	/**#@+ type codes */
	const TYPE_ACTIVATION = 0;
	const TYPE_AUTOLOGIN = 1;
	const TYPE_LOSTPASSWORD = 9;
	/**#@-*/

	/** @var string */
	protected static $table = 'usertokens';
	/** @var string */
	protected static $primary = 'id';

	/**
	 * Create user
	 *
	 * @param	int
	 * @param	string
	 * @param	int	type code
	 * @return	Nella\Core\Auth\Models\UserToken
	 */
	public static function createNew($userId, $key, $type)
	{
		return static::create(array(
			'user_id' => $userId,
			'key' => $key,
			'type' => $type,
			'created' => date("Y-m-d H:i:s")
			));
	}
}