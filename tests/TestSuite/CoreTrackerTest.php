<?php
/**
 * Unit tests for Open Power Autoloader
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009-2011 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite;
use Opl\Autoloader\CoreTracker;
use Opl\Autoloader\GenericLoader;
use stdClass;
require_once 'vfsStream/vfsStream.php';

/**
 * @covers \Opl\Autoloader\CoreTracker
 * @runTestsInSeparateProcesses
 */
class CoreTrackerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException DomainException
	 */
	public function testInvalidFirstArgument1()
	{
		$tracker = new CoreTracker(null, './cache/core.txt');	
	} // end testInvalidFirstArgument1();
	
	/**
	 * @expectedException DomainException
	 */
	public function testInvalidFirstArgument2()
	{
		$tracker = new CoreTracker(new stdClass(), './cache/core.txt');	
	} // end testInvalidFirstArgument2();
	
	public function testRegisterWorks()
	{
		$loader = new GenericLoader('./foo/bar/');
		$tracker = new CoreTracker($loader, './cache/core.txt');	
		$tracker->register();
		
		$functions = spl_autoload_functions();
		$this->assertContains(array($tracker, 'loadClass'), $functions);
	} // end testRegisterWorks();
	
	public function testGetAutoloaderReturnsAutoloader()
	{
		$loader = new GenericLoader('./foo/bar/');
		$tracker = new CoreTracker($loader, './cache/core.txt');	
		
		$this->assertSame($loader, $tracker->getAutoloader());
	} // end testGetAutoloaderReturnsAutoloader();

	public function testUnregisterWorks()
	{
		$loader = new GenericLoader('./foo/bar/');
		$tracker = new CoreTracker($loader, './cache/core.txt');	
		$tracker->register();

		$functions = spl_autoload_functions();
		$this->assertContains(array($tracker, 'loadClass'), $functions);

		$tracker->unregister();

		$functions = spl_autoload_functions();
		$this->assertThat($functions, $this->logicalNot($this->contains(array($tracker, 'loadClass'))));
	} // end testUnregisterWorks();

	public function testCreatingTheInitialFile()
	{
		$file = new \vfsStreamFile('Bar.php');
		$file->setContent('<?php echo "FOO\BAR.PHP"; ');
		$topLevelDir = new \vfsStreamDirectory('Foo');
		$topLevelDir->addChild($file);

		\vfsStreamWrapper::register();
		\vfsStreamWrapper::setRoot($topLevelDir);
		
		unlink('./cache/core.txt');

		$loader = new GenericLoader(\vfsStream::url(''));
		$loader->addNamespace('Foo');
		
		ob_start();
		
		$tracker = new CoreTracker($loader, './cache/core.txt');	
		$tracker->register();
		$tracker->loadClass('Foo\\Bar');
		$tracker->unregister();
		unset($tracker);
		
		$this->assertEquals('FOO\BAR.PHP', ob_get_clean());

		$result = unserialize(file_get_contents('./cache/core.txt'));
		$this->assertEquals(array('Foo\\Bar'), $result);
	} // end testCreatingTheInitialFile();
	
	public function testUpdatingTheFile()
	{
		$file = new \vfsStreamFile('Bar.php');
		$file->setContent('<?php echo "FOO\BAR.PHP"; ');
		$topLevelDir = new \vfsStreamDirectory('Foo');
		$topLevelDir->addChild($file);
		
		$file = new \vfsStreamFile('Joe.php');
		$file->setContent('<?php echo "FOO\JOE.PHP"; ');
		$topLevelDir->addChild($file);
		
		$file = new \vfsStreamFile('Goo.php');
		$file->setContent('<?php echo "FOO\GOO.PHP"; ');
		$topLevelDir->addChild($file);
		
		\vfsStreamWrapper::register();
		\vfsStreamWrapper::setRoot($topLevelDir);
		
		unlink('./cache/core.txt');

		ob_start();
		$loader = new GenericLoader(\vfsStream::url(''));
		$loader->addNamespace('Foo');
		
		$tracker = new CoreTracker($loader, './cache/core.txt');	
		$tracker->register();
		$tracker->loadClass('Foo\\Bar');
		$tracker->loadClass('Foo\\Joe');
		$tracker->unregister();
		unset($tracker);

		$result = unserialize(file_get_contents('./cache/core.txt'));
		$this->assertEquals(array('Foo\\Bar', 'Foo\\Joe'), $result);
		
		$tracker = new CoreTracker($loader, './cache/core.txt');	
		$tracker->register();
		$tracker->loadClass('Foo\\Bar');
		$tracker->loadClass('Foo\\Goo');
		$tracker->unregister();
		unset($tracker);
		
		$this->assertEquals('FOO\BAR.PHPFOO\JOE.PHPFOO\BAR.PHPFOO\GOO.PHP', ob_get_clean());

		$result = unserialize(file_get_contents('./cache/core.txt'));
		$this->assertEquals(array('Foo\\Bar'), $result);
	} // end testUpdatingTheFile();
} // end CoreTrackerTest;