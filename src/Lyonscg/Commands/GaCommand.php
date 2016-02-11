<?php

namespace Lyonscg\Commands;
	
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Lyonscg\Ga\GoogleAnalytics;
/**
*   GaCommand gets the url list from Google Analytics most frequently accessed urls
*
*/
class GaCommand extends BaseCommand
{
    
    /*
    * Sets all arguments and options for the GaCommand
    */
    protected function configure()
    {             
        $this
            ->setName('warm:ga')
            ->setDescription('warm the site based on the Google Analytics')
            ->addArgument(
                'file_path',
                InputArgument::REQUIRED,
                'Path to credentials file'
            )
            ->addArgument(
                'ga_account',
                InputArgument::REQUIRED,
                'Google Analytics account ID'
            )
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                'Domain we are warming. Prepended to URLS'
            )
            ->addOption(
               'count',
               null,
               InputOption::VALUE_REQUIRED,
               'How many URLS to fetch from GA (1000)',
               1000
            )
            ->addOption(
               'start_date',
               null,
               InputOption::VALUE_REQUIRED,
               'Start (30daysAgo)',
               '30daysAgo'
            )
            ->addOption(
               'end_date',
               null,
               InputOption::VALUE_REQUIRED,
               'End (yesterday)',
               'yesterday'
            );
            
            // have to add defaults after required fields are already set
            $this->default_configure();
    }

    /*
    *   Reads all of the options and parameters, fetches the urls from GA and sends
    *   the list of urls to the firehose
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $text = "";
        
        $api_creds_file = $this->parse_argument($input, "file_path",    "Credentials File");           
        $ga_account     = $this->parse_argument($input, "ga_account",   "GA Account");
        $domain         = $this->parse_argument($input, "domain",       "Domain");
        
        $start_date = $input->getOption('start_date');
        $text .= "\nstart date = ". $start_date;
        
        $end_date = $input->getOption('end_date');
        $text .= "\nend date = ". $end_date;

        $count = $input->getOption("count");
        $text .= "\ncount = ". $count;

        $threads = $input->getOption("threads");
        $text .= "\nthreads = ". $threads;
        
        $wait = $input->getOption("wait");
        $text .= "\nwait = ". $wait;

        $ga =   new GoogleAnalytics($api_creds_file, $ga_account);
        $urls = $ga->fetch_urls($count, $start_date, $end_date);

        $output->writeln($text);
        
        // Prepend domain to list of urls
        array_walk($urls, function(&$key, $value) use ($domain) { $key = "http://". $domain . $key;});
        
        // write to file
        $this->write_urls_to_file($urls, $input->getOption("intermediate_file"));
        
        $this->firehose->blast($urls, $threads, $wait);
    }

    // Convenience function
    private function parse_argument(InputInterface $input, $arg, $name)
    {
        $value = $input->getArgument($arg);
        
        if ($value)
        {
            echo $name. ": ". $value . PHP_EOL;
        } 
        else 
        {
            throw new \Exception("Missing ". $name);
        }
        
        return $value;
    }

}