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

/**
 * Option model
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Models
 * @property-read	int	$id
 * @property	string	$key
 * @property	string	$value
 */
class Option extends \ActiveRecord
{
	/** @var string */
	protected static $table = 'options';
	/** @var string */
	protected static $primary = 'id';
}