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

use Nette;
use Nella\Nella;
use Nette\Forms\Form;

/**
 * Page module backend presenter
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Modules\Page
 */
class Backend extends \Nella\Presenters\BackendBase
{
	/**
	 * @return	void
	 */
	public function beforeRender()
	{
		$this->template->module = "Pages";
		parent::beforeRender();
	}
	
	/**
	 * Create component list data grid
	 * 
	 * @param	string	component name
	 */
	public function createComponentListDataGrid($name)
	{
		$grid = $this->getDataGrid($name, Models\Page::getDataSource());
		$grid->addColumn('name', "Name")->addFilter();
		$grid->addColumn('lang', "Lang")->addSelectboxFilter();
		$grid['lang']->getHeaderPrototype()->addStyle('width: 30px');
		$grid['lang']->getCellPrototype()->addStyle('text-align: center');
		$grid->addActionColumn('Actions')->getHeaderPrototype()->addStyle('width: 98px');
		$grid->addAction("Delete", "delete", NULL, "ajax-dialog");
		if (Nette\Environment::getUser()->isAllowed('Page', "publish"))
		{
			$grid->addAction("Publish", "publish", NULL, "ajax-dialog")->ifCallback = function ($data) { if ($data['status'] == Models\Page::STATUS_PUBLIC) return FALSE; else return TRUE; };
			$grid->addAction("Unpublish", "unpublish", NULL, "ajax-dialog")->ifCallback = function ($data) { if ($data['status'] == Models\Page::STATUS_CONCEPT) return FALSE; else return TRUE; };
		}
		$grid->addAction("Edit", "edit", NULL, "ajax-popup");
	}
	
	/**
	 * Action list
	 */
	public function actionList()
	{
		$this->template->action = "List";
	}
	
	/**
	 * Create component form
	 * 
	 * @param	string	component name
	 */
	public function createComponentAddForm($name)
	{
		$form = $this->getForm($name);
		$form->addText('name', "Name: ")
			->addRule(Form::FILLED, "Name must be filled");
		$form->addText('keywords', "Keywords: ");
		$form->addText('description', "Description: ");
		$form->addText('slug', "Slug: ")
			->addCondition(Form::FILLED)
				->addRule(Form::REGEXP, "Slug must be in valid format", "/^[a-z0-9-]*$/i");
		$form->addTextarea('text', "Text: ")
			->addRule(Form::FILLED, "Text must be filled")
			->getControlPrototype()->class("editor");
		$form->addSelect('lang', "Language: ", array('en' => "English"));
		
		$form->addSubmit('sub', "Save as concept");
		if (Nette\Environment::getUser()->isAllowed('Page', "publish"))
			$form->addSubmit('publish', "Publish");
		
		$form->setDefaults(array('lang' => $this->lang));
			
		$form->onSubmit[] = array($this, "processAddForm");
	}
	
	/**
	 * Process add form
	 * 
	 * @param	\Nette\Forms\Form	$form
	 */
	public function processAddForm(Form $form)
	{
		$values = $form->getValues();
		
		if (!empty($values['slug']) && Models\Page::count("[slug] = '" . $values['slug'] . "'"))
			$form['slug']->addError("This slug is exist");
		else
		{
			$slug = $values['slug'];
			if (empty($values['slug']))
			{
				$slug = $slugPrototipe = Nette\String::webalize($values['name']);
				$i = 0;
				while (Models\Page::count("[slug] = '" . $slug . "'") > 0)
				{
					$slug = $slugPrototipe . "-" . $i;
					$i++;
				}
			}
			
			if ($form['publish']->isSubmittedBy())
				Models\Page::createNew($values['name'], $values['lang'], $slug, $values['text'], $values['keywords'], $values['description'], 1, Models\Page::STATUS_PUBLIC);
			else
				Models\Page::createNew($values['name'], $values['lang'], $slug, $values['text'], $values['keywords'], $values['description'], 1, Models\Page::STATUS_CONCEPT);
			$this->flashMessage("New page saved", 'ok');
			$this->redirect(":Page:Backend:list");
		}
	}
	
	/**
	 * Action add
	 */
	public function actionAdd()
	{
		$this->template->action = "Add";
		if ($this->isAjax())
			$this->invalidateControl("content");
	}
	
	/**
	 * Action delete
	 * 
	 * @param	int	page id
	 */
	public function actionDelete($id)
	{
		$this->template->action = "Delete";
		$page = Models\Page::findById($id);
		if (empty($page))
		{
			$this->flashMessage("Page not exist", "error");
			$this->redirect("list");
		}
		else
		{
			$this['dialog']->question = "Realy delete '".$page->name."'?";
			$this['dialog']->yesLink = $this->link('delete!', array('id' => $id));
		}

		if ($this->isAjax())
			$this->terminate(new Nette\Application\JsonResponse($this['dialog']->getData()));
	}
	
	/**
	 * Process delete signal
	 * 
	 * @param	int	page id
	 */
	public function handleDelete($id)
	{
		$page = Models\Page::findById($id);
		if (empty($page))
		{
			$this->flashMessage("Page not exist", "error");
			$this->redirect("list");
		}
		else
		{
			$page->destroy();
			$this->flashMessage("Page successfully deleted", "ok");
			$this->redirect("list");
		}
	}
	
	/**
	 * Action publish
	 * 
	 * @param	int	page id
	 */
	public function actionPublish($id)
	{
		$this->template->action = "Publish";
		$page = Models\Page::findById($id);
		if (empty($page))
		{
			$this->flashMessage("Page not exist", "error");
			$this->redirect("list");
		}
		elseif ($page->status == Models\Page::STATUS_PUBLIC)
		{
			$this->flashMessage("Page is public", "warning");
			$this->redirect("list");
		}
		else
		{
			$this['dialog']->question = "Realy publish '".$page->name."'?";
			$this['dialog']->yesLink = $this->link('publish!', array('id' => $id));
		}

		if ($this->isAjax())
			$this->terminate(new Nette\Application\JsonResponse($this['dialog']->getData()));
	}
	
	/**
	 * Process publish signal
	 * 
	 * @param	int	page id
	 */
	public function handlePublish($id)
	{
		$page = Models\Page::findById($id);
		if (empty($page))
		{
			$this->flashMessage("Page not exist", "error");
			$this->redirect("list");
		}
		elseif ($page->status == Models\Page::STATUS_PUBLIC)
		{
			$this->flashMessage("Page is public", "warning");
			$this->redirect("list");
		}
		else
		{
			$page->status = Models\Page::STATUS_PUBLIC;
			$page->save();
			$this->flashMessage("Page marked as pubic", "ok");
			$this->redirect("list");
		}
	}
	
	/**
	 * Action unpublish
	 * 
	 * @param	int	page id
	 */
	public function actionUnpublish($id)
	{
		$this->template->action = "Unpublish";
		$page = Models\Page::findById($id);
		if (empty($page))
		{
			$this->flashMessage("Page not exist", "error");
			$this->redirect("list");
		}
		elseif ($page->status == Models\Page::STATUS_CONCEPT)
		{
			$this->flashMessage("Page is concept", "warning");
			$this->redirect("list");
		}
		else
		{
			$this['dialog']->question = "Realy marked as concept '".$page->name."'?";
			$this['dialog']->yesLink = $this->link('unpublish!', array('id' => $id));
		}

		if ($this->isAjax())
			$this->terminate(new Nette\Application\JsonResponse($this['dialog']->getData()));
	}
	
	/**
	 * Process unpublish signal
	 * 
	 * @param	int	page id
	 */
	public function handleUnpublish($id)
	{
		$page = Models\Page::findById($id);
		if (empty($page))
		{
			$this->flashMessage("Page not exist", "error");
			$this->redirect("list");
		}
		elseif ($page->status == Models\Page::STATUS_CONCEPT)
		{
			$this->flashMessage("Page is concept", "warning");
			$this->redirect("list");
		}
		else
		{
			$page->status = Models\Page::STATUS_CONCEPT;
			$page->save();
			$this->flashMessage("Page marked as concept", "ok");
			$this->redirect("list");
		}
	}
	
	/**
	 * Create component edit form
	 * 
	 * @param	string	component name
	 */
	public function createComponentEditForm($name)
	{
		$form = $this->getForm($name);
		$form->addHidden('id');
		$form->addText('name', "Name: ")
			->addRule(Form::FILLED, "Name must be filled");
		$form->addText('keywords', "Keywords: ");
		$form->addText('description', "Description: ");
		$form->addText('slug', "Slug: ")
			->addCondition(Form::FILLED)
				->addRule(Form::REGEXP, "Slug must be in valid format", "/^[a-z0-9-]*$/i");
		$form->addTextarea('text', "Text: ")
			->addRule(Form::FILLED, "Text must be filled")
			->getControlPrototype()->class("editor");
		
		$form->addSubmit('sub', "Save");
		
		$form->onSubmit[] = array($this, "processEditForm");
	}
	
	/**
	 * Process edit form
	 * 
	 * @param	Nette\Forms\Form	processing form
	 */
	public function processEditForm(Form $form)
	{
		$page = Models\Page::findById($form['id']->getValue());
		if (empty($page))
		{
			$this->flashMessage("Page not exist", "error");
			$this->redirect("list");
		}
		else
		{
			$page->name = $form['name']->getValue();
			$page->keywords = $form['keywords']->getValue();
			$page->description = $form['description']->getValue();
			$page->text = $form['text']->getValue();
			$page->save();
			$this->flashMessage("Page changes saved", "ok");
			$this->redirect('list');
		}
	}
	
	/**
	 * Create component
	 * 
	 * @param	string	component name
	 */
	public function createComponentRevisionListDataGrid($name)
	{
		$grid = $this->getDataGrid($name);
		$grid->addColumn('revision', "Revision")->addFilter();
		$grid['revision']->addDefaultSorting('desc');
		$grid->addColumn('datetime', "Date & Time")->addFilter();
		$grid->addColumn('username', "Username")->addSelectboxFilter();
		$grid->addActionColumn('Actions')->getHeaderPrototype()->addStyle('width: 98px');
		$grid->addAction("Back to this revision", "backToRevision", NULL, "ajax-dialog");
	}
	
	/**
	 * Action edit
	 * 
	 * @param	int	page id
	 */
	public function actionEdit($id)
	{
		$this->template->action = "Edit";
		$page = Models\Page::findById($id);
		if (empty($page))
		{
			$this->flashMessage("Page not exist", "error");
			$this->redirect("list");
		}
		else
		{
			$this['editForm']->setDefaults(array(
				'id' => $id,
				'name' => $page->name,
				'keywords' => $page->keywords,
				'description' => $page->description,
				'slug' => $page->slug,
				'text' => $page->text
			));
			$this['revisionListDataGrid']->bindDataTable(Models\Page::getArchiveDataSource($id));
		}
		
		if ($this->isAjax())
			$this->invalidateControl("content");
	}
	
	/**
	 * Action back to revision
	 * 
	 * @param	int page archive id
	 */
	public function actionBackToRevision($id)
	{
		$this->template->action = "Back to revision";
		$pageArchive = Models\PageArchive::findById($id);
		if (empty($pageArchive))
		{
			$this->flashMessage("Archive version page not exist", "error");
			$this->redirect("list");
		}
		else
		{
			$page = Models\Page::findById($pageArchive->pageId);
			if ($page->revision == $pageArchive->revision)
			{
				$this->flashMessage("This revision allready use", "warning");
				$this->redirect("this");
			}
			else
			{
				$this['dialog']->question = "Realy '".$page->name."' back to revision from '".$pageArchive->datetime."'?";
				$this['dialog']->yesLink = $this->link('backToRevision!', array('id' => $id));
			}
		}
		
		if ($this->isAjax())
			$this->terminate(new Nette\Application\JsonResponse($this['dialog']->getData()));
	}
	
	/**
	 * Process signal back to revision
	 * 
	 * @param	int page archive id
	 */
	public function handleBackToRevision($id)
	{
		$this->template->action = "Back to revision";
		$pageArchive = Models\PageArchive::findById($id);
		if (empty($pageArchive))
		{
			$this->flashMessage("Archive version page not exist", "error");
			$this->redirect("list");
		}
		else
		{
			$page = Models\Page::findById($pageArchive->pageId);
			if ($page->revision == $pageArchive->revision)
			{
				$this->flashMessage("This revision allready use", "warning");
				$this->redirect("this");
			}
			else
			{
				$page->name = $pageArchive->name;
				$page->keywords = $pageArchive->keywords;
				$page->description = $pageArchive->description;
				$page->slug = $pageArchive->slug;
				$page->text = $pageArchive->text;
				$page->revision = $pageArchive->revision;
				$page->save();
				Models\PageArchive::deleteAllPreviousRevision($pageArchive->pageId, $pageArchive->revision);
				$this->flashMessage("Reverting to revision complete", 'ok');
				$this->redirect('list');
			}
		}
	}
}