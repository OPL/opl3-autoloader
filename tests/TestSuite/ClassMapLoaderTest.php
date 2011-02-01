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
		$loader = new ClassMapLoader('./data/classMap.txt', './data/');
		$this->assertEquals('./data/', $loader->getDefaultPath());
	} // end testLoaderInitialization();

	/**
	 * @depends testLoaderInitialization
	 */
	public function testConstructorAppendsSlash()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', './foo/bar');
		$this->assertEquals('./foo/bar/', $loader->getDefaultPath());
	} // end testConstructorAppendsSlash();

	/**
	 * @depends testLoaderInitialization
	 */
	public function testConstructorAppendsSlashToEmptyString()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', '');
		$this->assertEquals('/', $loader->getDefaultPath());
	} // end testConstructorAppendsSlash();

	public function testSetDefaultPath()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', './foo/bar/');
		$this->assertEquals('./foo/bar/', $loader->getDefaultPath());

		$loader->setDefaultPath('./bar/joe/');
		$this->assertEquals('./bar/joe/', $loader->getDefaultPath());
	} // end testSetDefaultPath();

	/**
	 * @depends testSetDefaultPath
	 */
	public function testSetDefaultPathAppendsSlash()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', './foo/bar/');
		$loader->setDefaultPath('./bar/joe');
		$this->assertEquals('./bar/joe/', $loader->getDefaultPath());
	} // end testSetDefaultPathAppendsSlash();

	public function testAddingLibrary()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', './foo/bar/');

		$this->assertFalse($loader->hasLibrary('Foo'));
		$this->assertFalse($loader->hasLibrary('Bar'));

		$loader->addLibrary('Foo');

		$this->assertTrue($loader->hasLibrary('Foo'));
		$this->assertFalse($loader->hasLibrary('Bar'));
	} // end testAddingLibrary();

	public function testAddLibrarySetsDefaultPath()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', './foo/bar/');
		$loader->addLibrary('Foo');

		$reflection = new \ReflectionObject($loader);
		$librariesProperty = $reflection->getProperty('_libraries');
		$librariesProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './foo/bar/'), $librariesProperty->getValue($loader));
	} // end testAddLibrarySetsDefaultPath();

	public function testAddLibrarySetsCustomPath()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', './foo/bar/');
		$loader->addLibrary('Foo', './bar/joe/');

		$reflection = new \ReflectionObject($loader);
		$librariesProperty = $reflection->getProperty('_libraries');
		$librariesProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './bar/joe/'), $librariesProperty->getValue($loader));
	} // end testAddLibrarySetsCustomPath();

	/**
	 * @expectedException RuntimeException
	 */
	public function testAddLibraryThrowsExceptionWhenLibraryExists()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', './foo/bar/');
		$loader->addLibrary('Foo');
		$this->assertTrue($loader->hasLibrary('Foo'));
		$loader->addLibrary('Foo');
	} // end testAddLibraryThrowsExceptionWhenLibraryExists();

	public function testRemoveLibrary()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', './foo/bar/');
		$loader->addLibrary('Foo');
		$this->assertTrue($loader->hasLibrary('Foo'));

		$reflection = new \ReflectionObject($loader);
		$librariesProperty = $reflection->getProperty('_libraries');
		$librariesProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './foo/bar/'), $librariesProperty->getValue($loader));

		$loader->removeLibrary('Foo');
		$this->assertFalse($loader->hasLibrary('Foo'));

		$this->assertEquals(array(), $librariesProperty->getValue($loader));
	} // end testRemoveLibrary();

	/**
	 * @depends testRemoveLibrary
	 * @expectedException RuntimeException
	 */
	public function testRemoveLibraryThrowsExceptionWhenLibraryDoesNotExist()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', './foo/bar/');
		$this->assertFalse($loader->hasLibrary('Moo'));
		$loader->removeLibrary('Moo');
	} // end testRemoveLibraryThrowsExceptionWhenLibraryDoesNotExist();

	public function testRegisterWorks()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', './foo/bar/');
		$loader->register();

		$functions = spl_autoload_functions();
		$this->assertContains(array($loader, 'loadClass'), $functions);
	} // end testRegisterWorks();

	public function testUnregisterWorks()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', './foo/bar/');
		$loader->register();

		$functions = spl_autoload_functions();
		$this->assertContains(array($loader, 'loadClass'), $functions);

		$loader->unregister();

		$functions = spl_autoload_functions();
		$this->assertThat($functions, $this->logicalNot($this->contains(array($loader, 'loadClass'))));
	} // end testUnregisterWorks();

	public function testLoadingClasses()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', './data/');
		$loader->addLibrary('Dummy');
		$loader->register();

		// No error should happen here.
		$object = new \Dummy\ShortFile();
	} // end testLoadingClasses();

	public function testSkippingUnknownClasses()
	{
		$loader = new ClassMapLoader('./data/classMap.txt', './data/');
		$loader->addLibrary('Dummy');
		$loader->register();

		spl_autoload_register(function($name){ echo 'yey'; return true; });

		ob_start();
		spl_autoload_call('Foo\\Bar');
		$this->assertEquals('yey', ob_get_clean());
	} // end testSkippingUnknownClasses();
} // end ClassMapLoaderTest;