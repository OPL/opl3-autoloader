<?php
/**
 * This is a simple test utility that allows to produce a sample application
 * core for the tests.
 */

$list = array(
	'Dummy\\ShortFile',
	'Dummy\\DifferentNamespaceStyle',
	'Dummy_Subdirectory_NoNamespace',
	'Dummy\\Subdirectory\\SubdirSupport'	
);

file_put_contents('./data/core.txt', serialize($list));