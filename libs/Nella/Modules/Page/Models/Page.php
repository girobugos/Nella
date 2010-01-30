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
use Nella\Core\Auth\Models\User;

/**
 * Page model for page module
 *
 * @author		Patrik Votoček
 * @copyright	Copyright (c) 2006, 2010 Patrik Votoček
 * @package		Nella\Modules\Page\Models
 * @property-read	int	$id
 * @property	string	$name
 * @property	string	$slug
 * @property	string	$text
 * @property	string	$keywords
 * @property	string	$description
 * @property	int		$revision
 * @property	int		$status
 * @hasMany(Nella\Modules\Page\Models\PageArchive)
 */
class Page extends \ActiveRecord
{
	/**#@+ status codes */
	const STATUS_CONCEPT = 0;
	const STATUS_PUBLIC = 1;
	/**#@-*/
	
	/** @var string */
	protected static $table = 'pages';
	/** @var string */
	protected static $primary = 'id';

	/** @var bool */
	public $tempIsNew = FALSE;
	
	/**
	 * Get all slugs array
	 * 
	 * @return	array
	 */
	public static function getAllSlugsArray()
	{
		$res = dibi::select('[id],[slug]')->from(static::$table)->execute();
		if ($res->count() > 0)
			return $res->fetchPairs();
			
		return array();
	}
	
	/**
	 * Create page
	 *
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	int
	 * @param	int
	 * @return	Nella\Modules\Page\Models\Page
	 */
	public static function createNew($name, $lang, $slug, $text, $keywords, $description, $revision = 1, $status = 0)
	{
		return static::create(array(
			'name' => $name,
			'lang' => $lang,
			'slug' => $slug,
			'text' => $text,
			'keywords' => $keywords,
			'description' => $description,
			'revision' => $revision,
			'status' => $status,
		));
	}
	
	/**
	 * Get data source for list data grid
	 * 
	 * @return	DibiDataSource
	 */
	public static function getDataSource()
	{
		return parent::getDataSource("SELECT [id],[name],[lang],[status] FROM [".static::$table."]");
	}

	/**
	 * Get data source for archive data grid
	 *
	 * @param	int	page id
	 * @return	DibiDataSource
	 */
	public static function getArchiveDataSource($pageId)
	{
		return \dibi::dataSource("SELECT [".PageArchive::getTableName()."].*, [".User::getTableName()."].[username]"
			." FROM [".PageArchive::getTableName()."]"
			." INNER JOIN [".User::getTableName()."] ON [".User::getTableName()."].[id] = [".PageArchive::getTableName()."].[user_id]"
			." WHERE [".PageArchive::getTableName()."].[page_id] = %i", $pageId);
	}
	
	/**
	 * Install table
	 */
	public static function install()
	{
		dibi::query(
			"CREATE TABLE IF NOT EXISTS [:prefix:".static::$table."] (
			  [id] INT(11) NOT NULL AUTO_INCREMENT ,
			  [name] VARCHAR(128) NOT NULL ,
			  [lang] VARCHAR(5) NOT NULL DEFAULT 'en' ,
			  [slug] VARCHAR(128) NOT NULL ,
			  [text] TEXT NULL DEFAULT NULL ,
			  [keywords] TEXT NULL DEFAULT NULL ,
			  [description] TEXT NULL DEFAULT NULL ,
			  [revision] INT(11) NOT NULL DEFAULT '1' ,
			  [status] TINYINT(4) NULL DEFAULT NULL ,
			  PRIMARY KEY ([id]) ,
			  UNIQUE INDEX [slug_UNIQUE] ([slug] ASC) )
			ENGINE = InnoDB
			AUTO_INCREMENT = 1
			DEFAULT CHARACTER SET = utf8;");
		PageArchive::install();
	}
	
	/**
	 * Uninstall table
	 */
	public static function uninstall()
	{
		PageArchive::uninstall();
		dibi::query("DROP TABLE IF EXISTS [:prefix:".static::$table."];");
	}

	/**
	 * Before save
	 *
	 * @param Nella\Modules\Page\Models\Page
	 */
	public static function beforeSave(Page $sender)
	{
		$changes = $sender->getChanges();
		if ($sender->isExistingRecord() && !isset($changes['status']))
		{
			$sender->revision++;
			PageArchive::createNew($sender->id, $sender->name, $sender->slug, $sender->text, $sender->keywords, $sender->description, $sender->revision);
		}

		$sender->tempIsNew = $sender->isNewRecord();
	}
	
	/**
	 * After save
	 *
	 * @param Nella\Modules\Page\Models\Page
	 */
	public static function afterSave(Page $sender)
	{
		if ($sender->tempIsNew === TRUE)
			PageArchive::createNew($sender->id, $sender->name, $sender->slug, $sender->text, $sender->keywords, $sender->description);
	}
	
	/**
	 * Before destroy
	 * 
	 * @param Nella\Modules\Page\Models\Page
	 */
	public static function beforeDestroy(Page $sender)
	{
		PageArchive::deleteAllByPageId($sender->id);
	}
}
