<?php
	
namespace Lyonscg\Ga;

use Google;

class GoogleAnalytics
{
	private $api_creds_file = "";
	private $metrics = "ga:pageviews";
	private $ga_id;
	private $start_date;
	private $end_date;
	private $pem_file; 	// needed to authenticate to Google. located in root
	
	
	public function __construct($api_creds_file, $id )
	{
		$this->api_creds_file 	= $api_creds_file;
		$this->ga_id 		= $id;
		//$this->pem_file 		= __DIR__ . '/../../../cacerts.pem';
		$this->pem_file			= getcwd() ."/cacerts.pem";
		$this->client		= $this->create_client();
	}
	
	
	public function fetch_urls($max_results = 1000, $start_date = '30daysAgo', $end_date = 'yesterday')
	{
		$optParams = array(
    		'dimensions' => 'ga:pagepath',
    		'sort' => '-ga:pageviews', 
    		'max-results' => $max_results
		);
		
		$service = new \Google_Service_Analytics($this->client);
	// for debugging
	// $logger  = new \Google_Logger_File($this->client);
		$results = $service->data_ga->get($this->ga_id, 
										  $start_date, 
										  $end_date, 
										  $this->metrics, 
										  $optParams);

		// get the first column in the result row
		$url_list = array_map(function($r){ return $r[0]; }, $results->rows);

		return $url_list;
	}
	
	
	private function create_client()
	{
		$full_creds_file = $this->api_creds_file;
		if (!file_exists($full_creds_file))
		{
			throw new \Exception("Cannot find file ". $full_creds_file , 1);
		}
	
		$client = new \Google_Client();
		echo "Credentials File: ". $full_creds_file .PHP_EOL;
		
		$cred = $client->loadServiceAccountJson($full_creds_file, array(\Google_Service_Analytics::ANALYTICS_READONLY));
		
		// Setup authentication to Google.
		// This is needed because we are running via a phar file
		$client->setClassConfig("Google_IO_Curl", ["options" => [CURLOPT_CAINFO => $this->pem_file ]]);
		
		// re-auth if needed
		if ($client->getAuth()->isAccessTokenExpired()) {
			$client->getAuth()->refreshTokenWithAssertion($cred);
		}
		
		return $client;
	}	
}