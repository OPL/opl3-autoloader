<?php
/**
 * The test suite file that configures the execution of the test cases.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite\Toolset;

class AllTests extends \PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new AllTests('TestSuite\Toolset');

		$suite->addTestSuite('TestSuite\\Toolset\\AbstractToolTest');
		$suite->addTestSuite('TestSuite\\Toolset\\ClassMapBuilderTest');
		$suite->addTestSuite('TestSuite\\Toolset\\CoreDumpTest');
		$suite->addTestSuite('TestSuite\\Toolset\\ConfigurationTest');
		
		return $suite;
	} // end suite();
} // end AllTests;