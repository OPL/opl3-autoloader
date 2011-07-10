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
use Opl\Autoloader\Toolset\Configuration;
use Opl\Autoloader\Toolset\CoreDump as CoreDumpTool;
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
class CoreDump extends Command
{
	/**
	 * @see Command
	 */
	protected function configure()
	{
		$this->ignoreValidationErrors = true;
		
		$this->setDefinition(array(
			new InputArgument('configuration', InputArgument::REQUIRED, 'The OPA configuration file.'),
		))
			->addOption('core', 'c', InputOption::VALUE_REQUIRED, 'The path to the core file.')
			->addOption('export-type', 'e', InputOption::VALUE_REQUIRED, 'Export type: \'require\' or \'concat\'')
			->addOption('strip-comments', 's', InputOption::VALUE_NONE, 'Strip comment headers in the concatenated files.')
			->setName('opl:autoloader:core-dump-export')
			->setDescription('Generates a list of require statements that load the common application core.')
			->setHelp(<<<EOF
Use the <info>CoreTracker</info> autoloader decorator to find the common application
core by sending some HTTP requests. The more requests you perform, the more precise
the lookup is. The command requires the path to the Open Power Autoloader XML configuration
file to be passed. Please refer to the library manual to read more about it.

The default export type is \'require\' which generates a list of \'require\' statements
that load the core files. Another supported type is \'concat\' which concatenates the
core files into a single PHP file. Use it with -s option to strip the header comments
from the concatenated files. The concatenation is safe both for namespaces and non-namespaced
code.
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
		
		$core = $input->getOption('core');
		if(!$configuration->hasFile('core-dump') && empty($core))
		{
			$output->writeln('<error>The path to the core dump file is missing.</error>');
			$output->writeln('Hint: add \'core-dump\' file type to export-files section or use the --core option.');
			return;
		}
		elseif(empty($core))
		{
			$core = $configuration->getFile('core-dump');
		}
		if(!$configuration->hasFile('core-export'))
		{
			$output->writeln('<error>The path to the core export file is missing.</error>');
			$output->writeln('Hint: add \'core-export\' file type to export-files section.');
			return;
		}
		$exportType = $input->getOption('export-type');
		if(empty($exportType))
		{
			$exportType = 'require';
		}
		elseif($exportType != 'require' && $exportType != 'concat')
		{
			$output->writeln('<error>Invalid value for the --export-type option: \'require\' or \'concat\' expected.</error>');
			return;
		}
		
		$dump = new CoreDumpTool();
		foreach($configuration->getSeparators() as $separator)
		{
			foreach($configuration->getSeparatorNamespaces($separator) as $name => $namespace)
			{
				$dump->addNamespace($name, $namespace['path'], $namespace['extension']);
			}
		}
		$dump->loadCore($core);
		if($exportType == 'require')
		{
			$dump->exportRequireList($configuration->getFile('core-export'));
		}
		else
		{
			$dump->exportConcatenated($configuration->getFile('core-export'), $input->getOption('strip-comments') ? true : false);
		}
		
		$output->writeln('<info>The core loader has been successfully exported.</info>');
	} // end execute();
} // end CoreDump;