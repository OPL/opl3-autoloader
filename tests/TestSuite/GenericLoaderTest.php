<?php
/**
 * Unit tests for Open Power Autoloader
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite;
use Opl\Autoloader\GenericLoader;
require_once 'vfsStream/vfsStream.php';

/**
 * @covers \Opl\Autoloader\GenericLoader
 * @runTestsInSeparateProcesses
 */
class GenericLoaderTest extends \PHPUnit_Framework_TestCase
{
	public function testLoaderInitialization()
	{
		$loader = new GenericLoader('foo', './foo/bar/');
		$this->assertEquals('foo', $loader->getNamespaceSeparator());
		$this->assertEquals('./foo/bar/', $loader->getDefaultPath());
	} // end testLoaderInitialization();

	/**
	 * @depends testLoaderInitialization
	 */
	public function testConstructorAppendsSlash()
	{
		$loader = new GenericLoader('foo', './foo/bar');
		$this->assertEquals('./foo/bar/', $loader->getDefaultPath());
	} // end testConstructorAppendsSlash();

	/**
	 * @depends testLoaderInitialization
	 */
	public function testConstructorAppendsSlashToEmptyString()
	{
		$loader = new GenericLoader('foo', '');
		$this->assertEquals('/', $loader->getDefaultPath());
	} // end testConstructorAppendsSlash();

	public function testSetDefaultPath()
	{
		$loader = new GenericLoader('foo', './foo/bar/');
		$this->assertEquals('./foo/bar/', $loader->getDefaultPath());

		$loader->setDefaultPath('./bar/joe/');
		$this->assertEquals('./bar/joe/', $loader->getDefaultPath());
	} // end testSetDefaultPath();

	/**
	 * @depends testSetDefaultPath
	 */
	public function testSetDefaultPathAppendsSlash()
	{
		$loader = new GenericLoader('foo', './foo/bar/');
		$loader->setDefaultPath('./bar/joe');
		$this->assertEquals('./bar/joe/', $loader->getDefaultPath());
	} // end testSetDefaultPathAppendsSlash();

	public function testSetNamespaceSeparator()
	{
		$loader = new GenericLoader('foo', './foo/bar/');
		$this->assertEquals('foo', $loader->getNamespaceSeparator());
		$loader->setNamespaceSeparator('bar');
		$this->assertEquals('bar', $loader->getNamespaceSeparator());
	} // end testSetNamespaceSeparator();

	public function testAddingLibrary()
	{
		$loader = new GenericLoader('\\', './foo/bar/');

		$this->assertFalse($loader->hasLibrary('Foo'));
		$this->assertFalse($loader->hasLibrary('Bar'));

		$loader->addLibrary('Foo');

		$this->assertTrue($loader->hasLibrary('Foo'));
		$this->assertFalse($loader->hasLibrary('Bar'));
	} // end testAddingLibrary();

	public function testAddLibrarySetsDefaultPathAndException()
	{
		$loader = new GenericLoader('\\', './foo/bar/');
		$loader->addLibrary('Foo');

		$reflection = new \ReflectionObject($loader);
		$librariesProperty = $reflection->getProperty('_libraries');
		$librariesProperty->setAccessible(true);
		$extensionsProperty = $reflection->getProperty('_extensions');
		$extensionsProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './foo/bar/'), $librariesProperty->getValue($loader));
		$this->assertEquals(array('Foo' => '.php'), $extensionsProperty->getValue($loader));
	} // end testAddLibrarySetsDefaultPathAndException();

	public function testAddLibrarySetsCustomPathAndException()
	{
		$loader = new GenericLoader('\\', './foo/bar/');
		$loader->addLibrary('Foo', './bar/joe/', '.php5');

		$reflection = new \ReflectionObject($loader);
		$librariesProperty = $reflection->getProperty('_libraries');
		$librariesProperty->setAccessible(true);
		$extensionsProperty = $reflection->getProperty('_extensions');
		$extensionsProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './bar/joe/'), $librariesProperty->getValue($loader));
		$this->assertEquals(array('Foo' => '.php5'), $extensionsProperty->getValue($loader));
	} // end testAddLibrarySetsCustomPathAndException();

	/**
	 * @expectedException RuntimeException
	 */
	public function testAddLibraryThrowsExceptionWhenLibraryExists()
	{
		$loader = new GenericLoader('\\', './foo/bar/');
		$loader->addLibrary('Foo');
		$this->assertTrue($loader->hasLibrary('Foo'));
		$loader->addLibrary('Foo');
	} // end testAddLibraryThrowsExceptionWhenLibraryExists();

	public function testRemoveLibrary()
	{
		$loader = new GenericLoader('\\', './foo/bar/');
		$loader->addLibrary('Foo');
		$this->assertTrue($loader->hasLibrary('Foo'));

		$reflection = new \ReflectionObject($loader);
		$librariesProperty = $reflection->getProperty('_libraries');
		$librariesProperty->setAccessible(true);
		$extensionsProperty = $reflection->getProperty('_extensions');
		$extensionsProperty->setAccessible(true);

		$this->assertEquals(array('Foo' => './foo/bar/'), $librariesProperty->getValue($loader));
		$this->assertEquals(array('Foo' => '.php'), $extensionsProperty->getValue($loader));

		$loader->removeLibrary('Foo');
		$this->assertFalse($loader->hasLibrary('Foo'));

		$this->assertEquals(array(), $librariesProperty->getValue($loader));
		$this->assertEquals(array(), $extensionsProperty->getValue($loader));
	} // end testRemoveLibrary();

	/**
	 * @depends testRemoveLibrary
	 * @expectedException RuntimeException
	 */
	public function testRemoveLibraryThrowsExceptionWhenLibraryDoesNotExist()
	{
		$loader = new GenericLoader('\\', './foo/bar/');
		$this->assertFalse($loader->hasLibrary('Moo'));
		$loader->removeLibrary('Moo');
	} // end testRemoveLibraryThrowsExceptionWhenLibraryDoesNotExist();

	public function testRegisterWorks()
	{
		$loader = new GenericLoader('\\', './foo/bar/');
		$loader->register();

		$functions = spl_autoload_functions();
		$this->assertContains(array($loader, 'loadClass'), $functions);
	} // end testRegisterWorks();

	public function testUnregisterWorks()
	{
		$loader = new GenericLoader('\\', './foo/bar/');
		$loader->register();

		$functions = spl_autoload_functions();
		$this->assertContains(array($loader, 'loadClass'), $functions);

		$loader->unregister();

		$functions = spl_autoload_functions();
		$this->assertThat($functions, $this->logicalNot($this->contains(array($loader, 'loadClass'))));
	} // end testUnregisterWorks();

	public function testLoaderReplacesNSToSlashes()
	{
		$file = new \vfsStreamFile('Bar.php');
		$file->setContent('<?php echo "FOO\BAR.PHP"; ');
		$topLevelDir = new \vfsStreamDirectory('Foo');
		$topLevelDir->addChild($file);

		\vfsStreamWrapper::register();
		\vfsStreamWrapper::setRoot($topLevelDir);

		$loader = new GenericLoader('\\', \vfsStream::url(''));
		$loader->addLibrary('Foo');
		$loader->register();

		ob_start();
		spl_autoload_call('Foo\\Bar');
		$this->assertEquals('FOO\\BAR.PHP', ob_get_clean());
	} // end testLoaderReplacesNSToSlashes();

	public function testLoaderReplacesUnderscoresToSlashesInClassNames()
	{
		$file = new \vfsStreamFile('Joe.php');
		$file->setContent('<?php echo "FOO\BAR\JOE.PHP"; ');
		$subdir = new \vfsStreamDirectory('Bar');
		$subdir->addChild($file);
		$topLevelDir = new \vfsStreamDirectory('Foo');
		$topLevelDir->addChild($subdir);


		\vfsStreamWrapper::register();
		\vfsStreamWrapper::setRoot($topLevelDir);

		$loader = new GenericLoader('\\', \vfsStream::url(''));
		$loader->addLibrary('Foo');
		$loader->register();

		ob_start();
		spl_autoload_call('Foo\\Bar_Joe');
		$this->assertEquals('FOO\\BAR\\JOE.PHP', ob_get_clean());
	} // end testLoaderReplacesUnderscoresToSlashesInClassNames();

	public function testLoaderDoesNotReplaceUnderscoresToSlashesInNamespace()
	{
		$file = new \vfsStreamFile('Goo.php');
		$file->setContent('<?php echo "FOO\BAR_JOE\GOO.PHP"; ');
		$subdir = new \vfsStreamDirectory('Bar_Joe');
		$subdir->addChild($file);
		$topLevelDir = new \vfsStreamDirectory('Foo');
		$topLevelDir->addChild($subdir);


		\vfsStreamWrapper::register();
		\vfsStreamWrapper::setRoot($topLevelDir);

		$loader = new GenericLoader('\\', \vfsStream::url(''));
		$loader->addLibrary('Foo');
		$loader->register();

		ob_start();
		spl_autoload_call('Foo\\Bar_Joe\\Goo');
		$this->assertEquals('FOO\\BAR_JOE\\GOO.PHP', ob_get_clean());
	} // end testLoaderDoesNotReplaceUnderscoresToSlashesInNamespace();

	public function testSkippingUnknownLibraries()
	{
		$loader = new GenericLoader('\\', './foo/bar/');
		$loader->addLibrary('Dummy');
		$loader->register();

		spl_autoload_register(function($name){ echo 'yey'; return true; });

		ob_start();
		spl_autoload_call('Foo\\Bar');
		$this->assertEquals('yey', ob_get_clean());
	} // end testSkippingUnknownClasses();
} // end GenericLoaderTest;