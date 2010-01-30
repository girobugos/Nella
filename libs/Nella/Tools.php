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

/**
 * Tools
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella
 */
abstract class Tools extends \Nette\Object
{
	/**
	 * Hash password
	 * 
	 * @param	string	raw password
	 * @return	string
	 */
	public static function hash($password)
	{
		return sha1("nella".$password);
	}
	
	/**
	 * Underscore string to upper string
	 * 
	 * example: this_functio -> thisFunction
	 * 
	 * @param	string
	 * @return	string
	 */
	public static function underscoreToUpper($s)
	{
		return preg_replace_callback('~_([a-z])~', function($m) {return strtoupper($m[1]);}, $s);
	}
	
	/**
	 * Upper string to underscore string
	 * 
	 * example: thisFunction -> this_function
	 * 
	 * @param	string
	 * @return	string
	 */
	public static function upperToUnderscore($s)
	{
		return preg_replace_callback('~([A-Z])~', function($m) {return '_' . strtolower($m[1]);}, $s);
	}
	
	/**
     * Get random generated string
     *
     * @param int $length
     * @param string $base
     * @return string
     */
    public static function getRandomString($length, $base = "abcdefghjkmnpqrstwxyz0123456789")
    {
		$max = strlen($base)-1;
		$key = "";
		
		mt_srand((double)microtime()*1000000);
		while (strlen($key) < $length)
			$key .= $base[mt_rand(0,$max)];
		
		return $key;
    }
}