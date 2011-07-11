<?php
/**
 * Unit tests for Open Power Autoloader
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009-2011 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite;
use Opl\Autoloader\ChdbLoader;

/**
 * @covers \Opl\Autoloader\ChdbLoader
 * @runTestsInSeparateProcesses
 */
class ChdbLoaderTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		if(!extension_loaded('chdb'))
		{
			$this->markTestSkipped('chdb extension is not installed.');
		}
	} // end setUp();
	
	public function testLoaderInitialization()
	{
		$loader = new ChdbLoader('./data/', './data/classMap.chdb');
		$this->assertEquals('./data/', $loader->getDefaultPath());
	} // end testLoaderInitialization();
	
	/**
	 * @expectedException RuntimeException
	 */
	public function testConstructorThrowsExceptionIfFileDoesNotExist()
	{
		$loader = new ChdbLoader('./data/', './data/not_exist.chdb');
	} // end testLoaderInitialization();

	/**
	 * @expectedException RuntimeException
	 */
	public function testConstructorThrowsExceptionIfMapIsInvalid()
	{
		$loader = new ChdbLoader('./data/', './data/invalid_map.chdb');
	} // end testLoaderInitialization();
	
	public function testGetClassMapLocationReturnsTheRequestedData()
	{
		$loader = new ChdbLoader('./data/', './data/classMap.chdb');
		$this->assertEquals('./data/classMap.chdb', $loader->getClassMapLocation());
	} // end testGetClassMapLocationReturnsTheRequestedData();
	
	/**
	 * @depends testLoaderInitialization
	 */
	public function testConstructorAppendsSlash()
	{
		$loader = new ChdbLoader('./foo/bar', './data/classMap.chdb');
		$this->assertEquals('./foo/bar/', $loader->getDefaultPath());
	} // end testConstructorAppendsSlash();

	/**
	 * @depends testLoaderInitialization
	 */
	public function testConstructorAppendsSlashToEmptyString()
	{
		$loader = new ChdbLoader('', './data/classMap.chdb');
		$this->assertEquals('/', $loader->getDefaultPath());
	} // end testConstructorAppendsSlash();

	public function testSetDefaultPath()
	{
		$loader = new ChdbLoader('./foo/bar/', './data/classMap.chdb');
		$this->assertEquals('./foo/bar/', $loader->getDefaultPath());

		$loader->setDefaultPath('./bar/joe/');
		$this->assertEquals('./bar/joe/', $loader->getDefaultPath());
	} // end testSetDefaultPath();

	/**
	 * @depends testSetDefaultPath
	 */
	public function testSetDefaultPathAppendsSlash()
	{
		$loader = new ChdbLoader('./foo/bar/', './data/classMap.chdb');
		$loader->setDefaultPath('./bar/joe');
		$this->assertEquals('./bar/joe/', $loader->getDefaultPath());
	} // end testSetDefaultPathAppendsSlash();

	public function testAddingNamespace()
	{
		$loader = new ChdbLoader('./foo/bar/', './data/classMap.chdb');

		$this->assertFalse($loader->hasNamespace('Foo'));
		$this->assertFalse($loader->hasNamespace('Bar'));

		$loader->addNamespace('Foo');

		$this->assertTrue($loader->hasNamespace('Foo'));
		$this->assertFalse($loader->hasNamespace('Bar'));
	} // end testAddingNamespace();

	public function testAddNamespaceSetsDefaultPath()
	{
		$loader = new ChdbLoader('./foo/bar/', './data/classMap.chdb');
		$loader->addNamespace('Foo');

		$reflection = new \ReflectionObject($loader);
		$namespacesProperty = $reflection->getProperty('namespaces');
		$namespacesProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './foo/bar/'), $namespacesProperty->getValue($loader));
	} // end testAddNamespaceSetsDefaultPath();

	public function testAddNamespaceSetsCustomPath()
	{
		$loader = new ChdbLoader('./foo/bar/', './data/classMap.chdb');
		$loader->addNamespace('Foo', './bar/joe/');

		$reflection = new \ReflectionObject($loader);
		$namespacesProperty = $reflection->getProperty('namespaces');
		$namespacesProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './bar/joe/'), $namespacesProperty->getValue($loader));
	} // end testAddNamespaceSetsCustomPath();
	
	public function testAddNamespaceAddsTrailingSlash()
	{
		$loader = new ChdbLoader('./foo/bar/', './data/classMap.chdb');
		$loader->addNamespace('Foo', './bar/joe');
		
		$reflection = new \ReflectionObject($loader);
		$namespacesProperty = $reflection->getProperty('namespaces');
		$namespacesProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './bar/joe/'), $namespacesProperty->getValue($loader));
	} // end testAddNamespaceAddsTrailingSlash();

	/**
	 * @expectedException DomainException
	 */
	public function testAddNamespaceThrowsExceptionWhenNamespaceExists()
	{
		$loader = new ChdbLoader('./foo/bar/', './data/classMap.chdb');
		$loader->addNamespace('Foo');
		$this->assertTrue($loader->hasNamespace('Foo'));
		$loader->addNamespace('Foo');
	} // end testAddNamespaceThrowsExceptionWhenNamespaceExists();

	public function testRemoveNamespace()
	{
		$loader = new ChdbLoader('./foo/bar/', './data/classMap.chdb');
		$loader->addNamespace('Foo');
		$this->assertTrue($loader->hasNamespace('Foo'));

		$reflection = new \ReflectionObject($loader);
		$namespacesProperty = $reflection->getProperty('namespaces');
		$namespacesProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './foo/bar/'), $namespacesProperty->getValue($loader));

		$loader->removeNamespace('Foo');
		$this->assertFalse($loader->hasNamespace('Foo'));

		$this->assertEquals(array(), $namespacesProperty->getValue($loader));
	} // end testRemoveNamespace();

	/**
	 * @depends testRemoveNamespace
	 * @expectedException DomainException
	 */
	public function testRemoveNamespaceThrowsExceptionWhenNamespaceDoesNotExist()
	{
		$loader = new ChdbLoader('./foo/bar/', './data/classMap.chdb');
		$this->assertFalse($loader->hasNamespace('Moo'));
		$loader->removeNamespace('Moo');
	} // end testRemoveNamespaceThrowsExceptionWhenNamespaceDoesNotExist();

	public function testRegisterWorks()
	{
		$loader = new ChdbLoader('./foo/bar/', './data/classMap.chdb');
		$loader->register();

		$functions = spl_autoload_functions();
		$this->assertContains(array($loader, 'loadClass'), $functions);
	} // end testRegisterWorks();

	public function testUnregisterWorks()
	{
		$loader = new ChdbLoader('./foo/bar/', './data/classMap.chdb');
		$loader->register();

		$functions = spl_autoload_functions();
		$this->assertContains(array($loader, 'loadClass'), $functions);

		$loader->unregister();

		$functions = spl_autoload_functions();
		$this->assertThat($functions, $this->logicalNot($this->contains(array($loader, 'loadClass'))));
	} // end testUnregisterWorks();

	public function testLoadingClasses()
	{
		$loader = new ChdbLoader('./data/', './data/classMap.chdb');
		$loader->addNamespace('Dummy');
		$loader->register();

		// No error should happen here.
		$object = new \Dummy\ShortFile();
	} // end testLoadingClasses();

	public function testSkippingUnknownClasses()
	{
		$loader = new ChdbLoader('./data/', './data/classMap.chdb');
		$loader->addNamespace('Dummy');
		$loader->register();

		spl_autoload_register(function($name){ echo 'yey'; return true; });

		ob_start();
		spl_autoload_call('Foo\\Bar');
		$this->assertEquals('yey', ob_get_clean());
	} // end testSkippingUnknownClasses();
} // end ChdbLoaderTest;