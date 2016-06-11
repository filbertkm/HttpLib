<?php

namespace Filbertkm\Http;

class HttpClient {

	/**
	 * @var string
	 */
	private $userAgent;

	/**
	 * @var string
	 */
	private $cookiePrefix;

	/**
	 * @var resource
	 */
	private $conn;

	/**
	 * @var string
	 */
	private $cookieFileName;

	/**
	 * @param string $userAgent
	 * @param string $cookiePrefix
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $userAgent, $cookiePrefix ) {
		$this->assertString( 'userAgent', $userAgent );
		$this->assertString( 'cookiePrefix', $cookiePrefix );

		$this->userAgent = $userAgent;
		$this->cookiePrefix = $cookiePrefix;
	}

	public function connect() {
		$this->conn = curl_init();
	}

	public function disconnect() {
		curl_close( $this->conn );
		unset( $this->conn );

		$this->destroyCookie();
	}

	/**
	 * @param string $url
	 *
	 * @throws InvalidArgumentException
	 * @return string|false
	 */
	public function get( $url ) {
		$this->assertString( 'url', $url );

		if ( !isset( $this->conn ) ) {
			$this->connect();
		}

		$this->setCurlGetOpts( $url );

		return $this->request();
	}

	/**
	 * @param string $url
	 * @param string|array|null $postFields
	 * @param array $headers
	 *
	 * @throws InvalidArgumentException
	 * @return string|false
	 */
	public function post( $url, $postFields = null, $headers = array() ) {
		$this->assertString( 'url', $url );

		if ( !is_string( $postFields ) && !is_array( $postFields ) && !is_null( $postFields ) ) {
			throw new \InvalidArgumentException( '$postFields must be a string, array or null' );
		}

		if ( !isset( $this->conn ) ) {
			$this->connect();
		}

		$this->setCurlPostOpts( $url, $postFields, $headers );

		return $this->request();
	}

	/**
	 * @param string $url
	 * @param string $destination
	 */
	public function download( $url, $destination ) {
		$this->assertString( 'url', $url );
		$this->assertString( 'destination', $destination );

		if ( !isset( $this->conn ) ) {
			$this->connect();
		}

		$this->setGeneralCurlOpts();
		$this->setCurlGetOpts( $url );
		$this->setTimeout( 300 );

		// CURLOPT_FILE needs to be set after CURLOPT_RETURNTRANSFER
		curl_setopt( $this->conn, CURLOPT_FILE, fopen( $destination, 'w' ) );

		$this->request();
	}

	/**
	 * @param string $url
	 * @param string|array $postFields
	 *
	 * @throws InvalidArgumentException
	 * @return string|false
	 */
	public function multipart( $url, $postFields ) {
		$this->assertString( 'url', $url );

		if ( !isset( $this->conn ) ) {
			$this->connect();
		}

		$headers = array(
			'Content-Type' => 'multipart/form-data'
		);


		curl_setopt( $this->conn, CURLOPT_TIMEOUT, 500 );

		return $this->post( $url, $postFields, $headers );
	}

	/**
	 * @param int $timeout
	 */
	public function setTimeout( $timeout ) {
		if ( !isset( $this->conn ) ) {
			$this->connect();
		}

		curl_setopt( $this->conn, CURLOPT_TIMEOUT, $timeout );
	}

	private function request() {
		$response = curl_exec( $this->conn );

		if ( $response === false ) {
			$response = curl_error( $this->conn );
		}

		curl_reset( $this->conn );

		return $response;
	}

	/**
	 * @param string $param
	 * @param mixed $value
	 *
	 * @throws \InvalidArgumentException
	 */
	private function assertString( $param, $value ) {
		if ( !is_string( $value ) ) {
			throw new \InvalidArgumentException( '$' . $param . ' must be a string' );
		}
	}

	private function setGeneralCurlOpts() {
        curl_setopt( $this->conn, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->conn, CURLOPT_HEADER, 0 );
		curl_setopt( $this->conn, CURLOPT_COOKIEFILE, $this->getCookieFileName() );
		curl_setopt( $this->conn, CURLOPT_COOKIEJAR, $this->getCookieFileName() );
		curl_setopt( $this->conn, CURLOPT_USERAGENT, $this->userAgent );
		curl_setopt( $this->conn, CURLOPT_SSL_VERIFYPEER, false );
	}

	private function setCurlGetOpts( $url ) {
		$this->setGeneralCurlOpts();

		curl_setopt( $this->conn, CURLOPT_URL, $url );
		curl_setopt( $this->conn, CURLOPT_HTTPHEADER, array( null ) );
		curl_setopt( $this->conn, CURLOPT_CUSTOMREQUEST, 'GET' );
		curl_setopt( $this->conn, CURLOPT_POST, false );
		curl_setopt( $this->conn, CURLOPT_POSTFIELDS, null );
	}

	private function setCurlPostOpts( $url, $postFields, $headers = array() ) {
		$this->setGeneralCurlOpts();

		curl_setopt( $this->conn, CURLOPT_URL, $url );
		curl_setopt( $this->conn, CURLOPT_POST, true );
		curl_setopt( $this->conn, CURLOPT_CUSTOMREQUEST, 'POST' );
		curl_setopt( $this->conn, CURLOPT_HTTPHEADER, array_merge( array( 'Expect:' ), $headers ) );
		curl_setopt( $this->conn, CURLOPT_POSTFIELDS, $postFields );
	}

	private function getCookieFileName() {
		if ( !isset( $this->cookieFileName ) ) {
			$this->cookieFileName = tempnam( sys_get_temp_dir(), $this->cookiePrefix );
		}

		return $this->cookieFileName;
	}

	private function destroyCookie() {
		if ( isset( $this->cookieFileName ) ) {
			unlink( $this->cookieFileName );
		}
	}

}
