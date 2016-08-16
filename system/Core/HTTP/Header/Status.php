<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 03-Aug-16
 * Time: 7:06 PM
 */

namespace O2System\Core\HTTP\Header;

/**
 * Header Status
 *
 * This is a list of Hypertext Transfer Protocol (HTTP) response status codes.
 * It includes codes from IETF internet standards, other IETF RFCs, other specifications,
 * and some additional commonly used codes. The first digit of the status code specifies
 * one of five classes of response; an HTTP client must recognise these five classes at a minimum.
 * The phrases used are the standard wordings, but any human-readable alternative can be provided.
 * Unless otherwise stated, the status code is part of the HTTP/1.1 standard (RFC 7231)
 *
 * The Internet Assigned Numbers Authority (IANA) maintains the official registry of HTTP status codes.
 *
 * Microsoft IIS sometimes uses additional decimal sub-codes to provide more specific information,
 * but not all of those are here.
 *
 * @see     https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
 *
 * @package O2System\Core\HTTP\Header
 * @since   4.0.0
 */
interface Status
{
	/**
	 * ------------------------------------------------------------------------
	 *
	 * 1xx Informational
	 *
	 * Request received, continuing process.
	 *
	 * This class of status code indicates a provisional response, consisting only
	 * of the Status-Line and optional headers, and is terminated by an empty line.
	 * Since HTTP/1.0 did not define any 1xx status codes, servers must not send a 1xx response
	 * to an HTTP/1.0 client except under experimental conditions.
	 *
	 * ------------------------------------------------------------------------
	 */

	/**
	 * 100 Continue
	 *
	 * The server has received the request headers and the client should proceed to send the request body
	 * (in the case of a request for which a body needs to be sent; for example, a POST request).
	 * Sending a large request body to a server after a request has been rejected
	 * for inappropriate headers would be inefficient. To have a server check the request's headers,
	 * a client must send Expect: 100-continue as a header in its initial request and
	 * receive a 100 Continue status code in response before sending the body. The response 417 Expectation Failed
	 * indicates the request should not be continued.
	 *
	 * @var int
	 */
	const STATUS_CONTINUE = 100;

	/**
	 * 101 Switching Protocols
	 *
	 * The requester has asked the server to switch protocols and the server has agreed to do so.
	 *
	 * @var int
	 */
	const STATUS_SWITCHING_PROTOCOLS = 101;

	/**
	 * 102 Processing (WebDAV; RFC 2518)
	 *
	 * A WebDAV request may contain many sub-requests involving file operations,
	 * requiring a long time to complete the request. This code indicates that the server
	 * has received and is processing the request, but no response is available yet.
	 * This prevents the client from timing out and assuming the request was lost.
	 *
	 * @var int
	 */
	const STATUS_PROCESSING = 102; // WebDav; RFC 2518

	/**
	 * ------------------------------------------------------------------------
	 *
	 * 2xx Success
	 *
	 * This class of status codes indicates the action requested by the client
	 * was received, understood, accepted, and processed successfully.
	 *
	 * ------------------------------------------------------------------------
	 */

	/**
	 * 200 OK
	 *
	 * Standard response for successful HTTP requests. The actual response will depend on the request method used.
	 * In a GET request, the response will contain an entity corresponding to the requested resource. I
	 * n a POST request, the response will contain an entity describing or containing the result of the action
	 *
	 * @var int
	 */
	const STATUS_OK = 200;

	/**
	 * 201 Created
	 *
	 * The request has been fulfilled, resulting in the creation of a new resource
	 *
	 * @var int
	 */
	const STATUS_CREATED = 201;

	/**
	 * 202 Accepted
	 *
	 * The request has been accepted for processing, but the processing has not been completed.
	 * The request might or might not be eventually acted upon, and may be disallowed when processing occurs
	 *
	 * @var int
	 */
	const STATUS_ACCEPTED = 202;

	/**
	 * 203 Non-Authoritative Information (since HTTP/1.1)
	 *
	 * The server is a transforming proxy (e.g. a Web accelerator) that received a 200 OK from its origin,
	 * but is returning a modified version of the origin's response.
	 *
	 * @see https://en.wikipedia.org/wiki/Web_accelerator
	 *
	 * @var int
	 */
	const STATUS_NON_AUTHORITATIVE_INFORMATION = 203;

	/**
	 * 204 No Content
	 *
	 * The server successfully processed the request and is not returning any content
	 *
	 * @var int
	 */
	const STATUS_NO_CONTENT = 204;

	/**
	 * 205 Reset Content
	 *
	 * The server successfully processed the request, but is not returning any content.
	 * Unlike a 204 response, this response requires that the requester reset the document view.
	 *
	 * @var int
	 */
	const STATUS_RESET_CONTENT = 205;

	/**
	 * 206 Partial Content (RFC 7233)
	 *
	 * The server is delivering only part of the resource (byte serving) due to a range header sent by the client.
	 * The range header is used by HTTP clients to enable resuming of interrupted downloads,
	 * or split a download into multiple simultaneous streams.
	 *
	 * @see https://en.wikipedia.org/wiki/Byte_serving
	 *
	 * @var int
	 */
	const STATUS_PARTIAL_CONTENT = 206;

	/**
	 * 207 Multi-Status (WebDAV; RFC 4918)
	 *
	 * The message body that follows is an XML message and can contain a number of separate response codes,
	 * depending on how many sub-requests were made.
	 *
	 * @var int
	 */
	const STATUS_MULTI_STATUS = 207;

	/**
	 * 208 Already Reported (WebDAV; RFC 5842)
	 *
	 * The members of a DAV binding have already been enumerated in a previous reply to this request,
	 * and are not being included again.
	 *
	 * @var int
	 */
	const STATUS_ALREADY_REPORTED = 208;

	/**
	 * 226 IM Used (RFC 3229)
	 *
	 * The server has fulfilled a request for the resource, and the response is a representation of the result
	 * of one or more instance-manipulations applied to the current instance.
	 *
	 * @var int
	 */
	const STATUS_IM_USED = 226;

	/**
	 * ------------------------------------------------------------------------
	 *
	 * 3xx Redirection
	 *
	 * @see https://en.wikipedia.org/wiki/URL_redirection
	 *
	 * This class of status code indicates the client must take additional action to complete the request.
	 * Many of these status codes are used in URL redirection.
	 *
	 * A user agent may carry out the additional action with no user interaction only if the method used
	 * in the second request is GET or HEAD. A user agent may automatically redirect a request.
	 * A user agent should detect and intervene to prevent cyclical redirects.
	 *
	 * ------------------------------------------------------------------------
	 */

	/**
	 * 300 Multiple Choices
	 *
	 * Indicates multiple options for the resource from which the client may choose. For example,
	 * this code could be used to present multiple video format options, to list files with different extensions,
	 * or to suggest word sense disambiguation
	 *
	 * @see https://en.wikipedia.org/wiki/Filename_extension
	 *
	 * @see https://en.wikipedia.org/wiki/Word-sense_disambiguation
	 *
	 * @var int
	 */
	const STATUS_MULTIPLE_CHOICES = 300;

	/**
	 * 301 Moved Permanently
	 *
	 * This and all future requests should be directed to the given URI
	 *
	 * @see https://en.wikipedia.org/wiki/HTTP_301
	 *
	 * @var int
	 */
	const STATUS_MOVED_PERMANENTLY = 301;

	/**
	 * 302 Found
	 *
	 * This is an example of industry practice contradicting the standard. The HTTP/1.0 specification (RFC 1945)
	 * required the client to perform a temporary redirect (the original describing phrase was "Moved Temporarily"),
	 * but popular browsers implemented 302 with the functionality of a 303 See Other.
	 * Therefore, HTTP/1.1 added status codes 303 and 307 to distinguish between the two behaviours.
	 * However, some Web applications and frameworks use the 302 status code as if it were the 303
	 *
	 * @see https://en.wikipedia.org/wiki/HTTP_302
	 *
	 * @var int
	 */
	const STATUS_FOUND = 302;

	/**
	 * 303 See Other (since HTTP/1.1)
	 *
	 * The response to the request can be found under another URI using a GET method.
	 * When received in response to a POST (or PUT/DELETE), the client should presume that the server has received
	 * the data and should issue a redirect with a separate GET message
	 *
	 * @see https://en.wikipedia.org/wiki/HTTP_303
	 *
	 * @var int
	 */
	const STATUS_SEE_OTHER = 303;

	/**
	 * 304 Not Modified (RFC 7232)
	 *
	 * Indicates that the resource has not been modified since the version specified by the request headers
	 * If-Modified-Since or If-None-Match. In such case, there is no need to retransmit the resource
	 * since the client still has a previously-downloaded copy.
	 *
	 * @see https://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Request_Headers
	 *
	 * @var int
	 */
	const STATUS_NOT_MODIFIED = 304;

	/**
	 * 305 Use Proxy (since HTTP/1.1)
	 *
	 * The requested resource is available only through a proxy, the address for which is provided in the response.
	 * Many HTTP clients (such as Mozilla[29] and Internet Explorer) do not correctly handle responses
	 * with this status code, primarily for security reasons
	 *
	 * @var int
	 */
	const STATUS_USE_PROXY = 305;

	/**
	 * 306 Switch Proxy
	 *
	 * No longer used. Originally meant "Subsequent requests should use the specified proxy
	 *
	 * @var int
	 */
	const STATUS_SWITCH_PROXY = 306;

	/**
	 * 307 Temporary Redirect (since HTTP/1.1)
	 *
	 * In this case, the request should be repeated with another URI; however,
	 * future requests should still use the original URI. In contrast to how 302 was historically implemented,
	 * the request method is not allowed to be changed when reissuing the original request. For example,
	 * a POST request should be repeated using another POST request
	 *
	 * @var int
	 */
	const STATUS_TEMPORARY_REDIRECT = 307;

	/**
	 * 308 Permanent Redirect (RFC 7538)
	 *
	 * The request and all future requests should be repeated using another URI.
	 * 307 and 308 parallel the behaviours of 302 and 301, but do not allow the HTTP method to change.
	 * So, for example, submitting a form to a permanently redirected resource may continue smoothly.
	 *
	 * @var int
	 */
	const STATUS_PERMANENT_REDIRECT = 308;

	/**
	 * ------------------------------------------------------------------------
	 *
	 * 4xx Client Error
	 *
	 * The server failed to fulfill an apparently valid request.
	 *
	 * The 4xx class of status code is intended for situations in which the client seems to have erred.
	 * Except when responding to a HEAD request, the server should include an entity containing
	 * an explanation of the error situation, and whether it is a temporary or permanent condition.
	 * These status codes are applicable to any request method. User agents should display
	 * any included entity to the user.
	 *
	 * ------------------------------------------------------------------------
	 */

	/**
	 * 400 Bad Request
	 *
	 * The server cannot or will not process the request due to an apparent client error
	 * (e.g., malformed request syntax, too large size, invalid request message framing,
	 * or deceptive request routing).
	 */
	const STATUS_BAD_REQUEST = 400;

	/**
	 * 401 Unauthorized (RFC 7235)
	 *
	 * Similar to 403 Forbidden, but specifically for use when authentication is required and has failed
	 * or has not yet been provided. The response must include a WWW-Authenticate header field containing a challenge
	 * applicable to the requested resource. See Basic access authentication and Digest access authentication.
	 * 401 semantically means "unauthenticated", i.e. the user does not have the necessary credentials.
	 *
	 * @note Some sites issue HTTP 401 when an IP address is banned from the website (usually the website domain)
	 * and that specific address is refused permission to access a website
	 *
	 * @var int
	 */
	const STATUS_UNAUTHORIZED = 401;

	/**
	 * 402 Payment Required
	 *
	 * Reserved for future use. The original intention was that this code might be used as part of some
	 * form of digital cash or micropayment scheme, but that has not happened, and this code is not usually used.
	 * Google Developers API uses this status if a particular developer has exceeded the daily limit on requests.
	 *
	 * @var int
	 */
	const STATUS_PAYMENT_REQUIRED = 402;

	/**
	 * 403 Forbidden
	 *
	 * The request was a valid request, but the server is refusing to respond to it.
	 * 403 error semantically means "unauthorized", i.e. the user does not have the necessary permissions
	 * for the resource.
	 *
	 * @var int
	 */
	const STATUS_FORBIDDEN = 403;

	/**
	 * 404 Not Found
	 *
	 * The requested resource could not be found but may be available in the future.
	 * Subsequent requests by the client are permissible
	 *
	 * @var int
	 */
	const STATUS_NOT_FOUND = 404;

	/**
	 * 405 Method Not Allowed
	 *
	 * A request method is not supported for the requested resource; for example,
	 * a GET request on a form which requires data to be presented via POST, or a PUT request on a read-only resource.
	 *
	 * @var int
	 */
	const STATUS_METHOD_NOT_ALLOWED = 405;

	/**
	 * 406 Not Acceptable
	 *
	 * The requested resource is capable of generating only content not acceptable according
	 * to the Accept headers sent in the request
	 *
	 * @var int
	 */
	const STATUS_NOT_ACCEPTABLE = 406;

	/**
	 * 407 Proxy Authentication Required (RFC 7235)
	 *
	 * The client must first authenticate itself with the proxy
	 *
	 * @var int
	 */
	const STATUS_PROXY_AUTHENTICATION_REQUIRED = 407;

	/**
	 * 408 Request Timeout
	 *
	 * The server timed out waiting for the request. According to HTTP specifications:
	 * "The client did not produce a request within the time that the server was prepared to wait.
	 * The client MAY repeat the request without modifications at any later time.
	 *
	 * @var int
	 */
	const STATUS_REQUEST_TIMEOUT = 408;

	/**
	 * 409 Conflict
	 *
	 * Indicates that the request could not be processed because of conflict in the request,
	 * such as an edit conflict between multiple simultaneous updates.
	 *
	 * @var int
	 */
	const STATUS_CONFLICT = 409;

	/**
	 * 410 Gone
	 *
	 * Indicates that the resource requested is no longer available and will not be available again.
	 * This should be used when a resource has been intentionally removed and the resource should be purged.
	 * Upon receiving a 410 status code, the client should not request the resource in the future.
	 * Clients such as search engines should remove the resource from their indices.
	 * Most use cases do not require clients and search engines to purge the resource,
	 * and a "404 Not Found" may be used instead.
	 *
	 * @var int
	 */
	const STATUS_GONE = 410;

	/**
	 * 411 Length Required
	 *
	 * The request did not specify the length of its content, which is required by the requested resource.
	 *
	 * @var int
	 */
	const LENGTH_REQUIRED = 411;

	/**
	 * 412 Precondition Failed (RFC 7232)
	 *
	 * The server does not meet one of the preconditions that the requester put on the request
	 *
	 * @var int
	 */
	const STATUS_PRECONDITION_FAILED = 412;

	/**
	 * 413 Request Entity Too Large
	 *
	 * The request is larger than the server is willing or able to process.
	 *
	 * @var int
	 */
	const STATUS_REQUEST_ENTITY_TOO_LARGE = 413;

	/**
	 * 413 Payload Too Large (RFC 7231)
	 *
	 * The request is larger than the server is willing or able to process.
	 * Previously called "Request Entity Too Large".
	 *
	 * @var int
	 */
	const STATUS_PAYLOAD_TOO_LARGE = 413;

	/**
	 * 414 URI Too Long (RFC 7231)
	 *
	 * The URI provided was too long for the server to process. Often the result of too much data being encoded
	 * as a query-string of a GET request, in which case it should be converted to a POST request.
	 * Called "Request-URI Too Long" previously
	 *
	 * @var int
	 */
	const STATUS_REQUEST_URI_TOO_LONG = 414;

	/**
	 * 415 Unsupported Media Type
	 *
	 * The request entity has a media type which the server or resource does not support.
	 * For example, the client uploads an image as image/svg+xml,
	 * but the server requires that images use a different format
	 *
	 * @var int
	 */
	const STATUS_UNSUPPORTED_MEDIA_TYPE = 415;

	/**
	 * 416 Range Not Satisfiable (RFC 7233)
	 *
	 * The client has asked for a portion of the file (byte serving), but the server cannot supply that portion.
	 * For example, if the client asked for a part of the file that lies beyond the end of the file.
	 * Called "Requested Range Not Satisfiable" previously
	 *
	 * @var int
	 */
	const STATUS_REQUESTED_RANGE_NOT_SATISFIABLE = 416;

	/**
	 * 417 Expectation Failed
	 *
	 * The server cannot meet the requirements of the Expect request-header field
	 *
	 * @var int
	 */
	const STATUS_EXPECTATION_FAILED = 417;

	/**
	 * 418 I'm a teapot (RFC 2324)
	 *
	 * This code was defined in 1998 as one of the traditional IETF April Fools' jokes, in RFC 2324,
	 * Hyper Text Coffee Pot Control Protocol, and is not expected to be implemented by actual HTTP servers.
	 * The RFC specifies this code should be returned by tea pots requested to brew coffee.
	 * This HTTP status is used as an easter egg in some websites, including Google.com
	 *
	 * @var int
	 */
	const STATUS_IM_A_TEAPOT = 418; // RFC 2324

	/**
	 * 419 Authentication Timeout (Deprecated)
	 *
	 * Not a part of the HTTP standard, 419 Authentication Timeout denotes that previously
	 * valid authentication has expired. It is used as an alternative to 401 Unauthorized in order to
	 * differentiate from otherwise authenticated clients being denied access to specific server resources.
	 *
	 * @var int
	 */
	const STATUS_AUTHENTICATION_TIMEOUT = 419; // not in RFC 2616

	/**
	 * 421 Misdirected Request (RFC 7540)
	 *
	 * The request was directed at a server that is not able to produce a response
	 * (for example because a connection reuse).
	 *
	 * @var int
	 */
	const STATUS_MISDIRECTED_REQUEST = 421;

	/**
	 * 422 Unprocessable Entity (WebDAV; RFC 4918)
	 *
	 * The request was well-formed but was unable to be followed due to semantic errors.
	 *
	 * @var int
	 */
	const STATUS_UNPROCESSABLE_ENTITY = 422; // WebDAV; RFC 4918

	// ------------------------------------------------------------------------

	/**
	 * 423 Locked (WebDAV; RFC 4918)
	 *
	 * The resource that is being accessed is locked
	 *
	 * @var int
	 */
	const STATUS_LOCKED = 423; // WebDAV; RFC 4918

	// ------------------------------------------------------------------------

	/**
	 * 424 Failed Dependency (WebDAV; RFC 4918)
	 *
	 * The request failed due to failure of a previous request (e.g., a PROPPATCH)
	 *
	 * @var int
	 */
	const STATUS_FAILED_DEPENDENCY = 424; // WebDAV

	// ------------------------------------------------------------------------

	/**
	 * 425 Unordered Collection (Draft)
	 *
	 * @var int
	 */
	const STATUS_UNORDERED_COLLECTION = 425; // Internet draft

	// ------------------------------------------------------------------------

	/**
	 * 426 Upgrade Required
	 *
	 * The client should switch to a different protocol such as TLS/1.0, given in the Upgrade header field
	 *
	 * @var int
	 */
	const STATUS_UPGRADE_REQUIRED = 426; // RFC 2817

	// ------------------------------------------------------------------------

	/**
	 * 428 Precondition Required (RFC 6585)
	 *
	 * The origin server requires the request to be conditional. Intended to prevent "the 'lost update' problem,
	 * where a client GETs a resource's state, modifies it, and PUTs it back to the server,
	 * when meanwhile a third party has modified the state on the server, leading to a conflict
	 *
	 * @var int
	 */
	const STATUS_PRECONDITION_REQUIRED = 428; // RFC 6585

	// ------------------------------------------------------------------------

	/**
	 * 429 Too Many Requests (RFC 6585)
	 *
	 * The user has sent too many requests in a given amount of time. Intended for use with rate-limiting schemes
	 *
	 * @var int
	 */
	const STATUS_TO_MANY_REQUEST = 429; // RFC 6585

	// ------------------------------------------------------------------------

	/**
	 * 431 Request Header Fields Too Large (RFC 6585)
	 *
	 * The server is unwilling to process the request because either an individual header field,
	 * or all the header fields collectively, are too large
	 *
	 * @var int
	 */
	const STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE = 430; // RFC 6585

	/**
	 * 444 No Response
	 *
	 * Used to indicate that the server has returned no information to the client and closed the connection.
	 *
	 * @var int
	 */
	const STATUS_NO_RESPONSE = 444; // Nginx

	/**
	 * 449 Retry With
	 *
	 * The server cannot honour the request because the user has not provided the required information
	 *
	 * @var int
	 */
	const STATUS_RETRY_WITH = 449; // Microsoft

	/**
	 * 450 Blocked by Windows Parental Controls
	 *
	 * A Microsoft extension. This error is given when Windows Parental Controls are turned on and are blocking access
	 * to the given webpage
	 *
	 * @var int
	 */
	const STATUS_BLOCKED_BY_PARENTAL_CONTROLS = 450;

	/**
	 * 451 Unavailable For Legal Reasons
	 *
	 * A server operator has received a legal demand to deny access to a resource or to a set of resources that
	 * includes the requested resource.[57] The code 451 was chosen as a reference to the novel Fahrenheit 451
	 *
	 * @var int
	 */
	const STATUS_UNAVAILABLE_FOR_LEGAL_REASONS = 451;

	/**
	 * 494 Request Header Too Large
	 *
	 * Nginx internal code similar to 431 but it was introduced earlier.
	 *
	 * @var int
	 */
	const STATUS_REQUEST_HEADER_TOO_LARGE = 494;

	/**
	 * 495 Cert Error
	 *
	 * Nginx internal code used when SSL client certificate error occurred to distinguish it
	 * from 4XX in a log and an error page redirection.
	 *
	 * @var int
	 */
	const STATUS_CERT_ERROR = 495; // Nginx

	/**
	 * 496 No Cert
	 *
	 * Nginx internal code used when client didn't provide certificate to distinguish it from 4XX in a log and an error
	 * page redirection.
	 *
	 * @var int
	 */
	const STATUS_NO_CERT = 496; // Nginx

	/**
	 * 497 Http To Https
	 *
	 * Nginx internal code used for the plain HTTP requests that are sent to HTTPS port to distinguish
	 * it from 4XX in a log and an error page redirection.
	 *
	 * @var int
	 */
	const STATUS_HTTP_TO_HTTPS = 497; // Nginx

	/**
	 * 499 Client Closed Request
	 *
	 * Used in Nginx logs to indicate when the connection has been closed by client while the server
	 * is still processing its request, making server unable to send a status code back.
	 *
	 * @var int
	 */
	const STATUS_CLIENT_CLOSED_REQUEST = 499; // Nginx

	/**
	 * ------------------------------------------------------------------------
	 *
	 * 5xx Server Error
	 *
	 * The server failed to fulfill an apparently valid request.
	 *
	 * Response status codes beginning with the digit "5" indicate cases in which the server is aware
	 * that it has encountered an error or is otherwise incapable of performing the request.
	 * Except when responding to a HEAD request, the server should include an entity containing an explanation
	 * of the error situation, and indicate whether it is a temporary or permanent condition.
	 * Likewise, user agents should display any included entity to the user. These response codes are
	 * applicable to any request method.
	 *
	 * ------------------------------------------------------------------------
	 */

	/**
	 * 500 Internal Server Error
	 *
	 * A generic error message, given when an unexpected condition was encountered
	 * and no more specific message is suitable.
	 *
	 * @var int
	 */
	const STATUS_INTERNAL_SERVER_ERROR = 500;

	/**
	 * 501 Not Implemented
	 *
	 * The server either does not recognize the request method, or it lacks the ability to fulfill the request.
	 * Usually this implies future availability (e.g., a new feature of a web-service API)
	 *
	 * @var int
	 */
	const STATUS_NOT_IMPLEMENTED = 501;

	/**
	 * 502 Bad Gateway
	 *
	 * The server was acting as a gateway or proxy and received an invalid response from the upstream server.
	 *
	 * @var int
	 */
	const STATUS_BAD_GATEWAY = 502;

	/**
	 * 503 Service Unavailable
	 *
	 * The server is currently unavailable (because it is overloaded or down for maintenance). Generally, this is a
	 * temporary state
	 *
	 * @var int
	 */
	const STATUS_SERVICE_UNAVAILABLE = 503;

	/**
	 * 504 Gateway Timeout
	 *
	 * The server was acting as a gateway or proxy and did not receive a timely response from the upstream server
	 *
	 * @var int
	 */
	const STATUS_GATEWAY_TIMEOUT = 504;

	/**
	 * 505 HTTP Version Not Supported
	 *
	 * The server does not support the HTTP protocol version used in the request
	 *
	 * @var int
	 */
	const STATUS_HTTP_VERSION_NOT_SUPPORTED = 505;

	/**
	 * 506 Variant Also Negotiates (RFC 2295)
	 *
	 * Transparent content negotiation for the request results in a circular reference.
	 *
	 * @see https://en.wikipedia.org/wiki/Circular_reference
	 * @var int
	 */
	const STATUS_VARIANT_ALSO_NEGOTIATES = 506; // RFC 2295

	/**
	 * 507 Insufficient Storage (WebDAV; RFC 4918)
	 *
	 * The server is unable to store the representation needed to complete the request
	 *
	 * @var int
	 */
	const STATUS_INSUFFICIENT_STORAGE = 507; // WebDAV; RFC 4918

	/**
	 * 508 Loop Detected (WebDAV; RFC 5842)
	 *
	 * The server detected an infinite loop while processing the request (sent in lieu of 208 Already Reported).
	 *
	 * @var int
	 */
	const STATUS_LOOP_DETECTED = 508; // WebDAV; RFC 5842

	/**
	 * 509 Bandwidth Limit Exceeded
	 *
	 * The server bandwidth Exceeded limit
	 *
	 * @var int
	 */
	const STATUS_BANDWIDTH_LIMIT_EXCEEDED = 509;  // Apache bw/limited extension

	/**
	 * 510 Not Extended
	 *
	 * Further extensions to the request are required for the server to fulfil it.
	 *
	 * @var int
	 */
	const STATUS_NOT_EXTENDED = 510; // RFC 2774

	/**
	 * 511 Network Authentication Required
	 *
	 * The client needs to authenticate to gain network access. Intended for use by intercepting proxies used to
	 * control access to the network
	 * (e.g., "captive portals" used to require agreement to Terms of Service before granting full Internet access via
	 * a Wi-Fi hotspot).
	 *
	 * @var int
	 */
	const STATUS_NETWORK_AUTHENTICATION_REQUIRED = 511; // RFC 6585

	/**
	 * 598 Network read timeout error
	 *
	 * Used by some HTTP proxies to signal a network read timeout behind the proxy to a client in front of the proxy.
	 *
	 * @var int
	 */
	const STATUS_NETWORK_READ_TIMEOUT_ERROR = 598;

	/**
	 * 599 NETWORK CONNECT TIMEOUT ERROR
	 *
	 * This status code is not specified in any RFCs, but is used by some HTTP proxies to signal
	 * a network connect timeout behind the proxy to a client in front of the proxy
	 *
	 * @var int
	 */
	const STATUS_NETWORK_CONNECT_TIMEOUT_ERROR = 599;

	/**
	 * ------------------------------------------------------------------------
	 *
	 * 700 O2System Framework Error
	 *
	 * A generic error message, given when an unexpected condition was encountered
	 * and no more specific message is suitable.
	 *
	 * ------------------------------------------------------------------------
	 */
	const METHOD_REQUEST_NOT_FOUND = 701; // O2System Framework
	const METHOD_INVALID_PARAMETER = 702;
	const CONFIGURATION_MISSING    = 703;
	const CONFIGURATION_INVALID    = 704;
	const MISSING_LIBRARY          = 705;

	// ------------------------------------------------------------------------
}