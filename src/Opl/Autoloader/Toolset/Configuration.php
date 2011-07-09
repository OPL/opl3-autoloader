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
use Opl\Autoloader\Exception\FileFormatException;

/**
 * This class allows to parse the official Open Power Autoloader configuration
 * files that are used by the CLI commands.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Configuration
{
	/**
	 * The exported PHP file header.
	 * @var string
	 */
	protected $fileHeader;
	/**
	 * The exported PHP file footer.
	 * @var string
	 */
	protected $fileFooter;
	/**
	 * The separators and their namespaces
	 * @var array[string][string]
	 */
	protected $separators;
	/**
	 * The list of defined export files.
	 * @var string[string]
	 */
	protected $files;
	/**
	 * Do we have simple namespaces only in the configuration?
	 * @var boolean
	 */
	protected $simpleNamespacesOnly = true;
	
	/**
	 * Imports the configuration file content into the memory.
	 * 
	 * @throws FileFormatException
	 * @param type $configFile 
	 */
	public function __construct($configFile)
	{
		libxml_use_internal_errors(true);
		$document = \simplexml_load_file($configFile);
		foreach (libxml_get_errors() as $error)
		{
			if($error->level != LIBXML_ERR_WARNING)
			{
				throw new FileFormatException('An error occured while parsing \''.$configFile.'\': '.$error->message.' on line '.($error->line - 1));
			}
		}
		
		if(isset($document->{'file-header'}))
		{
			$this->fileHeader = (string)$document->{'file-header'};
		}
		else
		{
			$this->fileHeader = '<?php'.PHP_EOL;
		}
		if(isset($document->{'file-footer'}))
		{
			$this->fileFooter = (string)$document->{'file-footer'};
		}
		
		if(isset($document->{'export-files'}))
		{
			foreach($document->{'export-files'}->{'file'} as $file)
			{
				$this->processFileTag($file);
			}
		}
		foreach($document->separator as $separatorTag)
		{
			$this->processSeparatorTag($separatorTag);
		}
	} // end __construct();

	/**
	 * Processes the <file> tag.
	 * 
	 * @internal
	 * @throws FileFormatException
	 * @param SimpleXMLElement $fileTag 
	 */
	protected function processFileTag($fileTag)
	{
		if(!isset($fileTag['type']))
		{
			throw new FileFormatException('The <file> tag must have the \'type\' attribute.');
		}
		$this->files[(string)$fileTag['type']] = (string)$fileTag;
	} // end processFileTag();
	
	/**
	 * Processes the <separator> tag and its contents.
	 * 
	 * @internal
	 * @throws FileFormatException
	 * @param SimpleXMLElement $separatorTag
	 */
	protected function processSeparatorTag($separatorTag)
	{
		if(!isset($separatorTag['value']))
		{
			throw new FileFormatException('The <separator> tag must have the \'value\' attribute.');
		}
		
		$separatorValue = (string)$separatorTag['value'];
		$namespaces = array();
		
		foreach($separatorTag->{'namespace'} as $namespaceTag)
		{
			if(!isset($namespaceTag['name']))
			{
				throw new FileFormatException('The <namespace> tag must have the \'name\' attribute.');
			}
			$name = (string)$namespaceTag['name'];
			
			// If the name contains the namespace separator, we cannot use GenericLoader any longer.
			if(strpos($name, $separatorValue) !== false)
			{
				$this->simpleNamespacesOnly = false;
			}
			
			$extension = '.php';
			if(isset($namespaceTag['extension']))
			{
				$extension = (string) $namespaceTag['extension'];
			}
			
			$path = (string)$namespaceTag;
			
			$namespaces[$name] = array(
				'path' => $path,
				'extension' => $extension
			);
		}
		
		$this->separators[$separatorValue] = $namespaces;
	} // end processSeparatorTag();

	/**
	 * Returns the list of available namespace separators.
	 * 
	 * @return string[] 
	 */
	public function getSeparators()
	{
		return array_keys($this->separators);
	} // end getSeparators();
	
	/**
	 * Returns the namespaces defined for the given namespace separator. Each namespace
	 * is an array consisting of two keys: 'path' and 'extension'.
	 * 
	 * @throws OutOfRangeException
	 * @param string $separator The namespace separator
	 * @return array
	 */
	public function getSeparatorNamespaces($separator)
	{
		if(!isset($this->separators[$separator]))
		{
			throw new OutOfRangeException('The separator \''.$separator.' is not defined.');
		}
		return $this->separators[$separator];
	} // end getSeparatorNamespaces();
	
	/**
	 * Returns all the files defined by the configuration file. If the optional
	 * argument is set to true, the list does not contain the reserved special file
	 * types.
	 * 
	 * @param boolean $filterSpecial Do we skip special file types?
	 * @return string[string]
	 */
	public function getFiles($filterSpecial = false)
	{
		if(!$filterSpecial)
		{
			return $this->files;
		}
		$result = array();
		$ignore = array('serialized-class-map', 'chdb-class-map', 'core-dump', 'core-export');
		foreach($this->files as $name => $file)
		{
			if(!in_array($name, $ignore))
			{
				$result[$name] = $file;
			}
		}
		return $result;
	} // end getFiles();
	
	/**
	 * Returns the file path defined for the given file type.
	 * 
	 * @throws OutOfRangeException If the type is not defined.
	 * @param string $type The type identifier.
	 * @return string
	 */
	public function getFile($type)
	{
		if(!isset($this->files[$type]))
		{
			throw new OutOfRangeException('The file type \''.$type.'\' is not defined.');
		}
		return $this->files[$type];
	} // end getFile();
	
	/**
	 * Checks if the given file type is defined.
	 * 
	 * @param string $type The type identifier.
	 * @return boolean
	 */
	public function hasFile($type)
	{
		return isset($this->files[$type]);
	} // end hasFile();
	
	/**
	 * Returns the stub file header with the autoloading code.
	 * 
	 * @return string 
	 */
	public function getFileHeader()
	{
		return $this->fileHeader;
	} // end getFileHeader();
	
	/**
	 * Returns the stub file footer with the autoloading code.
	 * 
	 * @return string
	 */
	public function getFileFooter()
	{
		return $this->fileFooter;
	} // end getFileFooter();
	
	/**
	 * Returns true, if the configuration file defines simple namespaces only.
	 * A simple namespace does not contain the namespace separator in its name,
	 * so it can be only a top-level namespace. <tt>GenericLoader</tt> does not
	 * support complex namespaces and this is why we need this information.
	 * 
	 * @return boolean
	 */
	public function hasSimpleNamespacesOnly()
	{
		return $this->simpleNamespacesOnly;
	} // end hasSimpleNamespacesOnly();
} // end Configuration;