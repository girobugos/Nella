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
 * @package    Nella\Modules\Page\Models
 */

namespace Nella\Modules\Page\Models;

use dibi;
use Nette;
use Nella\Nella;

/**
 * Pages archive model for page module
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Modules\Page\Models
 * @property-read	int	$id
 * @property	string		$name
 * @property	string		$slug
 * @property	string		$text
 * @property	string		$keywords
 * @property	string		$description
 * @property	int			$revision
 * @property	DateTime	$datetime
 * @belongsTo(Nella\Modules\Page\Models\Page)
 */
class PageArchive extends \ActiveRecord
{
	/** @var string */
	protected static $table = 'pagesarchive';
	/** @var string */
	protected static $primary = 'id';
	
	/**
	 * Create page archive
	 * 
	 * @param	int
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	int
	 * @param	string
	 * @param	int
	 * @return	Nella\Modules\Page\Models\PageArchive
	 */
	public static function createNew($pageId, $name, $slug, $text, $keywords, $description, $revision = 1, $datetime = NULL, $userId = NULL)
	{
		if (empty($datetime))
			$datetime = date("Y-m-d H:i:s");
		if (empty($userId))
			$userId = Nella::getUser()->id;

		return static::create(array(
			'page_id' => $pageId,
			'name' => $name,
			'slug' => $slug,
			'text' => $text,
			'keywords' => $keywords,
			'description' => $description,
			'revision' => $revision,
			'datetime' => $datetime,
			'user_id' => $userId
		));
	}
	
	/**
	 * Delete all by page id
	 * 
	 * @param	int	page id
	 */
	public static function deleteAllByPageId($pageId)
	{
		dibi::delete(static::$table)->where("[page_id] = %i", $pageId)->execute();
	}
	
	/**
	 * Delete all previous revision
	 * 
	 * @param	int	page id
	 * @param	int	revision
	 */
	public static function deleteAllPreviousRevision($pageId, $revision)
	{
		dibi::delete(static::$table)->where("[page_id] = %i ", $pageId, "AND [revision] > %i", $revision)->execute();
	}
	
	/**
	 * Install table
	 */
	public static function install()
	{
		dibi::query(
			"CREATE TABLE IF NOT EXISTS [:prefix:".static::$table."] (
			  [id] INT(11) NOT NULL AUTO_INCREMENT ,
			  [page_id] INT(11) NOT NULL ,
			  [name] VARCHAR(128) NOT NULL ,
			  [slug] VARCHAR(128) NOT NULL ,
			  [text] TEXT NULL DEFAULT NULL ,
			  [keywords] TEXT NULL DEFAULT NULL ,
			  [description] TEXT NULL DEFAULT NULL ,
			  [revision] INT(11) NULL DEFAULT '1' ,
			  [datetime] DATETIME NOT NULL ,
			  [user_id] INT(11) NOT NULL ,
			  PRIMARY KEY ([id]) ,
			  INDEX [fk_pagesarchive_pages1] ([page_id] ASC) ,
			  INDEX [fk_pagesarchive_users1] ([user_id] ASC) ,
			  CONSTRAINT [fk_pagesarchive_pages1]
			    FOREIGN KEY ([page_id] )
			    REFERENCES [:prefix:pages] ([id] )
			    ON DELETE NO ACTION
			    ON UPDATE NO ACTION,
			  CONSTRAINT [fk_pagesarchive_users1]
			    FOREIGN KEY ([user_id] )
			    REFERENCES [:prefix:users] ([id] )
			    ON DELETE NO ACTION
			    ON UPDATE NO ACTION)
			ENGINE = InnoDB
			AUTO_INCREMENT = 1
			DEFAULT CHARACTER SET = utf8;");
	}
	
	/**
	 * Uninstall table
	 */
	public static function uninstall()
	{
		dibi::query("DROP TABLE IF EXISTS [:prefix:".static::$table."];");
	}
}