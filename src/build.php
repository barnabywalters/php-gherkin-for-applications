<?php
#!/usr/local/php

// Eventually this will be the command line tool which builds applications into
// phar files and launches the web UI and such things. At the moment it’s just
// a tiny stub.

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console;

$app = new Console\Application;

$app->register('build')
	->setDefinition([
		new Console\Input\InputArgument('dir', Console\Input\InputArgument::OPTIONAL, 'Project Folder', 'CURRENT_DIRECTORY')
	])
	->setDescription('Packages a .phar file from a project directory (current dir by default)')
	->setCode(function (Console\Input\InputInterface $input, Console\Output\OutputInterface $output) {
		$dir = $input->getArgument('dir');
		
		if ($dir == 'CURRENT_DIRECTORY')
			$dir = getcwd();
		
		$dir = realpath($dir);
		
		$output->writeln("Building project from {$dir}");
	});

$app->run();
