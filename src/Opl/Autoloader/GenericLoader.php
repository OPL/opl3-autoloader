<?php
/*
 *  OPEN POWER LIBS <http://www.invenzzia.org>
 *
 * This file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE. It is also available through
 * WWW at this URL: <http://www.invenzzia.org/license/new-bsd>
 *
 * Copyright (c) Invenzzia Group <http://www.invenzzia.org>
 * and other contributors. See website for details.
 */
namespace Opl\Autoloader;
use RuntimeException;

/**
 * The generic class autoloader is a slightly enhanced version of the
 * AggregateAutoloader <http://github.com/zyxist/AggregateAutoloader>
 * originally distributed under the terms of MIT license.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class GenericLoader
{
	/**
	 * The default autoloader path.
	 * @static
	 * @var string
	 */
	private $_defaultPath = '';

	/**
	 * The list of available libraries.
	 * @var array
	 */
	private $_libraries = array();

	/**
	 * The library extensions.
	 * @var array
	 */
	private $_extensions = array();

	/**
	 * The namespace separator
	 * @var string
	 */
	private $_namespaceSeparator = '\\';

	/**
	 * Constructs the autoloader.
	 *
	 * @param string $defaultPath The default library path.
	 * @param string $namespaceSeparator The namespace separator used in this autoloader.
	 */
	public function __construct($defaultPath = './', $namespaceSeparator = '\\')
	{
		$this->_namespaceSeparator = $namespaceSeparator;

		$length = strlen($defaultPath);
		if($length == 0 || $defaultPath[$length - 1] != '/')
		{
			$defaultPath .= '/';
		}
		$this->_defaultPath = $defaultPath;
	} // end __construct();

	/**
	 * Registers a new library to match.
	 *
	 * @param string $library The library name to add.
	 * @param string $path The path to the library.
	 * @param string $extension The library file extension.
	 */
	public function addLibrary($library, $path = null, $extension = '.php')
	{
		if(isset($this->_libraries[(string)$library]))
		{
			throw new RuntimeException('Library '.$library.' is already added.');
		}
		if($path !== null)
		{
			$length = strlen($path);
			if($length == 0 || $path[$length - 1] != '/')
			{
				$path .= '/';
			}
			$this->_libraries[(string)$library] = $path;
		}
		else
		{
			$this->_libraries[(string)$library] = $this->_defaultPath;
		}
		$this->_extensions[(string)$library] = $extension;
	} // end addLibrary();

	/**
	 * Checks if the specified library is available.
	 *
	 * @param string $library The library name to check.
	 */
	public function hasLibrary($library)
	{
		return isset($this->_libraries[(string)$library]);
	} // end hasLibrary();

	/**
	 * Removes a recognized library.
	 *
	 * @param string $library The library name to remove.
	 */
	public function removeLibrary($library)
	{
		if(!isset($this->_libraries[(string)$library]))
		{
			throw new RuntimeException('Library '.$library.' is not available.');
		}
		unset($this->_libraries[(string)$library]);
		unset($this->_extensions[(string)$library]);
	} // end removeLibrary();

	/**
	 * Sets the namespace separator used by classes in the namespace of this class loader.
	 *
	 * @param string $sep The separator to use.
	 */
	public function setNamespaceSeparator($sep)
	{
		$this->_namespaceSeparator = $sep;
	} // end setNamespaceSeparator();

	/**
	 * Gets the namespace seperator used by classes in the namespace of this class loader.
	 *
	 * @return string
	 */
	public function getNamespaceSeparator()
	{
		return $this->_namespaceSeparator;
	} // end getNamespaceSeparator();

	/**
	 * Sets the default path used by the libraries. Note that it does not affect
	 * the already added libraries.
	 *
	 * @param string $defaultPath The new default path.
	 */
	public function setDefaultPath($defaultPath)
	{
		if($defaultPath[strlen($defaultPath) - 1] != '/')
		{
			$defaultPath .= '/';
		}
		$this->_defaultPath = $defaultPath;
	} // end setDefaultPath();

	/**
	 * Returns the default path used by the libraries.
	 *
	 * @return string The current default path.
	 */
	public function getDefaultPath()
	{
		return $this->_defaultPath;
	} // end getDefaultPath();

	/**
	 * Installs this class loader on the SPL autoload stack.
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'loadClass'));
	} // end register();

	/**
	 * Uninstalls this class loader from the SPL autoloader stack.
	*/
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'loadClass'));
	} // end unregister();

	/**
	 * Loads the given class or interface.
	 *
	 * @param string $className The name of the class to load.
	 * @return void
	 */
	public function loadClass($className)
	{
		$className = ltrim($className, $this->_namespaceSeparator);
		$match = strstr($className, $this->_namespaceSeparator, true);

		if(false === $match || !isset($this->_libraries[$match]))
		{
			return false;
		}
		$rest = strrchr($className, $this->_namespaceSeparator);
		$replacement =
			str_replace($this->_namespaceSeparator, '/', substr($className, 0, strlen($className) - strlen($rest))).
			str_replace(array('_', $this->_namespaceSeparator), '/', $rest);

		require($this->_libraries[$match].$replacement.$this->_extensions[$match]);
		return true;
	} // end loadClass();
} // end GenericLoader;