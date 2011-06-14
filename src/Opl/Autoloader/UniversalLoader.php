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
use DomainException;

/**
 * The universal class loader is a derivative of GenericLoader. The main
 * difference is the support for registering sub-namespaces at the extra
 * performance overhead. The loader is developed primarily for the
 * development purposes.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class UniversalLoader
{
	/**
	 * The default autoloader path.
	 * @static
	 * @var string
	 */
	private $_defaultPath = '';

	/**
	 * The list of available namespaces.
	 * @var array
	 */
	private $_namespaces = array();

	/**
	 * The file extensions in the namespaces.
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
	 * @param string $defaultPath The default namespace path.
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
	 * Registers a new top-level namespace to match.
	 *
	 * @param string $namespace The namespace name to add.
	 * @param string $path The path to the namespace (without the namespace name itself).
	 * @param string $extension The namespace file extension.
	 */
	public function addNamespace($namespace, $path = null, $extension = '.php')
	{
		if(isset($this->_namespaces[(string)$namespace]))
		{
			throw new DomainException('The namespace '.$namespace.' is already added.');
		}
		if($path !== null)
		{
			$length = strlen($path);
			if($length == 0 || $path[$length - 1] != '/')
			{
				$path .= '/';
			}
			$this->_namespaces[(string)$namespace] = $path;
		}
		else
		{
			$this->_namespaces[(string)$namespace] = $this->_defaultPath;
		}
		$this->_extensions[(string)$namespace] = $extension;
	} // end addNamespace();

	/**
	 * Checks if the specified top-level namespace is available.
	 *
	 * @param string $namespace The namespace name to check.
	 */
	public function hasNamespace($namespace)
	{
		return isset($this->_namespaces[(string)$namespace]);
	} // end hasNamespace();

	/**
	 * Removes a registered top-level namespace.
	 *
	 * @param string $namespace The namespace name to remove.
	 */
	public function removeNamespace($namespace)
	{
		if(!isset($this->_namespaces[(string)$namespace]))
		{
			throw new DomainException('The namespace '.$namespace.' is not available.');
		}
		unset($this->_namespaces[(string)$namespace]);
		unset($this->_extensions[(string)$namespace]);
	} // end removeNamespace();

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
	 * Sets the default path used by the namespaces. Note that it does not affect
	 * the already added namespaces.
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
	 * Returns the default path used by the namespaces.
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
		
		foreach($this->_namespaces as $namespace => $path)
		{
			if(0 === strpos($className, $namespace))
			{
				$rest = strrchr($className, $this->_namespaceSeparator);
				$replacement =
					str_replace($this->_namespaceSeparator, '/', substr($className, 0, strlen($className) - strlen($rest))).
					str_replace(array('_', $this->_namespaceSeparator), '/', $rest);
				require($path.$replacement.$this->_extensions[$namespace]);
				return true;
			}
		}
		return false;
	} // end loadClass();
} // end UniversalLoader;