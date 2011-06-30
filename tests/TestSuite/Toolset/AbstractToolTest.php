<?php
/**
 * Unit tests for Open Power Autoloader
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite\Toolset;
use Extra\DummyTool;

/**
 * @covers \Opl\Autoloader\Toolset\AbstractTool
 * @runTestsInSeparateProcesses
 */
class AbstractToolTest extends \PHPUnit_Framework_TestCase
{
	public function testAddingNamespace()
	{
		$tool = new DummyTool();

		$this->assertFalse($tool->hasNamespace('Foo'));
		$this->assertFalse($tool->hasNamespace('Bar'));
		$this->assertFalse($tool->hasNamespace('Bar\Joe'));

		$tool->addNamespace('Foo', './');
		$tool->addNamespace('Foo\Joe', './');

		$this->assertTrue($tool->hasNamespace('Foo'));
		$this->assertFalse($tool->hasNamespace('Bar'));
		$this->assertTrue($tool->hasNamespace('Foo\Joe'));
	} // end testAddingNamespace();

	public function testAddNamespaceSetsDefaultPathAndExtension()
	{
		$tool = new DummyTool();
		$tool->addNamespace('Foo', './foo/bar/');

		$reflection = new \ReflectionObject($tool);
		$namespacesProperty = $reflection->getProperty('namespaces');
		$namespacesProperty->setAccessible(true);
		$extensionsProperty = $reflection->getProperty('extensions');
		$extensionsProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './foo/bar/'), $namespacesProperty->getValue($tool));
		$this->assertEquals(array('Foo' => '.php'), $extensionsProperty->getValue($tool));
	} // end testAddNamespaceSetsDefaultPathAndExtension();

	public function testAddNamespaceSetsCustomPathAndExtension()
	{
		$tool = new DummyTool();
		$tool->addNamespace('Foo', './bar/joe/', '.php5');

		$reflection = new \ReflectionObject($tool);
		$namespacesProperty = $reflection->getProperty('namespaces');
		$namespacesProperty->setAccessible(true);
		$extensionsProperty = $reflection->getProperty('extensions');
		$extensionsProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './bar/joe/'), $namespacesProperty->getValue($tool));
		$this->assertEquals(array('Foo' => '.php5'), $extensionsProperty->getValue($tool));
	} // end testAddNamespaceSetsCustomPathAndExtension();

	public function testAddNamespaceAddsTrailingSlash()
	{
		$tool = new DummyTool();
		$tool->addNamespace('Foo', './bar/joe', '.php5');
		
		$reflection = new \ReflectionObject($tool);
		$namespacesProperty = $reflection->getProperty('namespaces');
		$namespacesProperty->setAccessible(true);
		$extensionsProperty = $reflection->getProperty('extensions');
		$extensionsProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './bar/joe/'), $namespacesProperty->getValue($tool));
		$this->assertEquals(array('Foo' => '.php5'), $extensionsProperty->getValue($tool));
	} // end testAddNamespaceAddsTrailingSlash();
	
	/**
	 * @expectedException DomainException
	 */
	public function testAddNamespaceThrowsExceptionWhenNamespaceExists()
	{
		$tool = new DummyTool();
		$tool->addNamespace('Foo', './foo/bar/');
		$this->assertTrue($tool->hasNamespace('Foo'));
		$tool->addNamespace('Foo', './foo/bar/');
	} // end testNamespaceThrowsExceptionWhenNamespaceExists();

	public function testRemoveNamespace()
	{
		$tool = new DummyTool();
		$tool->addNamespace('Foo', './foo/bar/');
		$this->assertTrue($tool->hasNamespace('Foo'));

		$reflection = new \ReflectionObject($tool);
		$namespacesProperty = $reflection->getProperty('namespaces');
		$namespacesProperty->setAccessible(true);
		$extensionsProperty = $reflection->getProperty('extensions');
		$extensionsProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './foo/bar/'), $namespacesProperty->getValue($tool));
		$this->assertEquals(array('Foo' => '.php'), $extensionsProperty->getValue($tool));

		$tool->removeNamespace('Foo');
		$this->assertFalse($tool->hasNamespace('Foo'));

		$this->assertEquals(array(), $namespacesProperty->getValue($tool));
		$this->assertEquals(array(), $extensionsProperty->getValue($tool));
	} // end testRemoveLibrary();

	/**
	 * @depends testRemoveNamespace
	 * @expectedException DomainException
	 */
	public function testRemoveNamespaceThrowsExceptionWhenNamespaceDoesNotExist()
	{
		$tool = new DummyTool();
		$this->assertFalse($tool->hasNamespace('Moo'));
		$tool->removeNamespace('Moo');
	} // end testRemoveNamespaceThrowsExceptionWhenNamespaceDoesNotExist();
	
	public function testToFilenameReturnsTheClassName()
	{
		$tool = new DummyTool();
		$tool->addNamespace('Foo', './foo/src/');
		$tool->addNamespace('Foo\\Bar', './bar/src/');
		
		$this->assertEquals('./foo/src/Foo/File.php', $tool->toFilename('Foo\\File'));
		$this->assertEquals('./foo/src/Foo/Bar/File.php', $tool->toFilename('Foo\\Bar\\File'));
		$this->assertEquals('./foo/src/Foo/File/Name.php', $tool->toFilename('Foo\\File_Name'));
	} // end testToFilenameReturnsTheClassName();
	
	/**
	 * @expectedException Opl\Autoloader\Exception\TranslationException
	 */
	public function testToFilenameThrowsExceptionIfNamespaceDoesNotExist()
	{
		$tool = new DummyTool();
		$tool->addNamespace('Foo', './foo/src/');
		$this->assertFalse($tool->hasNamespace('Bar'));
		$tool->toFilename('Bar\\Joe');
	} // end testToFilenameThrowsExceptionIfNamespaceDoesNotExist();
	
	public function testToFilenameAllowsToSkipTheNamespacePath()
	{
		$tool = new DummyTool();
		$tool->addNamespace('Foo', './foo/src/');
		$tool->addNamespace('Foo\\Bar', './bar/src/');
		
		$this->assertEquals('Foo/File.php', $tool->toFilename('Foo\\File', false));
		$this->assertEquals('Foo/Bar/File.php', $tool->toFilename('Foo\\Bar\\File', false));
		$this->assertEquals('Foo/File/Name.php', $tool->toFilename('Foo\\File_Name', false));
	} // end testToFilenameReturnsTheClassName();
	
	public function testGettingAndSettingTheNamespaceSeparator()
	{
		$tool = new DummyTool();
		$this->assertEquals('\\', $tool->getNamespaceSeparator());
		$this->assertSame($tool, $tool->setNamespaceSeparator('_'));
		$this->assertEquals('_', $tool->getNamespaceSeparator());
	} // end testGettingAndSettingTheNamespaceSeparator();
} // end AbstractToolTest;