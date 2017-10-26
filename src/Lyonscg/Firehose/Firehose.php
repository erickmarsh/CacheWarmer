<?php
	
namespace Lyonscg\Firehose;

use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;
/*
*	The firehose is responsible for sending out requests
*	in batches of a predetermined size at a predentermed rate
*
*/
class Firehose
{
	private $client;
	
	public function __construct()
	{
		$this->client = new Client();
	}
	
	public function blast($urls = [], $threads = 8, $wait= 2000) 
	{
		echo "Blasting!!!";
		
		// TODO: Add total run time
		$start_time 	= microtime(true);
		
		$stack = HandlerStack::create();
		
		$stack->push(
			Middleware::log(
				new Logger('Logger'),
				new MessageFormatter('{code} - {uri}')
			)
		);
		
		$client = new \GuzzleHttp\Client(
			[
				'handler' => $stack,
			]
		);
		
		$requests = function ($req_array) {
			foreach ($req_array as $r) {
				yield new Request('GET', $r);
			}
		};


		$pool = new Pool($client, $requests($urls), [
			'concurrency' => 5,
			'fulfilled' => function ($response, $index) {
				// this is delivered each successful response
			},
			'rejected' => function ($reason, $index) {
				// this is delivered each failed request
			},
		]);
		
		// Initiate the transfers and create a promise
		$promise = $pool->promise();
		
		// Force the pool of requests to complete.
		$promise->wait();
	}



	/*
	*	Loops through the list of provided urls and make calls
	*	to those urls at the given rate with the provided number of
	* 	threads
	*
	*	$urls: array of urls
	*	$threds: number of requests to make simultaniously
	*	$wait: minimum wait time between bursts in ms
	*/
	public function blast_old($urls = [], $threads = 8, $wait= 2000)
	{
		echo "Blasting!!!";
		
		// TODO: Add total run time
		$start_time 	= microtime(true);
		
		$offset = 0;
		$url_count = count($urls);

		while ($offset < $url_count)
		{
			$url_slice 	= array_slice($urls, $offset, $threads);
			echo "Progress: ". round(($offset/$url_count)*100) ."% (". $offset ."/". $url_count . ")\n";
			$offset 	+= $threads;
			$requests = [];

			$this->make_batch_request($url_slice, $wait);
		}
		
		$end_time 		= microtime(true);
		$time_elapsed 	= ($end_time - $start_time);
		
		echo PHP_EOL . "------------------------------------". PHP_EOL. PHP_EOL;
		echo  "Warmer total run time: ". intval($time_elapsed) ." seconds" . PHP_EOL; 
		echo PHP_EOL . "------------------------------------". PHP_EOL;
		
	}
	
	/*
	*	Makes a single batch request
	*
	*	$urls: Group of urls that are going to be requested in this batch
	*	$wait: Amount of time to wait if the requests finish before $wait ms
	*
	*/
	private function make_batch_request($urls, $wait)
	{
		$requests = [];

		// create the actual request objects
		foreach ($urls as $u)
		{
			$requests[] = $this->client->createRequest('GET', trim($u), 
														[ 
														'headers' => ['Accept-Encoding' => 'gzip, deflate']
													  	]);
		}

		$start_time 	= microtime(true);
		$results 		= Pool::batch($this->client, $requests);
		$end_time 		= microtime(true);

		foreach ($results->getSuccessful() as $response) {
    		echo $response->getStatusCode() ." - ";
			echo $response->getEffectiveUrl(). "\n";
		}
		
		foreach ($results->getFailures() as $requestException) {
			echo "FAIL - "; 
			echo $requestException->getRequest()->getUrl(). "\n" ;
		}

		$time_elapsed 	= ($end_time - $start_time);
		$wait_time 		= $wait - $time_elapsed;

		echo "\nTime Elapsed: ". $time_elapsed;
		echo "\nWaiting:". $wait_time; 
		
		sleep(($wait_time < 0) ? 0 : $wait_time);
		echo "\n";

		return $time_elapsed;
	}
			
}	