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
 * @package    Nella\Core\Media
 */

namespace Nella\Core\Media;

use Nette;
use Nella;
use Nella\Models;
use Nette\Forms\Form;

/**
 * Backend media
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Core\Media
 */
class Backend extends \Nella\Presenters\BackendBase
{
	/**
	 * Get translated privilege from called action
	 * 
	 * @param	string
	 * @return	string
	 */
	protected function getActionTranslatedPrivilege($action)
	{
		$table = array(
			'imageList' => "images",
			'imageDelete' => "images",
			'imageAdd' => "images",
		);
		return $table[$action];
	}
	
	/**
	 * Get translated privilege from called signal
	 * 
	 * @param	string
	 * @return	string
	 */
	protected function getSignalTranslatedPermission($signal)
	{
		$table = array(
			'imageDelete' => "modules",
		);
		return $table[$signal];
	}
	
	/**
	 * Is called action auto allowed
	 * 
	 * @param	string
	 * @return	bool
	 */
	protected function isActionAutoAllow($action)
	{
		return FALSE;
	}
	
	/**
	 * Is called signal auto allowed
	 * 
	 * @param	string
	 * @return	bool
	 */
	protected function isSignalAutoAllow($signal)
	{
		$signals = array('submit');
		return in_array($signal, $signals);
	}
	
	/**
	 * @return	void
	 */
	public function beforeRender()
	{
		$this->template->module = "Media";
		parent::beforeRender();
	}
	
	/**
	 * Create component image add form
	 * 
	 * @param	string	component name
	 */
	public function createComponentImageAddForm($name)
	{
		$form = $this->getForm($name);
		$form->addFile('image', "Image")
			->addRule(Form::FILLED, "Image must be selected")
			->addRule(Form::MIME_TYPE, "File must be image", "image/jpeg,image/gif,image/png")
			->addRule(Form::MAX_FILE_SIZE, "Maximum file size overloaded", 10485760); //TODO: ini_get('upload_max_filesize')
		
		$form->addSubmit('sub', "Upload");
		
		$form->onSubmit[] = array($this, "processImageAddForm");
	}
	
	/**
	 * Action image add
	 */
	public function actionImageAdd()
	{
		$this->template->action = "Add image";
		if ($this->isAjax())
			$this->invalidateControl("content");
	}
	
	/**
	 * Process image add form
	 * 
	 * @param	Nette\Forms\Form	processing form
	 */
	public function processImageAddForm(Form $form)
	{
		$upl = $form['image']->getValue();
		if ($upl->isOk() && $upl->isImage())
		{
			$image = Models\Image::createNew(Models\Image::mime2suffix($upl->getContentType()));
			$image->save();
			if ($upl->move(APP_DIR . "/images/".$image->id.".".$image->suffix))
			{
				$this->flashMessage("Image saved", "ok");
				$this->redirect("imageList");
			}
			else
				$form['image']->addError("Saving error");
		}
		else
			$form['image']->addError("Uploading error");
	}
	
	/**
	 * Action image list
	 */
	public function actionImageList()
	{
		$this->template->action = "Image list";
		$images = Models\Image::findAll();
		if (count($images) > 0)
			$this->template->images = $images;
	}
	
	/**
	 * Action delete image
	 * 
	 * @param	int	image id
	 */
	public function actionImageDelete($id)
	{
		$this->template->action = "Delete image";
		$image = Models\Image::findById($id);
		if (empty($image))
		{
			$this->flashMessage("Image not exist", "error");
			$this->redirect("imageList");
		}
		else
		{
			$this['dialog']->question = "Realy delete image?";
			$this['dialog']->yesLink = $this->link('imageDelete!', array('id' => $id));
		}
		
		if ($this->isAjax())
			$this->terminate(new Nette\Application\JsonResponse($this['dialog']->getData()));
	}
	
	/**
	 * Process image delete signal
	 * 
	 * @param	int	image id
	 * @return	void
	 */
	public function handleImageDelete($id)
	{
		$image = Models\Image::findById($id);
		if (empty($image))
		{
			$this->flashMessage("Image not exist", "error");
			$this->redirect("list");
		}
		else
		{
			$this->deleteImageCacheById($id);
			unlink(APP_DIR . "/images/" . $image->id . "." . $image->suffix);
			$image->destroy();
			$this->flashMessage("Image successfully deleted", "ok");
			$this->redirect("imageList");
		}
	}
	
	/**
	 * Delete image cache by id
	 * 
	 * @param	int
	 * @return	void
	 */
	protected function deleteImageCacheById($id)
	{
		$dir = dir(WWW_DIR . "/images");
		while (($entry = $dir->read()) !== FALSE)
		{
			if (!is_dir(WWW_DIR . "/images/" . $entry) && $entry != "." && $entry != ".." && substr($entry, 0, strlen($id."-")) == $id."-")
			{
				unlink(WWW_DIR . "/images/" . $entry);
			}
		}
	}
}