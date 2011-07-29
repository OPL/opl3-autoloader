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
use Opl\Autoloader\UniversalLoader;
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
	public function testAddLoaderThrowsExceptionIfTheArgumentIsInvalid1()
	{
		$tracker = new CoreTracker('./cache/core.txt');	
		$tracker->addLoader(null);
	} // end testAddLoaderThrowsExceptionIfTheArgumentIsInvalid1();

	/**
	 * @expectedException DomainException
	 */
	public function testAddLoaderThrowsExceptionIfTheArgumentIsInvalid2()
	{
		$tracker = new CoreTracker('./cache/core.txt');	
		$tracker->addLoader(new stdClass());
	} // end testAddLoaderThrowsExceptionIfTheArgumentIsInvalid2();
	
	public function testRegisterWorks()
	{
		$loader = new GenericLoader('./foo/bar/');
		$tracker = new CoreTracker('./cache/core.txt');
		$tracker->addLoader($loader);
		$tracker->register();
		
		$functions = spl_autoload_functions();
		$this->assertContains(array($tracker, 'loadClass'), $functions);
	} // end testRegisterWorks();
	
	public function testGetLoadersReturnsAutoloaderList()
	{
		$loader = new GenericLoader('./foo/bar/');
		$tracker = new CoreTracker('./cache/core.txt');
		$tracker->addLoader($loader);
		
		$this->assertSame(array($loader), $tracker->getLoaders());
	} // end testGetLoadersReturnsAutoloaderList();

	public function testUnregisterWorks()
	{
		$loader = new GenericLoader('./foo/bar/');
		$tracker = new CoreTracker('./cache/core.txt');
		$tracker->addLoader($loader);
		$tracker->register();

		$functions = spl_autoload_functions();
		$this->assertContains(array($tracker, 'loadClass'), $functions);

		$tracker->unregister();

		$functions = spl_autoload_functions();
		$this->assertThat($functions, $this->logicalNot($this->contains(array($tracker, 'loadClass'))));
	} // end testUnregisterWorks();

	public function testCreatingTheInitialFile()
	{
	
		unlink('./cache/core.txt');

		$loader = new GenericLoader('./data/');
		$loader->addNamespace('Core');
		
		ob_start();
		
		$tracker = new CoreTracker('./cache/core.txt');
		$tracker->addLoader($loader);
		$tracker->register();
		$tracker->loadClass('Core\\Subcore1\\Bar');
		$tracker->unregister();
		unset($tracker);
		
		$this->assertEquals('CORE\SUBCORE1\BAR'.PHP_EOL, ob_get_clean());

		$result = unserialize(file_get_contents('./cache/core.txt'));
		$this->assertEquals(array('Core\\Subcore1\\Bar'), $result);
	} // end testCreatingTheInitialFile();
	
	public function testUpdatingTheFile()
	{		
		unlink('./cache/core.txt');

		ob_start();
		$loader = new GenericLoader('./data/');
		$loader->addNamespace('Core');
		
		$tracker = new CoreTracker('./cache/core.txt');
		$tracker->addLoader($loader);
		$tracker->register();
		$tracker->loadClass('Core\\Subcore1\\Bar');
		$tracker->loadClass('Core\\Subcore1\\Joe');
		$tracker->unregister();
		unset($tracker);

		$result = unserialize(file_get_contents('./cache/core.txt'));
		$this->assertEquals(array('Core\\Subcore1\\Bar', 'Core\\Subcore1\\Joe'), $result);
		
		$tracker = new CoreTracker('./cache/core.txt');
		$tracker->addLoader($loader);
		$tracker->register();
		$tracker->loadClass('Core\\Subcore1\\Bar');
		$tracker->loadClass('Core\\Subcore1\\Foo');
		$tracker->unregister();
		unset($tracker);
		
		$this->assertEquals('CORE\SUBCORE1\BAR'.PHP_EOL.'CORE\SUBCORE1\JOE'.PHP_EOL.'CORE\SUBCORE1\BAR'.PHP_EOL.'CORE\SUBCORE1\FOO'.PHP_EOL, ob_get_clean());

		$result = unserialize(file_get_contents('./cache/core.txt'));
		$this->assertEquals(array('Core\\Subcore1\\Bar'), $result);
	} // end testUpdatingTheFile();
	
	public function testLoadingInterfacesAndTraits()
	{
		unlink('./cache/core.txt');
		
		$loader = new GenericLoader('./data/');
		$loader->addNamespace('Core');
		
		$tracker = new CoreTracker('./cache/core.txt');
		$tracker->addLoader($loader);
		$tracker->register();
		
		ob_start();
		
		if(version_compare(phpversion(), '5.3.99-dev', '<'))
		{
			spl_autoload_call('Core\Subcore1\SomeInterface');
			$this->assertEquals('CORE\SUBCORE1\SOMEINTERFACE'.PHP_EOL, ob_get_clean());
			
			$tracker->unregister();
			unset($tracker);
			
			$result = unserialize(file_get_contents('./cache/core.txt'));
			$this->assertEquals(array('Core\\Subcore1\\SomeInterface'), $result);
		}
		else
		{
			spl_autoload_call('Core\Subcore1\SomeInterface');
			spl_autoload_call('Core\Subcore1\SomeTrait');
			$this->assertEquals('CORE\SUBCORE1\SOMEINTERFACE'.PHP_EOL.'CORE\SUBCORE1\SOMETRAIT'.PHP_EOL, ob_get_clean());
			
			$tracker->unregister();
			unset($tracker);
			
			$result = unserialize(file_get_contents('./cache/core.txt'));
			$this->assertEquals(array('Core\\Subcore1\\SomeInterface', 'Core\\Subcore1\\SomeTrait'), $result);
		}
	} // end testLoadingInterfacesAndTraits();
	
	public function testMultipleLoaders()
	{
		$tracker = new CoreTracker('./cache/core.txt');
		
		$loader = new UniversalLoader('./data/');
		$loader->addNamespace('Core\\Subcore1');
		$tracker->addLoader($loader);
		
		$loader = new UniversalLoader('./data/');
		$loader->addNamespace('Core\\Subcore2');
		$tracker->addLoader($loader);
		$tracker->register();
		
		ob_start();
		
		$obj1 = new \Core\Subcore1\Foo();
		$obj2 = new \Core\Subcore1\Bar();
		$obj3 = new \Core\Subcore2\Foo();
		
		ob_end_clean();
		
		$this->assertTrue(is_object($obj1));
		$this->assertTrue(is_object($obj2));
		$this->assertTrue(is_object($obj3));
	} // end testMultipleLoaders();
} // end CoreTrackerTest;