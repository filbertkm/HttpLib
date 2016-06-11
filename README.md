HTTPLib
=======

[![Build Status](https://travis-ci.org/filbertkm/HttpLib.svg?branch=master)](https://travis-ci.org/filbertkm/HttpLib)

Small php wrapper library around curl.

# Usage

```
$url = 'http://www.openstreetmap.org/api/0.6/capabilities';

$httpClient = new HttpClient( 'HttpClientBot', 'http-client-test' );
$response = $httpClient->get( $url );
```

Returns response as a string, if successful, or false.

Also supports post and multipart requests.