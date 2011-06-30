<?php
/**
 * The test suite file that configures the execution of the test cases.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite;

class AllTests extends \PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new AllTests('TestSuite');

		$suite->addTestSuite('TestSuite\\GenericLoaderTest');
		$suite->addTestSuite('TestSuite\\ClassMapLoaderTest');
		$suite->addTestSuite('TestSuite\\PHARLoaderTest');
		$suite->addTestSuite('TestSuite\\UniversalLoaderTest');
		$suite->addTestSuite('TestSuite\\CoreTrackerTest');
		
		$suite->addTestSuite('TestSuite\\Toolset\\AllTests');

		return $suite;
	} // end suite();
} // end AllTests;