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

use Nella\Core\Auth\Models\User;
use Nella\Core\Auth\Models\UserToken;
use Nette\Security\Identity;
use Nette\Security\AuthenticationException;

/**
 * Complex Authenticator
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Security
 */
class ComplexAuthenticator implements \Nette\Security\IAuthenticator
{
	/**
	 * Performs an authentication
	 * 
	 * @param	array
	 * @return	void
	 * @throws	AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		if ($credentials['extra'] == "auto")
		{
			$user = User::findByUsername($credentials[self::USERNAME]);
			if (empty($user))
				throw new AuthenticationException("User with this username not registered", self::IDENTITY_NOT_FOUND);
			
			$isTokenOk = FALSE;
			$tokens = UserToken::find(array("[user_id] = " . $user->id, "[type] = " . UserToken::TYPE_AUTOLOGIN));
			if (count($tokens) > 0)
			{
				foreach ($tokens as $token)
				{
					if ($credentials[self::PASSWORD] == $token->key)
						$isTokenOk = TRUE;
				}
			}
			if (!$isTokenOk)
				throw new AuthenticationException("Invalid token key", self::INVALID_CREDENTIAL);
		}
		else
		{
			if (strpos($credentials[self::USERNAME], "@") !== FALSE)
			{
				$user = User::findByMail($credentials[self::USERNAME]);
				if (empty($user))
					throw new AuthenticationException("User with this e-mail not registered", self::IDENTITY_NOT_FOUND);
			}
			else
			{
				$user = User::findByUsername($credentials[self::USERNAME]);
				if (empty($user))
					throw new AuthenticationException("User with this username not registered", self::IDENTITY_NOT_FOUND);
			}
			
			if ($user->verifyPassword($credentials[self::PASSWORD]) == FALSE)
				throw new AuthenticationException("Invalid password", self::INVALID_CREDENTIAL);
		}
		
		if ($user->status == User::STATUS_UNACTIVE)
			throw new AuthenticationException("User is not active");
		if ($user->status == User::STATUS_BANNED)
			throw new AuthenticationException("User is not banned");
			
		return new Identity($user->username, array($user->id));
	}
}