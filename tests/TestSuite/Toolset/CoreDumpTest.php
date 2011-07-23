<?php
/**
 * Unit tests for Open Power Autoloader
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009-2011 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite\Toolset;
use Opl\Autoloader\Toolset\CoreDump;

/**
 * @covers \Opl\Autoloader\Toolset\CoreDump
 */
class CoreDumpTest extends \PHPUnit_Framework_TestCase
{
	public function testLoadCoreLoadsTheCore()
	{
		$coreDump = new CoreDump();
		$coreDump->loadCore('./data/core.txt');
		
		$reflection = new \ReflectionObject($coreDump);
		$coreProperty = $reflection->getProperty('core');
		$coreProperty->setAccessible(true);
		
		$this->assertEquals(array(
			'Dummy\\ShortFile',
			'Dummy\\DifferentNamespaceStyle',
			'Dummy_Subdirectory_NoNamespace',
			'Dummy\\Subdirectory\\SubdirSupport'	
		), $coreProperty->getValue($coreDump));
	} // end testLoadCoreLoadsTheCore();
	
	/**
	 * @expectedException Opl\Autoloader\Exception\FileNotFoundException
	 */
	public function testLoadCoreThrowsExceptionIfFileDoesNotExist()
	{
		$coreDump = new CoreDump();
		$coreDump->loadCore('./data/does_not_exist.txt');
	} // end testLoadCoreThrowsExceptionIfFileDoesNotExist();
	
	public function testExportRequireListProducesRequireList()
	{
		$coreDump = new CoreDump();
		$coreDump->loadCore('./data/core.txt');
		$coreDump->addNamespace('Dummy', './data/');
		
		$coreDump->exportRequireList('./cache/output.php');
		
		$this->assertEquals(file_get_contents('./data/outputs/testExportRequireListProducesRequireList.php'), file_get_contents('./cache/output.php'));
	} // end testExportRequireListProducesRequireList();
	
	public function testExportConcatenatedConcatenatesTheFiles()
	{
		$coreDump = new CoreDump();
		$coreDump->loadCore('./data/core.txt');
		$coreDump->addNamespace('Dummy', './data/');
		
		$coreDump->exportConcatenated('./cache/output.php', false);
		
		$this->assertEquals(file_get_contents('./data/outputs/testExportConcatenatedConcatenatesTheFiles.php'), file_get_contents('./cache/output.php'));
	} // end testExportConcatenatedConcatenatesTheFiles();
	
	public function testExportConcatenatedCanRemoveHeadingComments()
	{
		$coreDump = new CoreDump();
		$coreDump->loadCore('./data/core.txt');
		$coreDump->addNamespace('Dummy', './data/');
		
		$coreDump->exportConcatenated('./cache/output.php');
		
		$this->assertEquals(file_get_contents('./data/outputs/testExportConcatenatedCanRemoveHeadingComments.php'), file_get_contents('./cache/output.php'));		
	} // end testExportConcatenatedCanRemoveHeadingComments();
} // end CoreDumpTest;