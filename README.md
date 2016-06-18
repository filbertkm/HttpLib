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

# License

* [Apache-2.0](http://www.apache.org/licenses/LICENSE-2.0)

# Release notes

## 0.2

* Added download method.
