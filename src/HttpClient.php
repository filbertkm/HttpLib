<?php

namespace Filbertkm\Http;

use Wikimedia\Assert\Assert;

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
	 */
	public function __construct( $userAgent, $cookiePrefix ) {
		Assert::parameterType( 'string', $userAgent, '$userAgent' );
		Assert::parameterType( 'string', $cookiePrefix, '$cookiePrefix' );

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
	 */
	public function get( $url ) {
		Assert::parameterType( 'string', $url, '$url' );

		if ( !isset( $this->conn ) ) {
			$this->connect();
		}

		$this->setCurlGetOpts( $url );

		return $this->request();
	}

	/**
	 * @param string $url
	 * @param string $postFields
	 */
	public function post( $url, $postFields = null, $headers = array() ) {
		Assert::parameterType( 'string', $url, '$url' );

		if ( !is_string( $postFields ) && !is_array( $postFields ) && !is_null( $postFields ) ) {
			throw new \InvalidArgumentException( '$postFields must be a string, array or null' );
		}

		if ( !isset( $this->conn ) ) {
			$this->connect();
		}

		//$headers[] = 'X-Wikimedia-Debug: 1';

		$this->setCurlPostOpts( $url, $postFields, $headers );

		return $this->request();
	}

	public function multipart( $url, $postFields ) {
		$headers = array(
			'Content-Type' => 'multipart/form-data'
		);

		curl_setopt( $this->conn, CURLOPT_TIMEOUT, 500 );

		return $this->post( $url, $postFields, $headers );
	}

	private function request() {
		$response = curl_exec( $this->conn );

		if ( $response === false ) {
			$response = curl_error( $this->conn );
		}

		curl_reset( $this->conn );

		return $response;
	}

	private function setGeneralCurlOpts() {
		curl_setopt( $this->conn, CURLOPT_COOKIEFILE, $this->getCookieFileName() );
		curl_setopt( $this->conn, CURLOPT_COOKIEJAR, $this->getCookieFileName() );
		curl_setopt( $this->conn, CURLOPT_USERAGENT, $this->userAgent );
		curl_setopt( $this->conn, CURLOPT_SSL_VERIFYPEER, false );
	}

	private function setCurlGetOpts( $url ) {
		$this->setGeneralCurlOpts();

		curl_setopt( $this->conn, CURLOPT_URL, $url );
		curl_setopt( $this->conn, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->conn, CURLOPT_HEADER, 0 );
		curl_setopt( $this->conn, CURLOPT_HTTPHEADER, array( null ) );
		curl_setopt( $this->conn, CURLOPT_POST, false );
		curl_setopt( $this->conn, CURLOPT_POSTFIELDS, null );
	}

	private function setCurlPostOpts( $url, $postFields, $headers = array() ) {
		$this->setGeneralCurlOpts();

		curl_setopt( $this->conn, CURLOPT_URL, $url );
		curl_setopt( $this->conn, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->conn, CURLOPT_HEADER, 0 );
		curl_setopt( $this->conn, CURLOPT_POST, true );
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
