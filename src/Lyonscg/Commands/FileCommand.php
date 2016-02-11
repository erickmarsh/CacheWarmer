<?php

namespace Lyonscg\Commands;
	
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/*
*   FileCommand is used when "warm:file" is used as the first param in the command line
*
*
*/
class FileCommand extends BaseCommand
{
    
    /*
    *   Sets the config options for this command
    */
    protected function configure()
    {
	 	$this
            ->setName('warm:file')
            ->setDescription('warm the site based on file of urls')
            ->addArgument(
                'file_path',
                InputArgument::REQUIRED,
                'Path to file'
            );	
        
        // have to add defaults after required fields are already set
        $this->default_configure(); 
	}
	
    /*
    *   execute reads the specified file in the command line and passes
    *   the list of the urls to the firehose
    *
    */
	protected function execute(InputInterface $input, OutputInterface $output)
    {
		$filepath = $input->getArgument('file_path');
		
		if (!file_exists($filepath) || !is_readable($filepath))
		{
			throw new \Exception("File not found or is not readable: ". $filepath);
		}
		
		// Read the urls from the file
		$urls = file($filepath);
		
		$text =  PHP_EOL ."Threads: ". $input->getOption("threads");
        $text .= PHP_EOL ."Wait: ".    $input->getOption("wait") ." Seconds";
        $output->writeln($text);
        
        $this->firehose->blast($urls, $input->getOption("threads"), $input->getOption("wait"));
	}
	
}