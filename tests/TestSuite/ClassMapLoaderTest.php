<?php
/**
 * Unit tests for Open Power Autoloader
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite;
use Opl\Autoloader\ClassMapLoader;

/**
 * @covers \Opl\Autoloader\ClassMapLoader
 * @runTestsInSeparateProcesses
 */
class ClassMapLoaderTest extends \PHPUnit_Framework_TestCase
{
	public function testLoaderInitialization()
	{
		$loader = new ClassMapLoader('./data/', './data/classMap.txt');
		$this->assertEquals('./data/', $loader->getDefaultPath());
	} // end testLoaderInitialization();
	
	/**
	 * @expectedException RuntimeException
	 */
	public function testConstructorThrowsExceptionIfFileDoesNotExist()
	{
		$loader = new ClassMapLoader('./data/', './data/not_exist.txt');
	} // end testLoaderInitialization();

	/**
	 * @expectedException RuntimeException
	 */
	public function testConstructorThrowsExceptionIfMapIsInvalid()
	{
		$loader = new ClassMapLoader('./data/', './data/invalid_map.txt');
	} // end testLoaderInitialization();
	
	public function testGetClassMapLocationReturnsTheRequestedData()
	{
		$loader = new ClassMapLoader('./data/', './data/classMap.txt');
		$this->assertEquals('./data/classMap.txt', $loader->getClassMapLocation());
	} // end testGetClassMapLocationReturnsTheRequestedData();
	
	/**
	 * @depends testLoaderInitialization
	 */
	public function testConstructorAppendsSlash()
	{
		$loader = new ClassMapLoader('./foo/bar', './data/classMap.txt');
		$this->assertEquals('./foo/bar/', $loader->getDefaultPath());
	} // end testConstructorAppendsSlash();

	/**
	 * @depends testLoaderInitialization
	 */
	public function testConstructorAppendsSlashToEmptyString()
	{
		$loader = new ClassMapLoader('', './data/classMap.txt');
		$this->assertEquals('/', $loader->getDefaultPath());
	} // end testConstructorAppendsSlash();

	public function testSetDefaultPath()
	{
		$loader = new ClassMapLoader('./foo/bar/', './data/classMap.txt');
		$this->assertEquals('./foo/bar/', $loader->getDefaultPath());

		$loader->setDefaultPath('./bar/joe/');
		$this->assertEquals('./bar/joe/', $loader->getDefaultPath());
	} // end testSetDefaultPath();

	/**
	 * @depends testSetDefaultPath
	 */
	public function testSetDefaultPathAppendsSlash()
	{
		$loader = new ClassMapLoader('./foo/bar/', './data/classMap.txt');
		$loader->setDefaultPath('./bar/joe');
		$this->assertEquals('./bar/joe/', $loader->getDefaultPath());
	} // end testSetDefaultPathAppendsSlash();

	public function testAddingNamespace()
	{
		$loader = new ClassMapLoader('./foo/bar/', './data/classMap.txt');

		$this->assertFalse($loader->hasNamespace('Foo'));
		$this->assertFalse($loader->hasNamespace('Bar'));

		$loader->addNamespace('Foo');

		$this->assertTrue($loader->hasNamespace('Foo'));
		$this->assertFalse($loader->hasNamespace('Bar'));
	} // end testAddingNamespace();

	public function testAddNamespaceSetsDefaultPath()
	{
		$loader = new ClassMapLoader('./foo/bar/', './data/classMap.txt');
		$loader->addNamespace('Foo');

		$reflection = new \ReflectionObject($loader);
		$namespacesProperty = $reflection->getProperty('namespaces');
		$namespacesProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './foo/bar/'), $namespacesProperty->getValue($loader));
	} // end testAddNamespaceSetsDefaultPath();

	public function testAddNamespaceSetsCustomPath()
	{
		$loader = new ClassMapLoader('./foo/bar/', './data/classMap.txt');
		$loader->addNamespace('Foo', './bar/joe/');

		$reflection = new \ReflectionObject($loader);
		$namespacesProperty = $reflection->getProperty('namespaces');
		$namespacesProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './bar/joe/'), $namespacesProperty->getValue($loader));
	} // end testAddNamespaceSetsCustomPath();
	
	public function testAddNamespaceAddsTrailingSlash()
	{
		$loader = new ClassMapLoader('./foo/bar/', './data/classMap.txt');
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
		$loader = new ClassMapLoader('./foo/bar/', './data/classMap.txt');
		$loader->addNamespace('Foo');
		$this->assertTrue($loader->hasNamespace('Foo'));
		$loader->addNamespace('Foo');
	} // end testAddNamespaceThrowsExceptionWhenNamespaceExists();

	public function testRemoveNamespace()
	{
		$loader = new ClassMapLoader('./foo/bar/', './data/classMap.txt');
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
		$loader = new ClassMapLoader('./foo/bar/', './data/classMap.txt');
		$this->assertFalse($loader->hasNamespace('Moo'));
		$loader->removeNamespace('Moo');
	} // end testRemoveNamespaceThrowsExceptionWhenNamespaceDoesNotExist();

	public function testRegisterWorks()
	{
		$loader = new ClassMapLoader('./foo/bar/', './data/classMap.txt');
		$loader->register();

		$functions = spl_autoload_functions();
		$this->assertContains(array($loader, 'loadClass'), $functions);
	} // end testRegisterWorks();

	public function testUnregisterWorks()
	{
		$loader = new ClassMapLoader('./foo/bar/', './data/classMap.txt');
		$loader->register();

		$functions = spl_autoload_functions();
		$this->assertContains(array($loader, 'loadClass'), $functions);

		$loader->unregister();

		$functions = spl_autoload_functions();
		$this->assertThat($functions, $this->logicalNot($this->contains(array($loader, 'loadClass'))));
	} // end testUnregisterWorks();

	public function testLoadingClasses()
	{
		$loader = new ClassMapLoader('./data/', './data/classMap.txt');
		$loader->addNamespace('Dummy');
		$loader->register();

		// No error should happen here.
		$object = new \Dummy\ShortFile();
	} // end testLoadingClasses();

	public function testSkippingUnknownClasses()
	{
		$loader = new ClassMapLoader('./data/', './data/classMap.txt');
		$loader->addNamespace('Dummy');
		$loader->register();

		spl_autoload_register(function($name){ echo 'yey'; return true; });

		ob_start();
		spl_autoload_call('Foo\\Bar');
		$this->assertEquals('yey', ob_get_clean());
	} // end testSkippingUnknownClasses();
} // end ClassMapLoaderTest;