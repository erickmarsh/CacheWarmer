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
}