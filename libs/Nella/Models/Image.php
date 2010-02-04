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

use Nette;

/**
 * Image model
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Core\Media\Models
 * @property-read	int	$id
 * @property	string		$suffix
 * @property	DateTime	$datetime
 * @belongsTo(Nella\Models\User)
 */
class Image extends \ActiveRecord
{
	/** @var string */
	protected static $table = 'images';
	/** @var string */
	protected static $primary = 'id';
	
	/**
	 * Mime to suffix (without dot)
	 * 
	 * @param string $mime
	 * @return string
	 */
	public static function mime2suffix($mime)
	{
		switch ($mime)
		{
			case 'image/jpeg':
				return "jpg";
			case 'image/gif':
				return "gif";
			case 'image/png':
				return "png";
			default:
				return;
		}
	}
	
	/**
	 * Suffix (without dot) to mime
	 * 
	 * @param string $suffix
	 * @return string
	 */
	public static function suffix2mime($suffix)
	{
		switch ($suffix)
		{
			case 'jpg':
				return "image/jpeg";
			case 'gif':
				return "image/gif";
			case 'png':
				return "image/png";
			default:
				return;
		}
	}
	
	/**
	 * Create image
	 * 
	 * @param	string
	 * @param	string
	 * @param	int
	 * @return	Nella\Core\Media\Models\Image
	 */
	public static function createNew($suffix, $datetime = NULL, $userId = NULL)
	{
		if (empty($datetime))
			$datetime = date("Y-m-d H:i:s");
		if (empty($userId))
			$userId = User::findByUsername(Nette\Environment::getUser()->getIdentity()->name)->id;

		return static::create(array(
			'suffix' => $suffix,
			'datetime' => $datetime,
			'user_id' => $userId,
		));
	}
}