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
			new InputArgument('definition', InputArgument::REQUIRED, 'The class location definition INI file'),
			new InputArgument('core', InputArgument::OPTIONAL, 'The core file location, unless specified in the INI file.'),
		))
			->setName('opl:autoloader:core-dump-load')
			->setDescription('Generates a list of require statements that load the common application core.')
			->setHelp(<<<EOF
Use the <info>CoreTracker</info> autoloader decorator to find the common application
core by sending some HTTP requests. The more requests you perform, the more precise
the lookup is. The configuration for the command is given as
an INI file, where each entry represents a single top-level namespace and a path to its code:

  [config]
  coreDump = "./output/core.txt"
  coreLoadOutput = "./application/core.php"
  namespaceSeparator = "\\"
  extension = ".php"

  [namespaces]
  Opl = "../libs/"
  Foo = "../libs/"
  Bar = "../other/"

It is recommended for the paths to have the trailing slashes prepended. The coreDump
value can be also provided as a command argument. The INI setting is ignored then.
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
		$data = parse_ini_file($definition, true);
		if(!is_array($data))
		{
			$output->writeln('<error>Invalid INI structure in the definition file!</error>');
			return;
		}

		$coreDump = $input->getArgument('core');
		if($coreDump)
		{
			$data['config']['coreDump'] = $coreDump;
		}
		
		if(!file_exists($data['config']['coreDump']))
		{
			$output->writeln('<error>Cannot open the core dump file!</error>');
			return;
		}
		$dump = unserialize(file_get_contents($data['config']['coreDump']));
		$outFile = fopen($data['config']['coreLoadOutput'], 'w');
		fwrite($outFile, '<'.'?php'.PHP_EOL);
		
		foreach($dump as $className)
		{
			$fileName = $this->toFilename($data['namespaces'], $data['config']['namespaceSeparator'], $className, $output);
			if(false !== $fileName)
			{
				fwrite($outFile, 'require(\''.$fileName.$data['config']['extension'].'\');'.PHP_EOL);
			}
		}
		fclose($outFile);
		$output->writeln('<info>Core loading file generated.</info>');
	} // end execute();
	
	/**
	 * Returns the file name for the given class name.
	 *
	 * @param array $namespaces The list of available namespaces.
	 * @param string $className The class name to translate.
	 * @param OutputInterface $output The output interface.
	 */
	protected function toFilename(array $namespaces, $namespaceSeparator, $className, OutputInterface $output)
	{
		$className = ltrim($className, $namespaceSeparator);
		$match = strstr($className, $namespaceSeparator, true);

		if(false === $match || !isset($namespaces[$match]))
		{
			return false;
		}
		$rest = strrchr($className, $namespaceSeparator);
		$replacement =
			str_replace($namespaceSeparator, '/', substr($className, 0, strlen($className) - strlen($rest))).
			str_replace(array('_', $namespaceSeparator), '/', $rest);
		
		return $namespaces[$match].$replacement;
	} // end toFilename();
} // end CoreDump;