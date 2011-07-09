<?php
/**
 * Unit tests for Open Power Autoloader
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009-2011 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite\Toolset;
use Opl\Autoloader\Toolset\Configuration;

/**
 * @covers \Opl\Autoloader\Toolset\Configuration
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
	public function testLoadCorrectFile()
	{
		$configuration = new Configuration('./data/configs/correctConfig.xml');
		
		$this->assertEquals('<?php
/**
 * The index.php beginning
 */
', $configuration->getFileHeader());
		$this->assertEquals('
$application = new Application();
$application->start();', $configuration->getFileFooter());
		$this->assertEquals(array('\\', '_'), $configuration->getSeparators());
		
		$this->assertEquals(array(
			'serialized-class-map' => './data/classMap.txt',
			'chdb-class-map' => './data/classMap.chdb',
			'core-dump' => './data/coreDump.txt',
			'core-export' => './web/core.php',
			'index' => './web/index.php',
			'cli' => './cli/cli.php'
		), $configuration->getFiles());
		
		$this->assertEquals(array(
			'Opl' => array('path' => './src/Opl', 'extension' => '.php'),
			'Symfony' => array('path' => './src/Symfony', 'extension' => '.php'),
			'Doctrine\DBAL' => array('path' => './src/DBAL', 'extension' => '.php'),
			'Doctrine\ORM' => array('path' => './src/ORM', 'extension' => '.php5'),
		), $configuration->getSeparatorNamespaces('\\'));
		$this->assertEquals(array(
			'Zend' => array('path' => './src/Zend', 'extension' => '.php')
		), $configuration->getSeparatorNamespaces('_'));
		
		$this->assertFalse($configuration->hasSimpleNamespacesOnly());
	} // end testLoadCorrectFile();
	
	public function testGetFilesCanIgnoreSpecialTypes()
	{
		$configuration = new Configuration('./data/configs/correctConfig.xml');
		$this->assertEquals(array(
			'index' => './web/index.php',
			'cli' => './cli/cli.php'
		), $configuration->getFiles(true));
	} // end testGetFilesCanIgnoreSpecialTypes();
	
	/**
	 * @expectedException OutOfBoundsException
	 */
	public function testGetSeparatorNamespacesThrowsExceptionIfSeparatorIsNotDefined()
	{
		$configuration = new Configuration('./data/configs/correctConfig.xml');
		
		$this->assertEquals('array', gettype($configuration->getSeparatorNamespaces('_')));
		$configuration->getSeparatorNamespaces('joe');
	} // end test testGetSeparatorNamespacesThrowsExceptionIfSeparatorIsNotDefined();
	
	public function testGetFileReturnsTheFileOfTheGivenType()
	{
		$configuration = new Configuration('./data/configs/correctConfig.xml');
		$this->assertEquals('./data/classMap.txt', $configuration->getFile('serialized-class-map'));
		$this->assertEquals('./web/index.php', $configuration->getFile('index'));
	} // end testGetFileReturnsTheFileOfTheGivenType();

	/**
	 * @expectedException OutOfBoundsException
	 */
	public function testGetFileThrowsExceptionIfTypeIsNotDefined()
	{
		$configuration = new Configuration('./data/configs/correctConfig.xml');
		$this->assertTrue($configuration->hasFile('index'));
		$this->assertEquals('./web/index.php', $configuration->getFile('index'));
		$this->assertFalse($configuration->hasFile('joe'));
		$configuration->getFile('joe');
	} // end testGetFileThrowsExceptionIfTypeIsNotDefined();
	
	public function testLoadSimpleConfigFile()
	{
		$configuration = new Configuration('./data/configs/simpleConfig.xml');
		
		$this->assertEquals(array(
			'Opl' => array('path' => './src/Opl', 'extension' => '.php'),
			'Symfony' => array('path' => './src/Symfony', 'extension' => '.php'),
			'Doctrine' => array('path' => './src/Doctrine', 'extension' => '.php'),
		), $configuration->getSeparatorNamespaces('\\'));
		$this->assertTrue($configuration->hasSimpleNamespacesOnly());
	} // end testLoadSimpleConfigFile();
	
	public function testLoadNoHeadings()
	{
		$configuration = new Configuration('./data/configs/noHeadings.xml');
		$this->assertEquals('<?php'.PHP_EOL, $configuration->getFileHeader());
		$this->assertEquals(null, $configuration->getFileFooter());
	} // end testLoadNoHeadings();
	
	/**
	 * @expectedException Opl\Autoloader\Exception\FileNotFoundException
	 */
	public function testExceptionIfFileDoesNotExist()
	{
		$configuration = new Configuration('./data/configs/doesNotExist.xml');
	} // end testExceptionIfFileDoesNotExist();
	
	/**
	 * @expectedException Opl\Autoloader\Exception\FileFormatException
	 */
	public function testLoadInvalidFile()
	{
		$configuration = new Configuration('./data/configs/invalidFile.xml');
	} // end testLoadInvalidFile();
	
	/**
	 * @expectedException Opl\Autoloader\Exception\FileFormatException
	 */
	public function testLoadMissingFileAttribute()
	{
		$configuration = new Configuration('./data/configs/missingFileAttribute.xml');
	} // end testLoadMissingFileAttribute();
	
	/**
	 * @expectedException Opl\Autoloader\Exception\FileFormatException
	 */
	public function testLoadMissingNamespaceAttribute()
	{
		$configuration = new Configuration('./data/configs/missingNamespaceAttribute.xml');
	} // end testLoadMissingNamespaceAttribute();
	
	/**
	 * @expectedException Opl\Autoloader\Exception\FileFormatException
	 */
	public function testLoadMissingSeparatorAttribute()
	{
		$configuration = new Configuration('./data/configs/missingSeparatorAttribute.xml');
	} // end testLoadMissingSeparatorAttribute();
} // end ConfigurationTest;