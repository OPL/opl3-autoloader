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
use Opl\Cache\Cache;

/**
 * This autoloader is based on the pre-computed class location
 * map. The map can be stored in a file and optionally cached
 * in the memory.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class ClassMapLoader
{
	/**
	 * The default autoloader path.
	 * @static
	 * @var string
	 */
	private $_defaultPath = '';

	/**
	 * The list of available top-level namespaces.
	 * @var array
	 */
	private $_namespaces = array();
	/**
	 * The loaded class map.
	 * @var array
	 * @internal
	 */
	protected $_classMap;

	/**
	 * The location where the class map is stored.
	 * @var string
	 * @internal
	 */
	protected $_classMapLocation;

	/**
	 * Creates the class map loader and loads the map into the memory.
	 * The map must be constructed with the command line interface.
	 *
	 * @param string $defaultPath The default location path used for newly registered namespaces
	 * @param string $classMapLocation The class map location on the disk
	 * @param Cache $cache The optional memory cache to be used
	 */
	public function __construct($defaultPath, $classMapLocation, Cache $cache = null)
	{
		$this->setDefaultPath($defaultPath);
		$this->_classMapLocation = $classMapLocation;

		if(null !== $cache)
		{
			$this->_classMap = $cache->get('classMap');
			if(null === $this->_classMap)
			{
				$this->_loadMap();
				$cache->set('classMap', $this->_classMap);
			}
		}
		else
		{
			$this->_loadMap();
		}
	} // end __construct();

	/**
	 * Loads the map from a file.
	 *
	 * @internal
	 * @throws RuntimeException
	 */
	protected function _loadMap()
	{
		if(!file_exists($this->_classMapLocation))
		{
			throw new RuntimeException('Cannot find a class map under the specified location.');
		}
		$this->_classMap = unserialize(file_get_contents($this->_classMapLocation));

		if(!is_array($this->_classMap))
		{
			throw new RuntimeException('The loaded file does not contain a valid class map.');
		}
	} // end _loadMap();

	/**
	 * Registers a new top-level namespace to match. If no path is specified, the current
	 * default path is taken.
	 *
	 * @throws RuntimeException
	 * @param string $namespace The namespace name to add.
	 * @param string $path The path to the namespace.
	 */
	public function addNamespace($namespace, $path = null)
	{
		if(isset($this->_namespaces[(string)$namespace]))
		{
			throw new RuntimeException('The namespace '.$namespace.' is already added.');
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
	} // end addNamespace();

	/**
	 * Checks if the specified namespace is available.
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
	 * @throws RuntimeException
	 * @param string $namespace The namespace name to remove.
	 */
	public function removeNamespace($namespace)
	{
		if(!isset($this->_namespaces[(string)$namespace]))
		{
			throw new RuntimeException('The namespace '.$namespace.' is not available.');
		}
		unset($this->_namespaces[(string)$namespace]);
	} // end removeNamespace();

	/**
	 * Sets the default path used by the namespaces. Note that it does not affect
	 * the already added namespaces.
	 *
	 * @param string $defaultPath The new default path.
	 */
	public function setDefaultPath($defaultPath)
	{
		$length = strlen($defaultPath);
		if($length == 0 || $defaultPath[$length - 1] != '/')
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
	 * Returns the current class map location.
	 * 
	 * @return string
	 */
	public function getClassMapLocation()
	{
		return $this->_classMapLocation;
	} // end getClassMapLocation();

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
	 * Attempts to load the specified class from a file.
	 *
	 * @param string $className The class name.
	 * @return boolean
	 */
	public function loadClass($className)
	{
		if(!isset($this->_classMap[$className]))
		{
			return false;
		}
		require($this->_namespaces[$this->_classMap[$className][0]].$this->_classMap[$className][1]);
		return true;
	} // end loadClass();
} // end ClassMapLoader;