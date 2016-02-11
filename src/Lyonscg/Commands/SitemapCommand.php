<?php
	
namespace Lyonscg\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Lyonscg\Parser\SitemapParser;

class SitemapCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('warm:sitemap')
            ->setDescription('warm the site based on the sitemap.xml')
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'URL of the sitemap.xml'
            );
            
            // have to add defaults after required fields are already set
            $this->default_configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');
       
        if ($url) {
            $text = 'Sitemap URL: '.$url;
            $parser = new SitemapParser($url);
        } else {
            throw new \Exception('Missing URL');
        }

        $urls = $parser->get_urls();
        
        // write to file
        $this->write_urls_to_file($urls, $input->getOption("intermediate_file"));
        
        $text .= PHP_EOL ."Threads: ". $input->getOption("threads");
        $text .= PHP_EOL ."Wait: ". $input->getOption("wait") ." Seconds";
        $output->writeln($text);
        
        $this->firehose->blast($urls, $input->getOption("threads"), $input->getOption("wait"));
    }
}