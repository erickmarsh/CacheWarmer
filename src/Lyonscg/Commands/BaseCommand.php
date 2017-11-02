<?php
	
namespace Lyonscg\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Lyonscg\Firehose;

/*
*   BaseCommand from which all other commands inherit
*   Sets options that are included with every command and
*   sets the firehose
*/
class BaseCommand extends Command
{
    
    public $firehose = null;
    
    protected function default_configure()
    {
                    
        $this->firehose = new \Lyonscg\Firehose\Firehose();
        
        $this
            ->addOption(
               'threads',
               't',
               InputOption::VALUE_OPTIONAL,
               'Number of threads to run at once (5)',
				5
            )  
            ->addOption(
               'wait',
               'w',
               InputOption::VALUE_OPTIONAL,
               'seconds to wait between each blast (5)',
			   5
            )
            ->addOption(
                'intermediate_file',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Intermediary file to write. Provide full path. Make sure it is writeable',
                __DIR__. "/../../../var/urls.txt"
            );
	}
    
    /*
    *   Writes the list of urls that are being requested to the 
    *   intermediate file.
    *
    *   $urls: array of urls
    *   $intermediate_file: path of file to write to
    */
    protected function write_urls_to_file($urls, $intermediate_file)
    {
        $url_string = implode(PHP_EOL, $urls);
        file_put_contents($intermediate_file, $url_string);
    }
}