<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 03-Aug-16
 * Time: 7:10 PM
 */

namespace O2System\Core\HTTP\Header\Fields;

/**
 * HTTP Header Request Fields
 *
 * HTTP header fields are components of the header section of
 * request and response messages in the Hypertext Transfer Protocol (HTTP).
 * They define the operating parameters of an HTTP transaction.
 *
 * @see     https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
 *
 * @package O2System\Core\HTTP\Header\Fields
 * @since   4.0.0
 */
interface Request
{
	/**
	 * Accept
	 *
	 * Content-Types that are acceptable for the response
	 *
	 * @see     https://en.wikipedia.org/wiki/Content_negotiation
	 *
	 * @example Accept: text/plain
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_ACCEPT = 'Accept';

	/**
	 * Accept-Charset
	 *
	 * Character sets that are acceptable
	 *
	 * @see     https://en.wikipedia.org/wiki/Content_negotiation
	 *
	 * @example Accept-Charset: utf-8
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_ACCEPT_CHARSET = 'Accept-Charset';

	/**
	 * Accept-Charset
	 *
	 * List of acceptable encodings
	 *
	 * @see     https://en.wikipedia.org/wiki/Content_negotiation
	 *
	 * @example Accept-Encoding: gzip, deflate
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/HTTP_compression
	 * @status  Permanent
	 */
	const REQUEST_ACCEPT_ENCODING = 'Accept-Encoding';

	/**
	 * Accept-Language
	 *
	 * List of acceptable human languages for response
	 *
	 * @see     https://en.wikipedia.org/wiki/Content_negotiation
	 *
	 * @example Accept-Language: en-US
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/Content_negotiation
	 * @status  Permanent
	 */
	const REQUEST_ACCEPT_LANGUAGE = 'Accept-Language';

	/**
	 * Accept-Datetime
	 *
	 * Acceptable version in time
	 *
	 * @example Accept-Datetime: Thu, 31 May 2007 20:35:00 GMT
	 * @var string
	 * @status  Provisional
	 */
	const REQUEST_ACCEPT_DATETIME = 'Accept-Datetime';

	/**
	 * Authorization
	 *
	 * Authentication credentials for HTTP authentication
	 *
	 * @example Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_AUTHORIZATION = 'Authorization';

	/**
	 * Cache-Control
	 *
	 * Used to specify directives that must be obeyed by all caching mechanisms along the request-response chain
	 *
	 * @example Cache-Control: no-cache
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_CACHE_CONTROL = 'Cache-Control';

	/**
	 * Connection
	 *
	 * Control options for the current connection and list of hop-by-hop request fields
	 *
	 * @example Connection: keep-alive
	 * @example Connection: Upgrade
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_CONNECTION = 'Connection';

	/**
	 * Cookie
	 *
	 * An HTTP cookie previously sent by the server with Set-Cookie
	 *
	 * @see     https://en.wikipedia.org/wiki/HTTP_cookie
	 * @example Cookie: $Version=1; Skin=new;
	 * @var string
	 * @status  Permanent: Standard
	 */
	const REQUEST_COOKIE = 'Cookie';

	/**
	 * Content-Length
	 *
	 * The length of the request body in octets (8-bit bytes)
	 *
	 *
	 * @example Content-Length: 348
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_CONTENT_LENGTH = 'Content-Length';

	/**
	 * Content-MD5
	 *
	 * A Base64-encoded binary MD5 sum of the content of the request body
	 *
	 * @see     https://en.wikipedia.org/wiki/MD5
	 * @example Content-MD5: Q2hlY2sgSW50ZWdyaXR5IQ==
	 * @var string
	 * @status  Obsolete
	 */
	const REQUEST_CONTENT_MD5 = 'Content-MD5';

	/**
	 * Content-Type
	 *
	 * The MIME type of the body of the request (used with POST and PUT requests)
	 *
	 * @example Content-Type: application/x-www-form-urlencoded
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_CONTENT_TYPE = 'Content-Type';

	/**
	 * Date
	 *
	 * The date and time that the message was originated
	 *
	 * @see     http://tools.ietf.org/html/rfc7231#section-7.1.1.1
	 * @example Date: Tue, 15 Nov 1994 08:12:31 GMT
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_DATE = 'Date';

	/**
	 * Expect
	 *
	 * Indicates that particular server behaviors are required by the client
	 *
	 * @example Expect: 100-continue
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_EXPECT = 'Expect';

	/**
	 * Forwarded
	 *
	 * Disclose original information of a client connecting to a web server through an HTTP proxy
	 *
	 * @example Forwarded: for=192.0.2.60;proto=http;by=203.0.113.43
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_FORWARDED = 'Forwarded';

	/**
	 * From
	 *
	 * The email address of the user making the request
	 *
	 * @example From: user@example.com
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_FROM = 'From';

	/**
	 * Host
	 *
	 * The domain name of the server (for virtual hosting), and the TCP port number on which the server is listening.
	 * The port number may be omitted if the port is the standard port for the service requested. Mandatory since
	 * HTTP/1.1.
	 *
	 * @example Host: en.wikipedia.org:8080
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_HOST = 'Host';

	/**
	 * If-Match
	 *
	 * Only perform the action if the client supplied entity matches the same entity on the server.
	 * This is mainly for methods like PUT to only update a resource if it has not been modified since the user last
	 * updated it.
	 *
	 * @example If-Match: "737060cd8c284d8af7ad3082f209582d"
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_IF_MATCH = 'If-Match';

	/**
	 * If-Modified-Since
	 *
	 * Allows a 304 Not Modified to be returned if content is unchanged
	 *
	 * @example If-Modified-Since: Sat, 29 Oct 1994 19:43:31 GMT
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_IF_MODIFIED_SINCE = 'If-Modified-Since';

	/**
	 * If-None-Match
	 *
	 * Allows a 304 Not Modified to be returned if content is unchanged
	 *
	 * @see     https://en.wikipedia.org/wiki/HTTP_ETag
	 * @example If-None-Match: "737060cd8c284d8af7ad3082f209582d"
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_IF_NONE_MATCH = 'If-None-Match';

	/**
	 * If-Range
	 *
	 * If the entity is unchanged, send me the part(s) that I am missing; otherwise, send me the entire new entity
	 *
	 *
	 * @example If-Range: "737060cd8c284d8af7ad3082f209582d"
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_IF_RANGE = 'If-Range;';

	/**
	 * If-Unmodified-Since
	 *
	 * Only send the response if the entity has not been modified since a specific time
	 *
	 *
	 * @example If-Unmodified-Since: Sat, 29 Oct 1994 19:43:31 GMT
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';

	/**
	 * Max-Forwards
	 *
	 * Limit the number of times the message can be forwarded through proxies or gateways
	 *
	 * @example Max-Forwards: 10
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_MAX_FORWARDS = 'Max-Forwards';

	/**
	 * Origin
	 *
	 * Initiates a request for cross-origin resource sharing (asks server for an 'Access-Control-Allow-Origin' response
	 * field)
	 *
	 * @see     https://en.wikipedia.org/wiki/Cross-origin_resource_sharing
	 * @example Origin: http://www.example-social-network.com
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_ORIGIN = 'Origin';

	/**
	 * Pragma
	 *
	 * Implementation-specific fields that may have various effects anywhere along the request-response chain
	 *
	 * @example Pragma: no-cache
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_PRAGMA = 'Pragma';

	/**
	 * Proxy-Authorization
	 *
	 * Authorization credentials for connecting to a proxy.
	 *
	 * @example Proxy-Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_PROXY_AUTHORIZATION = 'Proxy-Authorization';

	/**
	 * Range
	 *
	 * Request only part of an entity. Bytes are numbered from 0. See Byte serving.
	 *
	 * @see     https://en.wikipedia.org/wiki/Byte_serving
	 * @example Range: bytes=500-999
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_RANGE = 'Range';

	/**
	 * Referer
	 *
	 * This is the address of the previous web page from which a link to the currently requested page was followed.
	 * (The word “referrer” has been misspelled in the RFC as well as in most implementations to the point that it
	 * has become standard usage and is considered correct terminology)
	 *
	 *
	 * @example Referer: http://en.wikipedia.org/wiki/Main_Page
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_REFERER = 'Referer';

	/**
	 * TE
	 *
	 * The transfer encodings the user agent is willing to accept: the same values as for the response header field
	 * Transfer-Encoding can be used, plus the "trailers" value (related to the "chunked" transfer method) to notify
	 * the server it expects to receive additional fields in the trailer after the last, zero-sized, chunk.
	 *
	 *
	 * @example TE: trailers, deflate
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_TE = 'TE';

	/**
	 * User-Agent
	 *
	 * The user agent string of the user agent
	 *
	 * @see     https://en.wikipedia.org/wiki/User_agent_string
	 * @example User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:12.0) Gecko/20100101 Firefox/21.0
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_USER_AGENT = 'User-Agent';

	/**
	 * Upgrade
	 *
	 * Ask the server to upgrade to another protocol.
	 *
	 * @example Upgrade: HTTP/2.0, HTTPS/1.3, IRC/6.9, RTA/x11, websocket
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_UPGRADE = 'Upgrade';

	/**
	 * Via
	 *
	 * Informs the server of proxies through which the request was sent.
	 *
	 * @example Via: 1.0 fred, 1.1 example.com (Apache/1.1)
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_VIA = 'Via';

	/**
	 * Warning
	 *
	 * A general warning about possible problems with the entity body.
	 *
	 * @example Warning: 199 Miscellaneous warning
	 * @var string
	 * @status  Permanent
	 */
	const REQUEST_WARNING = 'Warning';


	// ------------------------------------------------------------------------

	/**
	 * ------------------------------------------------------------------------
	 * Common Non-Standard Request Fields
	 * ------------------------------------------------------------------------
	 */

	/**
	 * X-Requested-With
	 *
	 * Mainly used to identify Ajax requests.
	 * Most JavaScript frameworks send this field with value of XMLHttpRequest
	 *
	 * @example X-Requested-With: XMLHttpRequest
	 * @status  Custom
	 */
	const REQUEST_X_REQUEST_WITH = 'X-Requested-With';

	/**
	 * DNT
	 *
	 * Requests a web application to disable their tracking of a user.
	 * This is Mozilla's version of the X-Do-Not-Track header field (since Firefox 4.0 Beta 11).
	 * Safari and IE9 also have support for this field
	 *
	 * @example DNT: 1 (Do Not Track Enabled)
	 * @example DNT: 0 (Do Not Track Disabled)
	 * @status  Custom
	 */
	const REQUEST_DNT = 'DNT';

	/**
	 * X-Forwarded-For
	 *
	 * a de facto standard for identifying the originating IP address
	 * of a client connecting to a web server through an HTTP proxy or load balancer
	 *
	 * @example X-Forwarded-For: client1, proxy1, proxy2
	 * @status  Custom
	 */
	const REQUEST_X_FORWARDED_FOR = 'X-Forwarded-For';

	/**
	 * X-Forwarded-Host
	 *
	 * a de facto standard for identifying the original host requested by the client in the Host HTTP request header,
	 * since the host name and/or port of the reverse proxy (load balancer) may differ from the origin server handling
	 * the request.
	 *
	 * @example X-Forwarded-Host: en.wikipedia.org:8080
	 * @status  Custom
	 */
	const REQUEST_X_FORWARDED_HOST = 'X-Forwarded-Host';

	/**
	 * X-Forwarded-Proto
	 *
	 * a de facto standard for identifying the originating protocol of an HTTP request,
	 * since a reverse proxy (or a load balancer) may communicate with a web server
	 * using HTTP even if the request to the reverse proxy is HTTPS.
	 * An alternative form of the header (X-ProxyUser-Ip) is used by Google clients talking to Google servers..
	 *
	 * @example X-Forwarded-Proto: https
	 * @status  Custom
	 */
	const REQUEST_X_FORWARDED_PROTO = 'X-Forwarded-Proto';

	/**
	 * Front-End-Https
	 *
	 * Non-standard header field used by Microsoft applications and load-balancers
	 *
	 * @example Front-End-Https: on
	 * @status  Custom
	 */
	const REQUEST_FRONT_END_HTTPS = 'Front-End-Https';

	/**
	 * X-Http-Method-Override
	 *
	 * Requests a web application override the method specified in the request (typically POST) with
	 * the method given in the header field (typically PUT or DELETE). Can be used when a user agent
	 * or firewall prevents PUT or DELETE methods from being sent directly (note that this either a bug in the software
	 * component, which ought to be fixed, or an intentional configuration, in which case bypassing it may be the wrong
	 * thing to do).
	 *
	 * @example X-HTTP-Method-Override: DELETE
	 * @status  Custom
	 */
	const REQUEST_X_HTTP_METHOD_OVERRIDE = 'X-Http-Method-Override';

	/**
	 * X-ATT-DeviceId
	 *
	 * Allows easier parsing of the MakeModel/Firmware that is usually found in the User-Agent String of AT&T Devices
	 *
	 * @example X-Att-Deviceid: GT-P7320/P7320XXLPG
	 * @status  Custom
	 */
	const REQUEST_X_ATT_DEVICEID = 'X-ATT-DeviceId';

	/**
	 * X-Wap-Profile
	 *
	 * Links to an XML file on the Internet with a full description and details about the device currently connecting.
	 * In the example to the right is an XML file for an AT&T Samsung Galaxy S2.
	 *
	 * @example x-wap-profile: http://wap.samsungmobile.com/uaprof/SGH-I777.xml
	 * @status  Custom
	 */
	const REQUEST_X_WAP_PROFILE = 'X-Wap-Profile';

	/**
	 * Proxy-Connection
	 *
	 * Implemented as a misunderstanding of the HTTP specifications. Common because of mistakes in implementations of
	 * early HTTP versions. Has exactly the same functionality as standard Connection field.
	 *
	 * @example Proxy-Connection: keep-alive
	 * @status  Custom
	 */
	const REQUEST_PROXY_CONNECTION = 'Proxy-Connection';

	/**
	 * X-UIDH
	 *
	 * Server-side deep packet insertion of a unique ID identifying customers of Verizon Wireless;
	 * also known as "perma-cookie" or "supercookie"
	 *
	 * @example X-UIDH: ...
	 * @status  Custom
	 */
	const REQUEST_X_UIDH = 'X-UIDH';

	/**
	 * X-Csrf-Token
	 *
	 * Used to prevent cross-site request forgery. Alternative header names are: X-CSRFToken[28] and X-XSRF-TOKEN
	 *
	 * @example X-Csrf-Token: i8XNjC4b8KVok4uw5RftR38Wgp2BFwql
	 * @status  Custom
	 */
	const REQUEST_X_CSRF_TOKEN = 'X-Csrf-Token';
}