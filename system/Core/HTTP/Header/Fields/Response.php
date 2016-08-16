<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 03-Aug-16
 * Time: 7:31 PM
 */

namespace O2System\Core\HTTP\Header\Fields;

/**
 * HTTP Header Response Fields
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
interface Response
{
	/**
	 * Access-Control-Allow-Origin
	 *
	 * Specifying which web sites can participate in cross-origin resource sharing
	 *
	 * @see     https://en.wikipedia.org/wiki/Cross-origin_resource_sharing
	 * @var string
	 * @example Access-Control-Allow-Origin: *
	 * @status  Provisional
	 */
	const RESPONSE_ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';

	/**
	 * Access-Control-Allow-Credentials
	 *
	 * Specifying indicator whether the response to request can be exposed when the omit credentials flag is unset.
	 * When part of the response to a preflight request it indicates that the actual request can include user credential.
	 *
	 * @see    https://www.w3.org/TR/cors/#access-control-allow-credentials-response-header
	 * @var string
	 * @example Access-Control-Allow-Credentials: true
	 * @status  Provisional
	 */
	const RESPONSE_ACCESS_CONTROL_ALLOW_CREDENTIALS = 'Access-Control-Allow-Credentials';

	/**
	 * Access-Control-Expose-Headers
	 *
	 * Specifying indicator which headers are safe to expose to the API of a CORS API specification.
	 *
	 * @see    https://www.w3.org/TR/cors/#access-control-expose-headers-response-header
	 * @var string
	 * @example Access-Control-Expose-Headers: field-name, ...
	 * @status  Provisional
	 */
	const RESPONSE_ACCESS_CONTROL_EXPOSE_HEADERS = 'Access-Control-Expose-Headers';

	/**
	 * Access-Control-Max-Age
	 *
	 * Specifying indicator how long the results of a preflight request can be cached in a preflight result cache.
	 *
	 * @see    https://www.w3.org/TR/cors/#access-control-max-age
	 * @var string
	 * @example Access-Control-Max-Age: (int) delta-seconds
	 * @status  Provisional
	 */
	const RESPONSE_ACCESS_CONTROL_MAX_AGE = 'Access-Control-Max-Age';

	/**
	 * Access-Control-Allow-Methods
	 *
	 * Specifying indicator which methods can be used during the actual request,
	 * as part of the response to a preflight request.
	 *
	 * @see    https://www.w3.org/TR/cors/#access-control-allow-methods
	 * @var string
	 * @example Access-Control-Allow-Methods: GET, POST, PUT, ...
	 * @status  Provisional
	 */
	const RESPONSE_ACCESS_CONTROL_ALLOW_METHODS = 'Access-Control-Allow-Methods';

	/**
	 * Access-Control-Allow-Headers
	 *
	 * Specifying indicator which header field names can be used during the actual request,
	 * as part of the response to a preflight request.
	 *
	 * @see    https://www.w3.org/TR/cors/#access-control-allow-headers
	 * @var string
	 * @example Access-Control-Allow-Headers: Authorization, ...
	 * @status  Provisional
	 */
	const RESPONSE_ACCESS_CONTROL_ALLOW_HEADERS = 'Access-Control-Allow-Headers';

	/**
	 * Access-Control-Allow-Content-Types
	 *
	 * Specifying Content Types result.
	 *
	 * @var string
	 * @example Access-Control-Allow-Headers: application/json, ...
	 * @status  Provisional
	 */
	const RESPONSE_ACCESS_CONTROL_ALLOW_CONTENT_TYPES = 'Access-Control-Allow-Content-Types';

	/**
	 * Accept-Patch
	 *
	 * Specifies which patch document formats this server supports
	 *
	 * @var string
	 * @example Accept-Patch: text/example;charset=utf-8
	 * @status  Permanent
	 */
	const RESPONSE_ACCEPT_PATCH = 'Accept-Patch';

	/**
	 * Accept-Ranges
	 *
	 * What partial content range types this server supports via byte serving
	 *
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/Byte_serving
	 * @example Accept-Ranges: bytes
	 * @status  Permanent
	 */
	const RESPONSE_ACCEPT_RANGES = 'Accept-Ranges';

	/**
	 * Age
	 *
	 * The age the object has been in a proxy cache in seconds
	 *
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/Proxy_cache
	 * @example Accept-Ranges: bytes
	 * @status  Permanent
	 */
	const RESPONSE_AGE = 'Age';

	/**
	 * Allow
	 *
	 * Valid actions for a specified resource. To be used for a 405 Method not allowed
	 *
	 * @var string
	 * @example Allow: GET, HEAD
	 * @status  Permanent
	 */
	const RESPONSE_ALLOW = 'Allow';

	/**
	 * Alt-Svc
	 *
	 * A server uses "Alt-Svc" header (meaning Alternative Services) to indicate that its resources can also be
	 * accessed at a different network location (host or port) or using a different protocol
	 *
	 * @var string
	 * @example Alt-Svc: h2="http2.example.com:443"; ma=7200
	 * @status  Permanent
	 */
	const RESPONSE_ALT_SVC = 'Alt-Svc';

	/**
	 * Cache-Control
	 *
	 * Tells all caching mechanisms from server to client whether they may cache this object. It is measured in seconds
	 *
	 * @var string
	 * @example Cache-Control: max-age=3600
	 * @status  Permanent
	 */
	const RESPONSE_CACHE_CONTROL = 'Cache-Control';

	/**
	 * Connection
	 *
	 * Control options for the current connection and list of hop-by-hop response fields
	 *
	 * @var string
	 * @example Connection: close
	 * @status  Permanent
	 */
	const RESPONSE_CONNECTION = 'Connection';

	/**
	 * Content-Disposition
	 *
	 * An opportunity to raise a "File Download" dialogue box for a known MIME type with binary format or suggest a
	 * filename for dynamic content. Quotes are necessary with special characters.
	 *
	 * @var string
	 * @example Content-Disposition: attachment; filename="fname.ext"
	 * @status  Permanent
	 */
	const RESPONSE_CONTENT_DISPOSITION = 'Content-Disposition';

	/**
	 * Content-Encoding
	 *
	 * The type of encoding used on the data. See HTTP compression.
	 *
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/HTTP_compression
	 * @example Content-Encoding: gzip
	 * @status  Permanent
	 */
	const RESPONSE_CONTENT_ENCODING = 'Content-Encoding';

	/**
	 * Content-Transfer-Encoding
	 *
	 * It indicates whether or not a binary-to-text encoding scheme has been used on top
	 * of the original encoding as specified within the Content-Type header.
	 *
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/MIME#Content-Transfer-Encoding
	 * @example Content-Transfer-Encoding: binary
	 * @status  Provision
	 */
	const RESPONSE_TRANSFER_ENCODING = 'Content-Transfer-Encoding';

	/**
	 * Content-Language
	 *
	 * The natural language or languages of the intended audience for the enclosed content
	 *
	 *
	 * @var string
	 * @example Content-Language: da
	 * @status  Permanent
	 */
	const RESPONSE_CONTENT_LANGUAGE = 'Content-Language';

	/**
	 * Content-Length
	 *
	 * The length of the response body in octets (8-bit bytes)
	 *
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/Octet_(computing)
	 * @example Content-Length: 348
	 * @status  Permanent
	 */
	const RESPONSE_CONTENT_LENGTH = 'Content-Length';

	/**
	 * Content-Location
	 *
	 * An alternate location for the returned data
	 *
	 * @var string
	 * @example Content-Location: /index.htm
	 * @status  Permanent
	 */
	const RESPONSE_CONTENT_LOCATION = 'Content-Location';

	/**
	 * Content-MD5
	 *
	 * A Base64-encoded binary MD5 sum of the content of the response
	 *
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/MD5
	 * @example Content-MD5: Q2hlY2sgSW50ZWdyaXR5IQ==
	 * @status  Obsolete
	 */
	const RESPONSE_CONTENT_MD5 = 'Content-MD5';

	/**
	 * Content-Range
	 *
	 * Where in a full body message this partial message belongs
	 *
	 * @var string
	 * @example Content-Range: bytes 21010-47021/47022
	 * @status  Permanent
	 */
	const RESPONSE_CONTENT_RANGE = 'Content-Range';

	/**
	 * Content-Type
	 *
	 * The MIME type of this content
	 *
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/MIME_type
	 * @example Content-Type: text/html; charset=utf-8
	 * @status  Permanent
	 */
	const RESPONSE_CONTENT_TYPE = 'Content-Type';

	/**
	 * Date
	 *
	 * The date and time that the message was sent (in "HTTP-date" format as defined by RFC 7231)
	 *
	 * @var string
	 * @see     https://tools.ietf.org/html/rfc7231
	 * @example Date: Tue, 15 Nov 1994 08:12:31 GMT
	 * @status  Permanent
	 */
	const RESPONSE_DATE = 'Date';

	/**
	 * ETag
	 *
	 * An identifier for a specific version of a resource, often a message digest
	 *
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/Message_digest
	 * @example ETag: "737060cd8c284d8af7ad3082f209582d"
	 * @status  Permanent
	 */
	const RESPONSE_ETAG = 'ETag';

	/**
	 * Expires
	 *
	 * Gives the date/time after which the response is considered stale (in "HTTP-date" format as defined by RFC 7231)
	 *
	 * @var string
	 * @see     https://tools.ietf.org/html/rfc7231
	 * @example Expires: Thu, 01 Dec 1994 16:00:00 GMT
	 * @status  Permanent: standard
	 */
	const RESPONSE_EXPIRES = 'Expires';

	/**
	 * Last-Modified
	 *
	 * The last modified date for the requested object (in "HTTP-date" format as defined by RFC 7231)
	 *
	 * @var string
	 * @see     https://tools.ietf.org/html/rfc7231
	 * @example Last-Modified: Tue, 15 Nov 1994 12:45:26 GMT
	 * @status  Permanent
	 */
	const RESPONSE_LAST_MODIFIED = 'Last-Modified';

	/**
	 * Link
	 *
	 * Used to express a typed relationship with another resource, where the relation type is defined by RFC 5988
	 *
	 * @var string
	 * @see     https://tools.ietf.org/html/rfc5988
	 * @example Link: </feed>; rel="alternate"
	 * @status  Permanent
	 */
	const RESPONSE_LINK = 'Link';

	/**
	 * Location
	 *
	 * Used in redirection, or when a new resource has been created.
	 *
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/URL_redirection
	 * @example Location: http://www.w3.org/pub/WWW/People.html
	 * @status  Permanent
	 */
	const RESPONSE_LOCATION = 'Location';

	/**
	 * P3P
	 *
	 * This field is supposed to set P3P policy, in the form of P3P:CP="your_compact_policy".
	 * However, P3P did not take off,[36] most browsers have never fully implemented it, a lot of websites set this
	 * field with fake policy text, that was enough to fool browsers the existence of P3P policy and grant permissions
	 * for third party cookies.
	 *
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/HTTP_cookie#Third-party_cookie
	 * @example P3P: CP="This is not a P3P policy! See
	 *          http://www.google.com/support/accounts/bin/answer.py?hl=en&answer=151657 for more info."
	 * @status  Permanent
	 */
	const RESPONSE_P3P = 'P3P';

	/**
	 * Pragma
	 *
	 * Implementation-specific fields that may have various effects anywhere along the request-response chain.
	 *
	 * @var string
	 * @example Pragma: no-cache
	 * @status  Permanent
	 */
	const RESPONSE_PRAGMA = 'Pragma';

	/**
	 * Proxy-Authenticate
	 *
	 * Request authentication to access the proxy.
	 *
	 * @var string
	 * @example Proxy-Authenticate: Basic
	 * @status  Permanent
	 */
	const RESPONSE_PROXY_AUTHENTICATE = 'Proxy-Authenticate';

	/**
	 * Refresh
	 *
	 * Used in redirection, or when a new resource has been created. This refresh redirects after 5 seconds.
	 *
	 * @var string
	 * @example Refresh: 5; url=http://www.w3.org/pub/WWW/People.html
	 * @status  Proprietary and non-standard
	 */
	const RESPONSE_REFRESH = 'Refresh';

	/**
	 * Retry-After
	 *
	 * If an entity is temporarily unavailable, this instructs the client to try again later.
	 * Value could be a specified period of time (in seconds) or a HTTP-date
	 *
	 * @var string
	 * @example Retry-After: 120
	 * @status  Permanent
	 */
	const RESPONSE_RETRY_AFTER = 'Retry-After';

	/**
	 * Server
	 *
	 * A name for the server
	 *
	 * @var string
	 * @example Server: Apache/2.4.1 (Unix)
	 * @status  Permanent
	 */
	const RESPONSE_SERVER = 'Server';

	/**
	 * Set-Cookie
	 *
	 * An HTTP cookie
	 *
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/HTTP_cookie
	 * @example Set-Cookie: UserID=JohnDoe; Max-Age=3600; Version=1
	 * @status  Permanent: standard
	 */
	const RESPONSE_SET_COOKIE = 'Set-Cookie';

	/**
	 * Status
	 *
	 * CGI header field specifying the status of the HTTP response.
	 * Normal HTTP responses use a separate "Status-Line" instead, defined by RFC 7230
	 *
	 * @var string
	 * @see     https://tools.ietf.org/html/rfc7230
	 * @example Status: 200 OK
	 * @status  Not listed as a registered field name
	 */
	const RESPONSE_STATUS = 'Status';

	/**
	 * Strict-Transport-Security
	 *
	 * A HSTS Policy informing the HTTP client how long to cache the HTTPS only policy and whether this applies to
	 * subdomains.
	 *
	 * @var string
	 * @example Status: 200 OK
	 * @status  Permanent: standard
	 */
	const RESPONSE_STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';

	/**
	 * Trailer
	 *
	 * The Trailer general field value indicates that the given set of header fields is present in the trailer of a
	 * message encoded with chunked transfer coding
	 *
	 * @var string
	 * @see     https://en.wikipedia.org/wiki/Chunked_transfer_coding
	 * @example Trailer: Max-Forwards
	 * @status  Permanent
	 */
	const RESPONSE_TRAILER = 'Trailer';

	/**
	 * TSV
	 *
	 * Tracking Status Value, value suggested to be sent in response to a DNT(do-not-track)
	 *
	 * @var string
	 * @example TSV: ?
	 * @status  Permanent
	 */
	const RESPONSE_TSV = 'TSV';

	/**
	 * Upgrade
	 *
	 * Ask the client to upgrade to another protocol.
	 *
	 * @var string
	 * @example Upgrade: HTTP/2.0, HTTPS/1.3, IRC/6.9, RTA/x11, websocket
	 * @status  Permanent
	 */
	const RESPONSE_UPGRADE = 'Upgrade';

	/**
	 * Vary
	 *
	 * Tells downstream proxies how to match future request headers to decide
	 * whether the cached response can be used rather than requesting a fresh one from the origin server.
	 *
	 * @var string
	 * @example Vary: Accept-Language
	 * @status  Permanent
	 */
	const RESPONSE_VARY = 'Vary';

	/**
	 * Via
	 *
	 * Informs the client of proxies through which the response was sent.
	 *
	 * @var string
	 * @example Via: 1.0 fred, 1.1 example.com (Apache/1.1)
	 * @status  Permanent
	 */
	const RESPONSE_VIA = 'Via';

	/**
	 * Warning
	 *
	 * A general warning about possible problems with the entity body.
	 *
	 * @var string
	 * @example Warning: 199 Miscellaneous warning
	 * @status  Permanent
	 */
	const RESPONSE_WARNING = 'Warning';

	/**
	 * WWW-Authenticate
	 *
	 * Indicates the authentication scheme that should be used to access the requested entity
	 *
	 * @var string
	 * @example WWW-Authenticate: Basic
	 * @status  Permanent
	 */
	const RESPONSE_WWW_AUTHENTICATE = 'WWW-Authenticate';

	/**
	 * X-Frame-Options
	 *
	 * Clickjacking protection: deny - no rendering within a frame, sameorigin - no rendering if origin mismatch,
	 * allow-from
	 * - allow from specified location, allowall - non-standard, allow from any location
	 *
	 * @var string
	 * @example X-Frame-Options: deny
	 * @status  Obsolete
	 */
	const RESPONSE_X_FRAME_OPTIONS = 'X-Frame-Options';

	// ------------------------------------------------------------------------

	/**
	 * ------------------------------------------------------------------------
	 * Common Non-Standard Response Fields
	 * ------------------------------------------------------------------------
	 */

	/**
	 * X-XSS-Protection
	 *
	 * Cross-site scripting (XSS) filter
	 *
	 * @see     https://en.wikipedia.org/wiki/Cross-site_scripting
	 *
	 * @example X-XSS-Protection: 1; mode=block; token=xxx
	 * @status  Custom
	 * @var string
	 */
	const RESPONSE_X_XSS_PROTECTION = 'X-XSS-Protection';

	/**
	 * X-CSRF-Protection
	 *
	 * Cross-site forgery
	 *
	 * @see     https://en.wikipedia.org/wiki/Cross-site_scripting
	 *
	 * @example X-CSRF-Protection: 1; mode=block; token=xxx
	 * @status  Custom
	 * @var string
	 */
	const RESPONSE_X_CSRF_PROTECTION = 'X-CSRF-Protection';

	/**
	 * Content-Security-Policy
	 *
	 * Content Security Policy definition.
	 *
	 * @see     https://en.wikipedia.org/wiki/Content_Security_Policy
	 *
	 * @example X-WebKit-CSP: default-src 'self'
	 * @status  Custom
	 * @var string
	 */
	const RESPONSE_CONTENT_SECURITY_POLICY = 'Content-Security-Policy';

	/**
	 * X-Content-Type-Options
	 *
	 * The only defined value, "nosniff", prevents Internet Explorer from MIME-sniffing a response away from the
	 * declared content-type. This also applies to Google Chrome, when downloading extensions
	 *
	 *
	 * @example X-Content-Type-Options: nosniff
	 * @status  Custom
	 * @var string
	 */
	const RESPONSE_X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';

	/**
	 * X-Powered-By
	 *
	 * Specifies the technology (e.g. ASP.NET, PHP, JBoss)
	 * supporting the web application (version details are often in X-Runtime, X-Version, or X-AspNet-Version)
	 *
	 *
	 * @example X-Powered-By: PHP/5.4.0
	 * @status  Custom
	 * @var string
	 */
	const RESPONSE_X_POWERED_BY = 'X-Powered-By';

	/**
	 * X-Generated-By
	 *
	 * Specifies the content generator you are using.
	 *
	 * @example X-Generated-By: O2System PHP Framework v4.0.0
	 * @status  Custom
	 * @var string
	 */
	const RESPONSE_X_GENERATED_BY = 'X-Generated-By';

	/**
	 * X-UA-Compatible
	 *
	 * Recommends the preferred rendering engine (often a backward-compatibility mode) to use to display the content.
	 * Also used to activate Chrome Frame in Internet Explorer
	 *
	 * @example X-UA-Compatible: IE=EmulateIE7
	 * @status  Custom
	 * @var string
	 */
	const RESPONSE_X_UA_COMPATIBLE = 'X-UA-Compatible';

	/**
	 * X-Content-Duration
	 *
	 * Provide the duration of the audio or video in seconds; only supported by Gecko browsers
	 *
	 * @example X-Content-Duration: 42.666
	 * @status  Custom
	 * @var string
	 */
	const RESPONSE_X_CONTENT_DURATION = 'X-Content-Duration';

	/**
	 * Upgrade-Insecure-Requests
	 *
	 * Tells a server which (presumably in the middle of a HTTP -> HTTPS migration) hosts mixed content
	 * that the client would prefer redirection to HTTPS and can handle
	 * Content-Security-Policy: upgrade-insecure-requests
	 *
	 * @example Upgrade-Insecure-Requests: 1
	 * @status  Custom
	 * @var string
	 */
	const RESPONSE_UPGRADE_INSECURE_REQUESTS = 'Upgrade-Insecure-Requests';

	/**
	 * X-Request-ID
	 *
	 * Correlates HTTP requests between a client and server
	 *
	 * @example X-Request-ID: f058ebd6-02f7-4d3f-942e-904344e8cde5
	 * @status  Custom
	 * @var string
	 */
	const RESPONSE_X_REQUEST_ID = 'X-Request-ID';
}