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
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

/**
 * This utility can produce class maps for PHARLoader and ClassMapLoader.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class ClassMapBuilder
{
	/**
	 * The generated map.
	 * @var array
	 */
	protected $map = array();

	/**
	 * Returns the current map structure as an associative array. Each entry
	 * is identified by the class name and consists of two entries. The first
	 * one, with index `0` contains the top-level namespace name; the second one - the
	 * relative path.
	 * 
	 * @return array
	 */
	public function getMap()
	{
		return $this->map;
	} // end getMap();

	/**
	 * Resets the object to the initial state.
	 */
	public function clearMap()
	{
		$this->map = array();
	} // end clearMap();

	/**
	 * Adds the specified top-level namespace to the map. Returns the list of encountered
	 * errors. If the class is already defined in the map, it is overwritten.
	 * 
	 * @param string $namespaceName The namespace name and the name of the top-level directory.
	 * @param string $path The path to the namespace directory.
	 * @param string $extension PHP file extension.
	 * @return array
	 */
	public function addNamespace($namespaceName, $path, $extension = '.php')
	{
		if($path[strlen($path) - 1] != '/')
		{
			$path .= '/';
		}

		$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($path.$namespaceName)
			);
		$errors = array();
		foreach($iterator as $name => $fileEntry)
		{
			
			if(!preg_match('/'.str_replace('.', '\\.', $extension).'$/i', $name))
			{
				continue;
			}
			$file = fopen($fileEntry->getPathname(), 'r');
			if(!is_resource($file))
			{
				$errors[] = 'Cannot open file for reading: '.$fileEntry;
				continue;
			}

			$className = $this->_processSingleFile($file);
			fclose($file);
			if(null === $className)
			{
				$errors[] = 'Not a valid class file: '.$fileEntry;
			}
			else
			{
				$this->map[$className] = array(0 => $namespaceName, str_replace($path, '', $fileEntry->getPathname()));
			}
		}

		return $errors;
	} // end addNamespace();

	/**
	 * Processes a single PHP file, attempting to load the class name
	 * provided by it.
	 *
	 * @param resource $file The file to analyze
	 * @return string
	 */
	protected function _processSingleFile($file)
	{
		$code = '';
		$namespace = '';
		$className = '';

		while(!feof($file))
		{
			// We can safely assume that in most cases, the namespace
			// and class definition will be found in the first 4 KB.
			$code .= fread($file, 4096);

			$state = 0;
			foreach(@token_get_all($code) as $token)
			{
				$tokenName = is_array($token) ? $token[0] : null;

				if($tokenName == T_WHITESPACE)
				{
					continue;
				}

				switch($state)
				{
					case 0:
						if($tokenName == T_NAMESPACE)
						{

							$state = 1;
						}
						elseif($tokenName == T_CLASS || $tokenName == T_INTERFACE)
						{
							$state = 2;
						}
						break;
					case 1:
						if($tokenName == T_STRING)
						{
							$namespace .= $token[1];
						}
						elseif($tokenName == T_NS_SEPARATOR)
						{
							$namespace .= '\\';
						}
						else
						{
							$state = 0;
						}
						break;
					case 2:
						if($tokenName == T_STRING)
						{
							$className .= $token[1];
						}
						else
						{
							$state = 0;
						}
						break;
				}
			}
			if($className != '' && $state == 0)
			{
				break;
			}
			$state = 0;
			$namespace = '';
			$className = '';
		}

		if($className == '')
		{
			return null;
		}
		if($namespace != '')
		{
			return $namespace.'\\'.$className;
		}
		return $className;
	} // end _processSingleFile();
} // end ClassMapBuilder;