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
use Nella\Models;

/**
 * Frontend media
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Core\Media
 */
class Frontend extends \Nella\Presenters\Base
{
	/**
	 * Action load
	 */
	public function actionLoad($id, $width, $height, $suffix)
	{
		$image = Models\Image::findById($id);
		if (!empty($image) && $image->suffix == $suffix)
		{
			$file = Nette\Image::fromFile(APP_DIR . "/images/" . $image->id . "." . $image->suffix);
			$file->resize($width, $height)->save(WWW_DIR . "/images/" . $image->id . "-" . $width . "x" . $height . "." . $image->suffix);
			$this->redirect("this");
		}
		else
		{
			//TODO: 404
		}
	}
}