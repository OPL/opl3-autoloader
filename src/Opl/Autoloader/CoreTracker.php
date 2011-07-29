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
 * This decorator for any OPL autoloader tracks the loaded classes, attempting
 * to find the common core of the entire application that must be always loaded.
 * We can skip the autoloading procedure for it by generating a plain list of
 * <tt>require</tt> commands.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class CoreTracker
{
	/**
	 * The decorated autoloaders.
	 * @var object[]
	 */
	protected $autoloaders = array();
	/**
	 * The core file name, where the results should be dumped.
	 * @var string
	 */
	protected $coreFileName;
	/**
	 * The core file resource.
	 * @var resource
	 */
	protected $coreFile;
	/**
	 * The current core layout.
	 * @var array
	 */
	protected $core;
	/**
	 * The current scan.
	 * @var array
	 */
	protected $currentScan;
	/**
	 * Whether we are generating the initial core or reducing the possibilities?
	 * @var integer
	 */
	protected $mode;
	
	/**
	 * If PHP 5.4 traits are available, this variable is set to true.
	 * @var type 
	 */
	protected $traitsSupported = false;
	
	/**
	 * Creates the core tracker.
	 * 
	 * @param string $coreFile The core file.
	 */
	public function __construct($coreFile)
	{
		$this->coreFileName = (string)$coreFile;
		
		if(!file_exists($this->coreFileName))
		{
			touch($this->coreFileName);
		}
		$this->coreFile = fopen($this->coreFileName, 'r+');
		
		$content = '';
		while(!feof($this->coreFile))
		{
			$content .= fread($this->coreFile, 2048);
		}
		ftruncate($this->coreFile, 0);
		rewind($this->coreFile);
		$this->core = @unserialize($content);
		if(false == $this->core)
		{
			$this->core = array();
			$this->mode = 0;
		}
		else
		{
			$this->mode = 1;
		}
		$this->currentScan = array();
		$this->traitsSupported = function_exists('trait_exists');
	} // end __construct();
	
	/**
	 * Updates the core dump file.
	 */
	public function __destruct()
	{
		if(0 == $this->mode)
		{
			fwrite($this->coreFile, serialize($this->currentScan));
		}
		else
		{
			fwrite($this->coreFile, serialize(array_intersect($this->core, $this->currentScan)));
		}
		fclose($this->coreFile);
	} // end __destruct();
	
	/**
	 * Registers a new autoloader in the core tracker. The method throws an exception,
	 * if the registered object does not contain a <tt>loadClass</tt> method.
	 * 
	 * @param object $loader The autoloader object.
	 * @return CoreTracker Fluent interface. 
	 */
	public function addLoader($loader)
	{
		if(!is_object($loader) || !method_exists($loader, 'loadClass'))
		{
			throw new DomainException('The first argument must be an autoloader object with \'loadClass\' method.');
		}
		$this->autoloaders[] = $loader;
		return $this;
	} // end addLoader();
	
	/**
	 * Returns the list of decorated autoloaders.
	 * 
	 * @return object[]
	 */
	public function getLoaders()
	{
		return $this->autoloaders;
	} // end getAutoloader();
	
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
	 * Performs the core tracking and delegates the loading to the decorated
	 * autoloader.
	 */
	public function loadClass($className)
	{
		if($this->traitsSupported)
		{
			// Code for PHP 5.4.x
			foreach($this->autoloaders as $loader)
			{
				// The class must be loaded before adding it to the list.
				$loader->loadClass($className);
				if(class_exists($className, false) || interface_exists($className, false) || trait_exists($className, false))
				{
					$this->currentScan[] = $className;
					break;
				}
			}
		}
		else
		{
			// Code for PHP 5.3.x
			foreach($this->autoloaders as $loader)
			{
				// The class must be loaded before adding it to the list.
				$loader->loadClass($className);
				if(class_exists($className, false) || interface_exists($className, false))
				{
					$this->currentScan[] = $className;
					break;
				}
			}
		}	
	} // end loadClass();
} // end CoreTracker;