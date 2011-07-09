<?php
/**
 * Unit tests for Open Power Autoloader
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite\Toolset;
use Opl\Autoloader\Toolset\ClassMapBuilder;

/**
 * @covers \Opl\Autoloader\Toolset\ClassMapBuilder
 */
class ClassMapBuilderTest extends \PHPUnit_Framework_TestCase
{
	public function testParsingNamespace()
	{
		$builder = new ClassMapBuilder();

		$builder->addNamespace('Dummy', './data/');
		$errors = $builder->buildMap();

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

		$builder->addNamespace('Dummy', './data');
		$errors = $builder->buildMap();

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

		$builder->addNamespace('Dummy', './data/');
		$builder->addNamespace('Dummy2', './data/');
		$errors = $builder->buildMap();
		

		$this->assertEquals(array('Not a valid class file: ./data/Dummy/Subdirectory/InvalidFile.php'), $errors);
		$this->assertEquals(array(
			'Dummy\\ShortFile' => array(0 => 'Dummy2', 1 => 'Dummy2/ShortFile.php'),
			'Dummy\\LongFile' => array(0 => 'Dummy', 1 => 'Dummy/LongFile.php'),
			'Dummy\\AnotherLongFile' => array(0 => 'Dummy', 1 => 'Dummy/AnotherLongFile.php'),
			'Dummy\\DifferentNamespaceStyle' => array(0 => 'Dummy', 1 => 'Dummy/DifferentNamespaceStyle.php'),
			'Dummy\\Subdirectory\\SubdirSupport' => array(0 => 'Dummy', 1 => 'Dummy/Subdirectory/SubdirSupport.php'),
			'Dummy_Subdirectory_NoNamespace' => array(0 => 'Dummy', 1 => 'Dummy/Subdirectory/NoNamespace.php'),
		), $builder->getMap());
	} // end testAddNamespaceOverwritesOldEntries();

	public function testTraitHandling()
	{
		if(version_compare(phpversion(), '5.3.99-dev', '<'))
		{
			$this->markTestSkipped('This test requires PHP 5.4 in order to work.');
		}
		else
		{
			$builder = new ClassMapBuilder();

			$builder->addNamespace('TraitTest', './data/');
			$errors = $builder->buildMap();

			$this->assertEquals(array(), $errors);
			$this->assertEquals(array(
				'TraitTest\\SampleTrait' => array(0 => 'TraitTest', 1 => 'TraitTest/SampleTrait.php'),
			), $builder->getMap());
		}
	} // end testTraitHandling();
} // end ClassMapBuilderTest;