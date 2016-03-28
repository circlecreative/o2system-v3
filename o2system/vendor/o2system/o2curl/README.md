O2CURL
=====
[![Latest Stable Version](https://poser.pugx.org/o2system/o2curl/v/stable)](https://packagist.org/packages/o2system/o2curl) [![Total Downloads](https://poser.pugx.org/o2system/o2curl/downloads)](https://packagist.org/packages/o2system/o2curl) [![Latest Unstable Version](https://poser.pugx.org/o2system/o2curl/v/unstable)](https://packagist.org/packages/o2system/o2curl) [![License](https://poser.pugx.org/o2system/o2curl/license)](https://packagist.org/packages/o2system/o2curl)

[O2CURL][3] is an Open Source PHP Lightweight HTTP Request Client Libraries. 
[O2CURL][3] is build for working more powerfull with O2System Framework, but also can be used for integrated with others as standalone version with limited features.
[O2CURL][3] is insipired by [Unirest][10], so [O2CURL][2] is has also functionality similar with it, but a little bit different at the syntax.

Another amazing product from [][1], released under MIT License.

Features
--------
- Utility methods to call GET, HEAD, POST, PUT, DELETE, CONNECT, OPTIONS, TRACE, PATCH requests
- Supports form parameters, file uploads and custom body entities
- Supports gzip
- Supports Basic, Digest, Negotiate, NTLM Authentication natively
- Customizable timeout
- Customizable default headers for every request (DRY)
- Automatic JSON parsing into a native object for JSON responses

Installation
------------
The best way to install O2CURL is to use [Composer][9]
```
composer require o2system/o2curl:"dev-master"
```

Usage
-----
```php
use O2System\CURL;

$curl = new CURL;

/*
 * Post Request
 *
 * @param string $url      Request URL
 * @param string $path     Request URI Path Segment
 * @param array  $params   Request Parameters
 * @param array  $headers  Request Headers
 *
 * @return \O2System\CURL\Factory\Request
 */
$response = $curl->post(
    "http://domain.com/", // URL
    'request/json',  // Path URI Segment
    // Parameters
    array(
        "foo" => "hello", 
        "bar" => "world"
    ), 
    // Headers
    array(
        "Accept" => "application/json"
    )
);

$response->meta;        // HTTP Request Metadata
$response->header;      // Parsed header
$response->body;        // Parsed body
$response->raw_body;    // Unparsed body
```

More details at the [Wiki](http://github.com/circlecreative/o2curl/wiki).

Ideas and Suggestions
---------------------
Please kindly mail us at [developer@o2system.in][7].

Bugs and Issues
---------------
Please kindly submit your [issues at Github][5] so we can track all the issues along development.

System Requirements
-------------------
- PHP 5.4+
- [Composer][9]

Credits
-------
* Founder and Lead Projects: [Steeven Andrian Salim (steevenz.com)][7]
* Github Pages Designer and Writer: [Teguh Rianto](http://teguhrianto.tk)
* Wiki Writer: [Steeven Andrian Salim](http://steevenz.com) (EN), Aradea Hind (ID)
* Special Thanks To: Yudi Primaputra (CTO - PT. YukBisnis Indonesia)

[1]: http://circle-creative.com
[2]: http://o2system.in
[3]: http://o2system.in/features/standalone/o2curl
[4]: http://o2system.in/features/standalone/o2curl/license
[5]: http://github.com/circlecreative/o2curl/issues
[6]: https://packagist.org/packages/o2system/o2curl
[7]: http://steevenz.com
[8]: mailto:developer@o2system.in
[9]: https://getcomposer.org
[10]: http://unirest.io
