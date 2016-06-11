<?php

namespace Filbertkm\Http\Tests;

use Filbertkm\Http\HttpClient;

/**
 * @covers Filbertkm\Http\HttpClient
 *
 * @licence GNU GPL v2+
 * @author Katie Filbert < aude.wiki@gmail.com >
 */
class HttpClientTest extends \PHPUnit_Framework_TestCase {

	public function testGet() {
		$url = 'http://www.openstreetmap.org/api/0.6/capabilities';

		$httpClient = new HttpClient( 'HttpClientBot', 'http-client-test' );
		$response = $httpClient->get( $url );

		$osmXml = new \SimpleXMLElement( $response );
		$this->assertEquals( '0.6', $osmXml['version'] );
	}

	public function testGetHttps() {
		$url = 'https://en.wikipedia.org/w/api.php?action=query&meta=siteinfo&format=json';

		$httpClient = new HttpClient( 'HttpClientBot', 'http-client-test' );
		$response = $httpClient->get( $url );

		$data = json_decode( $response, true );
		$this->assertEquals( 'Wikipedia', $data['query']['general']['sitename'] );
	}

}
