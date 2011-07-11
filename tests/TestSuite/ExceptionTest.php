<?php
/**
 * Unit tests for Open Power Autoloader
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009-2011 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite;
use Opl\Autoloader\Exception\TranslationException;
use Opl\Autoloader\Exception\FileNotFoundException;
use Opl\Autoloader\Exception\FileFormatException;
use RuntimeException;

/**
 * @covers Opl\Autoloader\Exception\TranslationException
 * @covers Opl\Autoloader\Exception\FileNotFoundException
 * @covers Opl\Autoloader\Exception\FileFormatException
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{
	public function testTranslationException()
	{
		$exception = new TranslationException('Foo');
		$this->assertEquals('Foo', $exception->getMessage());
		$this->assertTrue($exception instanceof RuntimeException);
	} // end testTranslationException();
	
	public function testFileNotFoundException()
	{
		$exception = new FileNotFoundException('Foo');
		$this->assertEquals('Foo', $exception->getMessage());
		$this->assertTrue($exception instanceof RuntimeException);
	} // end testFileNotFoundException();
	
	public function testFileFormatException()
	{
		$exception = new FileFormatException('Foo');
		$this->assertEquals('Foo', $exception->getMessage());
		$this->assertTrue($exception instanceof RuntimeException);
	} // end testFileFormatException();
} // end ExceptionTest;