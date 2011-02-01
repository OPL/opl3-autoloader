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
	public function testParsingNamespace()
	{
		$builder = new ClassMapBuilder();

		$errors = $builder->addNamespace('Dummy', './data/');

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

	public function testAddNamespaceAppendsSlashes()
	{
		$builder = new ClassMapBuilder();

		$errors = $builder->addNamespace('Dummy', './data');

		$this->assertEquals(array('Not a valid class file: ./data/Dummy/Subdirectory/InvalidFile.php'), $errors);
		$this->assertEquals(array(
			'Dummy\\ShortFile' => array(0 => 'Dummy', 1 => 'Dummy/ShortFile.php'),
			'Dummy\\LongFile' => array(0 => 'Dummy', 1 => 'Dummy/LongFile.php'),
			'Dummy\\AnotherLongFile' => array(0 => 'Dummy', 1 => 'Dummy/AnotherLongFile.php'),
			'Dummy\\DifferentNamespaceStyle' => array(0 => 'Dummy', 1 => 'Dummy/DifferentNamespaceStyle.php'),
			'Dummy\\Subdirectory\\SubdirSupport' => array(0 => 'Dummy', 1 => 'Dummy/Subdirectory/SubdirSupport.php'),
			'Dummy_Subdirectory_NoNamespace' => array(0 => 'Dummy', 1 => 'Dummy/Subdirectory/NoNamespace.php'),
		), $builder->getMap());
	} // end testAddNamespaceAppendsSlashes();

	public function testAddNamespaceOverwritesOldEntries()
	{
		$builder = new ClassMapBuilder();

		$errors = $builder->addNamespace('Dummy', './data/');
		$errors = $builder->addNamespace('Dummy2', './data/');

		$this->assertEquals(array(), $errors);
		$this->assertEquals(array(
			'Dummy\\ShortFile' => array(0 => 'Dummy2', 1 => 'Dummy2/ShortFile.php'),
			'Dummy\\LongFile' => array(0 => 'Dummy', 1 => 'Dummy/LongFile.php'),
			'Dummy\\AnotherLongFile' => array(0 => 'Dummy', 1 => 'Dummy/AnotherLongFile.php'),
			'Dummy\\DifferentNamespaceStyle' => array(0 => 'Dummy', 1 => 'Dummy/DifferentNamespaceStyle.php'),
			'Dummy\\Subdirectory\\SubdirSupport' => array(0 => 'Dummy', 1 => 'Dummy/Subdirectory/SubdirSupport.php'),
			'Dummy_Subdirectory_NoNamespace' => array(0 => 'Dummy', 1 => 'Dummy/Subdirectory/NoNamespace.php'),
		), $builder->getMap());
	} // end testAddNamespaceOverwritesOldEntries();
} // end ClassMapBuilderTest;