<?php
/**
 * Unit tests for Open Power Autoloader
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite;
use Opl\Autoloader\ClassMapBuilder;

/**
 * @covers \Opl\Autoloader\ClassMapBuilder
 * @runTestsInSeparateProcesses
 */
class ClassMapBuilderTest extends \PHPUnit_Framework_TestCase
{
	public function testParsingLibrary()
	{
		$builder = new ClassMapBuilder();

		$errors = $builder->addLibrary('Dummy', './data/');

		$this->assertEquals(array('Not a valid class file: ./data/Dummy/Subdirectory/InvalidFile.php'), $errors);
		$this->assertEquals(array(
			'Dummy\\ShortFile' => array(0 => 'Dummy', 1 => 'Dummy/ShortFile.php'),
			'Dummy\\LongFile' => array(0 => 'Dummy', 1 => 'Dummy/LongFile.php'),
			'Dummy\\AnotherLongFile' => array(0 => 'Dummy', 1 => 'Dummy/AnotherLongFile.php'),
			'Dummy\\DifferentNamespaceStyle' => array(0 => 'Dummy', 1 => 'Dummy/DifferentNamespaceStyle.php'),
			'Dummy\\Subdirectory\\SubdirSupport' => array(0 => 'Dummy', 1 => 'Dummy/Subdirectory/SubdirSupport.php'),
			'Dummy_Subdirectory_NoNamespace' => array(0 => 'Dummy', 1 => 'Dummy/Subdirectory/NoNamespace.php'),
		), $builder->getMap());
	} // end testParsingLibrary();

	public function testAddLibraryAppendsSlashes()
	{
		$builder = new ClassMapBuilder();

		$errors = $builder->addLibrary('Dummy', './data');

		$this->assertEquals(array('Not a valid class file: ./data/Dummy/Subdirectory/InvalidFile.php'), $errors);
		$this->assertEquals(array(
			'Dummy\\ShortFile' => array(0 => 'Dummy', 1 => 'Dummy/ShortFile.php'),
			'Dummy\\LongFile' => array(0 => 'Dummy', 1 => 'Dummy/LongFile.php'),
			'Dummy\\AnotherLongFile' => array(0 => 'Dummy', 1 => 'Dummy/AnotherLongFile.php'),
			'Dummy\\DifferentNamespaceStyle' => array(0 => 'Dummy', 1 => 'Dummy/DifferentNamespaceStyle.php'),
			'Dummy\\Subdirectory\\SubdirSupport' => array(0 => 'Dummy', 1 => 'Dummy/Subdirectory/SubdirSupport.php'),
			'Dummy_Subdirectory_NoNamespace' => array(0 => 'Dummy', 1 => 'Dummy/Subdirectory/NoNamespace.php'),
		), $builder->getMap());
	} // end testAddLibraryAppendsSlashes();

	public function testAddLibraryOverwritesOldEntries()
	{
		$builder = new ClassMapBuilder();

		$errors = $builder->addLibrary('Dummy', './data/');
		$errors = $builder->addLibrary('Dummy2', './data/');

		$this->assertEquals(array(), $errors);
		$this->assertEquals(array(
			'Dummy\\ShortFile' => array(0 => 'Dummy2', 1 => 'Dummy2/ShortFile.php'),
			'Dummy\\LongFile' => array(0 => 'Dummy', 1 => 'Dummy/LongFile.php'),
			'Dummy\\AnotherLongFile' => array(0 => 'Dummy', 1 => 'Dummy/AnotherLongFile.php'),
			'Dummy\\DifferentNamespaceStyle' => array(0 => 'Dummy', 1 => 'Dummy/DifferentNamespaceStyle.php'),
			'Dummy\\Subdirectory\\SubdirSupport' => array(0 => 'Dummy', 1 => 'Dummy/Subdirectory/SubdirSupport.php'),
			'Dummy_Subdirectory_NoNamespace' => array(0 => 'Dummy', 1 => 'Dummy/Subdirectory/NoNamespace.php'),
		), $builder->getMap());
	} // end testAddLibraryOverwritesOldEntries();
} // end ClassMapBuilderTest;