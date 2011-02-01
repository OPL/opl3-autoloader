<?php
/*
 *  OPEN POWER LIBS <http://www.invenzzia.org>
 *
 * This file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE. It is also available through
 * WWW at this URL: <http://www.invenzzia.org/license/new-bsd>
 *
 * Copyright (c) Invenzzia Group <http://www.invenzzia.org>
 * and other contributors. See website for details.
 */
namespace Opl\Autoloader\Command;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use Opl\Autoloader\ClassMapBuilder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;

/**
 * This command line interface command is responsible for building
 * class maps for the ClassMapLoader.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class ClassMapBuild extends Command
{
	/**
	 * The class map builder.
	 * @var ClassMapBuilder
	 */
	protected $_builder;

	/**
	 * @see Command
	 */
	protected function configure()
	{
		$this->ignoreValidationErrors = true;

		$this->setDefinition(array(
			new InputArgument('definition', InputArgument::REQUIRED, 'The class map definition INI file'),
		))
			->setName('opl:autoloader:build-class-map')
			->setDescription('Generates the class map for the ClassMapLoader')
			->setHelp(<<<EOF
The <info>autoloader:class-map:build</info> command is responsible for building
the class maps for the ClassMapLoader autoloader. The configuration is given as
an INI file, where each entry represents a single library and a path to its code:

  [config]
  extension = "php"
  
  [libraries]
  Opl = "../libs/"
  Foo = "../libs/"
  Bar = "../other/"

It is recommended for the paths to have the trailing slashes prepended.
EOF
			);
	} // end configure();

	/**
	 * @see Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$definition = $input->getArgument('definition');
		if(!$definition)
		{
			$output->writeln('<error>No definition file specified!</error>');
			return;
		}

		if(!file_exists($definition))
		{
			$output->writeln('<error>The specified definition file does not exist!</error>');
		}
		$data = parse_ini_file($definition, true);
		if(!is_array($data))
		{
			$output->writeln('<error>Invalid INI structure in the definition file!</error>');
		}

		$extension = 'php';
		$outputFile = './class-map.txt';
		if(isset($data['config']))
		{
			if(isset($data['config']['extension']))
			{
				$extension = $data['config']['extension'];
			}
			if(isset($data['config']['outputFile']))
			{
				$outputFile = $data['config']['outputFile'];
			}
		}

		if(!isset($data['libraries']))
		{
			$output->writeln('<error>No libraries specified!</error>');
		}

		$this->_builder = new ClassMapBuilder();
		foreach($data['libraries'] as $name => $path)
		{
			$this->_processSingleLibrary($output, $name, $path, $extension);
		}
		file_put_contents($outputFile, serialize($this->_builder->getMap()));
		$output->writeln('<info>Map saved as:</info> '.$outputFile);
	} // end execute();

	/**
	 * Processes a single class library.
	 *
	 * @param string $name
	 * @param string $path
	 */
	protected function _processSingleLibrary(OutputInterface $output, $libraryName, $path, $extension)
	{
		$errors = $this->_builder->addLibrary($libraryName, $path, $extension);

		foreach($errors as $error)
		{
			$output->writeln(preg_replace('/^(([^\:]+)\:) (.*)$/', '<error>$1</error> $2', $error));
		}
	} // end _processSingleLibrary();
} // end ClassMapBuild;