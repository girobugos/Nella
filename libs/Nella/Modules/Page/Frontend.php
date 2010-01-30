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
 * @package    Nella\Modules\Page
 */

namespace Nella\Modules\Page;

/**
 * Frontend of page module
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Modules\Page
 */
class Frontend extends \Nella\Presenters\Base
{
	/**
	 * Action default
	 */
	public function actionDefault($slug)
	{
		$page = Models\Page::findBySlug($slug);
		if (!empty($page) && $page->status == Models\Page::STATUS_PUBLIC && $page->lang == $this->lang)
		{
			$this->template->name = $this->template->title = $page->name;
			$this->template->data = $page->text;
			if (!empty($this->description))
				$this->template->description = $this->description;
			if (!empty($this->keywords))
				$this->template->keywords = $this->keywords;
		}
		else
		{
			$this->template->title = "Oops!";
			$this->template->name = "404 - Not found";
			$this->template->data = "<p>Page not exist</p>";
		}
		
		$pages = Models\Page::findAll(array(
			array("[lang] = %s", $this->lang),
			array("[status] = %i", Models\Page::STATUS_PUBLIC)));
		if (count($pages) > 0)
			$this->template->pages = $pages;
	}
}
