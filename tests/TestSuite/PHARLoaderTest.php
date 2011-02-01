<?php
/**
 * Unit tests for Open Power Autoloader
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite;
use Phar;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Opl\Autoloader\PHARLoader;
use Opl\Autoloader\ClassMapBuilder;
require_once('PHPUnit/Framework/Error.php');

/**
 * @covers \Opl\Autoloader\PHARLoader
 * @runTestsInSeparateProcesses
 */
class PHARLoaderTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		if(!is_dir('./cache/'))
		{
			mkdir('./cache/');
		}
		$phar = new Phar('./cache/pharloader.phar');
		$phar->startBuffering();

		$phar->buildFromDirectory('./data/');

		$builder = new ClassMapBuilder();
		$builder->addLibrary('Dummy', './data/');

		$phar->setStub(file_get_contents(__DIR__.'/../../src/Opl/Autoloader/PHARLoader.php').'
$loader = new PHARLoader('.var_export($builder->getMap(), true).');
$loader->register();
__HALT_COMPILER();');

		$phar->stopBuffering();
	} // end setUp();

	public function testRegisterWorks()
	{
		$loader = new PHARLoader(array());
		$loader->register();

		$functions = spl_autoload_functions();
		$this->assertContains(array($loader, 'loadClass'), $functions);
	} // end testRegisterWorks();

	public function testUnregisterWorks()
	{
		$loader = new PHARLoader(array());
		$loader->register();

		$functions = spl_autoload_functions();
		$this->assertContains(array($loader, 'loadClass'), $functions);

		$loader->unregister();

		$functions = spl_autoload_functions();
		$this->assertThat($functions, $this->logicalNot($this->contains(array($loader, 'loadClass'))));
	} // end testUnregisterWorks();

	public function testLoadingFromPhar()
	{
		require('./cache/pharloader.phar');

		// No error should occur.
		$object = new \Dummy\ShortFile();
	} // end testLoadingFromPhar();

	public function testSkippingUnknownClasses()
	{
		require('./cache/pharloader.phar');

		spl_autoload_register(function($name){ echo 'yey'; return true; });

		ob_start();
		spl_autoload_call('Foo\\Bar');
		$this->assertEquals('yey', ob_get_clean());
	} // end testSkippingUnknownClasses();
} // end PHARLoaderTest;