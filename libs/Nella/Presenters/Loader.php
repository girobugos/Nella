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
 * @package    Nella\Presenters
 */

namespace Nella\Presenters;

use Nette;
use Nette\Environment;
use Nette\Application\InvalidPresenterException;

/**
 * Presenter Loader
 *
 * @author     Patrik Votoček
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @package    Nella\Presenters
 */
class Loader extends Nette\Object implements Nette\Application\IPresenterLoader
{
	/** @var bool */
	public $caseSensitive = FALSE;
	/** @var string */
	protected $baseDir;
	/** @var array */
	protected $cache = array();

	/**
	 * @param  string
	 */
	public function __construct($baseDir)
	{
		$this->baseDir = $baseDir;
	}

	/**
	 * @param	string	presenter name
	 * @return	string
	 * @throws	InvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (isset($this->cache[$name]))
		{
			list($class, $name) = $this->cache[$name];
			return $class;
		}

		if (!is_string($name) || !preg_match("#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#", $name))
			throw new InvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");

		$class = $this->formatPresenterClass($name);

		if (!class_exists($class))
		{
			// internal autoloading
			$file = $this->formatPresenterFile($name);
			if (is_file($file) && is_readable($file))
			{
				Nette\Loaders\LimitedScope::load($file);
			}

			if (!class_exists($class))
			{
				$class = $this->formatPresenterClass($name, "module");
				
				if (!class_exists($class))
				{
					// internal autoloading
					$file = $this->formatPresenterFile($name, "module");
					if (is_file($file) && is_readable($file))
					{
						Nette\Loaders\LimitedScope::load($file);
					}
					
					if (!class_exists($class))
					{
						$class = $this->formatPresenterClass($name, "core");
						if (!class_exists($class))
						{
							// internal autoloading
							$file = $this->formatPresenterFile($name, "core");
							if (is_file($file) && is_readable($file))
							{
								Nette\Loaders\LimitedScope::load($file);
							}

							if (!class_exists($class))
							{
								$class = $this->formatPresenterClass($name);
								$file = $this->formatPresenterFile($name);
								throw new InvalidPresenterException("ACannot load presenter '$name', class '$class' was not found in '$file'.");
							}
						}
					}
				}
				
			}
		}

		$reflection = new \ReflectionClass($class);
		$class = $reflection->getName();

		if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
		}

		if ($reflection->isAbstract()) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
		}

		// canonicalize presenter name
		$realName = $this->unformatPresenterClass($class);
		if ($name !== $realName) {
			if ($this->caseSensitive) {
				throw new InvalidPresenterException("Cannot load presenter '$name', case mismatch. Real name is '$realName'.");
			} else {
				$this->cache[$name] = array($class, $realName);
				$name = $realName;
			}
		} else {
			$this->cache[$name] = array($class, $realName);
		}

		return $class;
	}

	/**
	 * Formats presenter class name from its name.
	 * @param	string	presenter name
	 * @param	string
	 * @return	string
	 */
	public function formatPresenterClass($presenter, $type = NULL)
	{
		// PHP 5.3
		if ($type == "module")
			return "\\Nella\\Modules\\" . str_replace(':', "\\", $presenter);
		elseif ($type == "core")
			return "\\Nella\\Core\\" . str_replace(':', "\\", $presenter);
		else
			return str_replace(':', 'Module\\', $presenter) . 'Presenter';
	}

	/**
	 * Formats presenter name from class name.
	 * @param	string	presenter class
	 * @return	string
	 */
	public function unformatPresenterClass($class)
	{
		if (strpos($class, "Nella\\Modules") !== FALSE)
		{
			return str_replace("\\", ":", substr($class, $class[0] == "\\" ? 15 : 14));
		}
		elseif (strpos($class, "Nella\\Core") !== FALSE)
		{
			return str_replace("\\", ":", substr($class, $class[0] == "\\" ? 12 : 11));
		}
		else
			return str_replace('Module\\', ':', substr($class, 0, -9));
	}

	/**
	 * Formats presenter class file name.
	 * @param	string	presenter name
	 * @param	string
	 * @return	string
	 */
	public function formatPresenterFile($presenter, $type = NULL)
	{
		if ($type == "module")
		{
			$path = "/" . str_replace(':', "/", $presenter);
			return LIBS_DIR . "/Nella/Modules" . $path . ".php";
		}
		elseif ($type == "core")
		{
			$path = "/" . str_replace(':', "/", $presenter);
			return LIBS_DIR . "/Nella/Core" . $path . ".php";
		}
		else
		{
			$path = '/' . str_replace(':', 'Module/', $presenter);
			return $this->baseDir . substr_replace($path, '/presenters', strrpos($path, '/'), 0) . 'Presenter.php';
		}
	}
	
	/**
	 * Presenter Loader factory
	 * 
	 * @return	Nella\Presenters\Loader
	 */
	public static function createPresenterLoader()
	{
		return new Loader(Environment::getVariable('appDir'));
	}
}