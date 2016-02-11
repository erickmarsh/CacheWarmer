<?php

namespace Lyonscg\Parser;

use GuzzleHttp\Client as Client;

class SitemapParser
{
	private $sitemap_url;
	private $urls = [];
	private $client;	// Guzzle client

	public function __construct($sitemap_url)
	{
		$this->sitemap_url = $sitemap_url;
		
		// Dependencies
		$this->client = new Client();
	}

	public function get_urls()
	{
		if (count($this->urls) > 0)
		{
			return $this->urls;
		}

		$this->urls = $this->get_urls_from_xml($this->sitemap_url);

		return $this->urls;
	}


	private function get_urls_from_xml($xml_url)
	{
		$xml = $this->download_xml($xml_url);

		$url_list = [];

		foreach ($xml->url as $u) {
		    $url_list[] = $u->loc->__toString();
		}

		return $url_list;
	}

	private function download_xml($xml_url)
	{
		echo $xml_url;
		$response = $this->client->get($xml_url);

		var_dump($response->getBody());
		
		if ($response->getStatusCode() != 200)
		{
			throw new \Execption("Error downloading: ". $response->getStatusCode() . " - ". $xml_url);
		}

		$xml = simplexml_load_string($response->getBody());

		return $xml;
	}
}
