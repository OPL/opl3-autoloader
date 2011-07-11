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
use Opl\Autoloader\Toolset\ClassMapBuilder;
use Opl\Autoloader\Toolset\Configuration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use RuntimeException;

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
	 * @see Command
	 */
	protected function configure()
	{
		$this->ignoreValidationErrors = true;

		$this->setDefinition(array(
			new InputArgument('configuration', InputArgument::REQUIRED, 'The Open Power Autoloader configuration'),
		))
			->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Map type: \'serialized\' or \'chdb\'')
			->setName('opl:autoloader:build-class-map')
			->setDescription('Generates the class map for the ClassMapLoader')
			->setHelp(<<<EOF
The <info>autoloader:class-map:build</info> command is responsible for building
the class maps for the ClassMapLoader autoloader. The configuration file is an
XML document. Please refer to the OPA user manual to get to know more.

The extra option \'type\' defines the output format: either a serialized array or
a memory-mapped file for \'chdb\' PECL extension (Unix only).
EOF
			);
	} // end configure();

	/**
	 * @see Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		try
		{
			$configuration = new Configuration($input->getArgument('configuration'));
		}
		catch(RuntimeException $exception)
		{
			$output->writeln('<error>An error occured: '.$exception->getMessage().'</error>');
			return;
		}
		
		$type = $input->getOption('type');
		if(empty($type))
		{
			$type = 'serialized';
		}
		if($type != 'serialized' && $type != 'chdb')
		{
			$output->writeln('<error>Invalid type specified.</error>');
			return;
		}
		switch($type)
		{
			case 'serialized':
				$optionName = 'serialized-class-map';
				break;
			case 'chdb':
				if(!extension_loaded('chdb'))
				{
					$output->writeln('<error>chdb extension is not installed.</error>');
					return;
				}
				$optionName = 'chdb-class-map';
		}
		
		if(!$configuration->hasFile($optionName))
		{
			$output->writeln('<error>The class map file definition is missing in the configuration file!</error>');
			$output->writeln('Hint: add \''.$optionName.'\' file type to export-files section.');
			return;
		}

		$builder = new ClassMapBuilder();
		
		foreach($configuration->getSeparators() as $separator)
		{
			foreach($configuration->getSeparatorNamespaces($separator) as $name => $namespace)
			{
				$builder->addNamespace($name, $namespace['path'], $namespace['extension']);
			}
		}
		$errors = $builder->buildMap();
		foreach($errors as $error)
		{
			$output->writeln(preg_replace('/^(([^\:]+)\:) (.*)$/', '<error>Warning: $1</error> $3', $error));
		}
		
		if($type == 'serialized')
		{
			file_put_contents($configuration->getFile($optionName), serialize($builder->getMap()));
		}
		else
		{
			$fileName = $configuration->getFile($optionName);

			$map = array();
			foreach($builder->getMap() as $className => $data)
			{
				$map[$className] = serialize($data);
			}
			
			chdb_create($fileName.'.0', $map);
			rename($fileName.'.0', $fileName);
		}
		$output->writeln('<info>Map saved as:</info> '.$configuration->getFile($optionName));
	} // end execute();
} // end ClassMapBuild;