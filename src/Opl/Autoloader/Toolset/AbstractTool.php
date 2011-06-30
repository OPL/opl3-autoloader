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
namespace Opl\Autoloader\Toolset;
use Opl\Autoloader\Exception\TranslationException;
use DomainException;

/**
 * The class provides a common base implementation for all the autoloader-related
 * tools. IT allows to define namespaces, and translate the class names into files.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
abstract class AbstractTool
{
	/**
	 * The list of available top-level namespaces.
	 * @var array
	 */
	protected $namespaces = array();

	/**
	 * The file extensions in the namespaces.
	 * @var array
	 */
	protected $extensions = array();
	
	/**
	 * Registers a new top-level namespace to match.
	 *
	 * @param string $namespace The namespace name to add.
	 * @param string $path The path to the namespace (without the namespace name itself).
	 * @param string $extension The namespace file extension.
	 */
	public function addNamespace($namespace, $path = null, $extension = '.php')
	{
		if(isset($this->namespaces[(string)$namespace]))
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
			$this->namespaces[(string)$namespace] = $path;
		}
		else
		{
			$this->namespaces[(string)$namespace] = $this->defaultPath;
		}
		$this->extensions[(string)$namespace] = $extension;
	} // end addNamespace();

	/**
	 * Checks if the specified top-level namespace is available.
	 *
	 * @param string $namespace The namespace name to check.
	 */
	public function hasNamespace($namespace)
	{
		return isset($this->namespaces[(string)$namespace]);
	} // end hasNamespace();

	/**
	 * Removes a registered top-level namespace.
	 *
	 * @param string $namespace The namespace name to remove.
	 */
	public function removeNamespace($namespace)
	{
		if(!isset($this->namespaces[(string)$namespace]))
		{
			throw new DomainException('The namespace '.$namespace.' is not available.');
		}
		unset($this->namespaces[(string)$namespace]);
		unset($this->extensions[(string)$namespace]);
	} // end removeNamespace();
	
	/**
	 * Translates the class name to the file name, using the algorithm from
	 * the Universal Loader to allow the best precision. If the class could
	 * not be translated, an exception is thrown.
	 * 
	 * @throws TranslationException
	 * @param string $className The class name.
	 * @param boolean $withNamespacePath Whether to include the namespace path into the result.
	 * @param string The file name.
	 */
	public function toFilename($className, $withNamespacePath = true)
	{
		$className = ltrim($className, $this->namespaceSeparator);
		
		foreach($this->namespaces as $namespace => $path)
		{
			if(0 === strpos($className, $namespace))
			{
				$rest = strrchr($className, $this->namespaceSeparator);
				$replacement =
					str_replace($this->namespaceSeparator, '/', substr($className, 0, strlen($className) - strlen($rest))).
					str_replace(array('_', $this->namespaceSeparator), '/', $rest);
				if(!$withNamespacePath)
				{
					return $replacement.$this->extensions[$namespace];
				}
				return $path.$replacement.$this->extensions[$namespace];
			}
		}
		throw new TranslationException('Namespace not found for the class: \''.$className.'\'.');
	} // end toFilename();
} // end AbstractTool;
