<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 7:52 PM
 */

namespace O2System\Core\HTTP\Header;

/**
 * HTTP Version
 *
 * HTTP is an application layer protocol designed within the framework of the Internet Protocol Suite.
 * Its definition presumes an underlying and reliable transport layer protocol, and
 * Transmission Control Protocol (TCP) is commonly used. However HTTP can be adapted to use unreliable
 * protocols such as the User Datagram Protocol (UDP), for example in HTTPU and
 * Simple Service Discovery Protocol (SSDP).
 *
 * @package O2System\Core\HTTP\Header
 */
interface Protocol
{
	/**
	 * HTTP/1.0
	 *
	 * HTTP/1.0 is the original version of HTTP
	 *
	 * @var string
	 */
	const HTTP_VERSION_1 = 'HTTP/1.0';

	/**
	 * HTTP/1.1
	 *
	 * HTTP/1.1 is a revision of the original HTTP (HTTP/1.0). In HTTP/1.0 a separate connection
	 * to the same server is made for every resource request. HTTP/1.1 can reuse a connection multiple times
	 * to download images, scripts, stylesheets, etc after the page has been delivered. HTTP/1.1
	 * communications therefore experience less latency as the establishment of TCP connections
	 * presents considerable overhead.
	 *
	 * @var string
	 */
	const HTTP_VERSION_11 = 'HTTP/1.1';

	/**
	 * HTTP/2.0
	 *
	 * HTTP/2 (originally named HTTP/2.0) is a major revision of the HTTP network protocol
	 * used by the World Wide Web. It was developed from the earlier experimental SPDY protocol,
	 * originally developed by Google. HTTP/2 was developed by the Hypertext Transfer Protocol
	 * working group (httpbis, where bis means "second") of the Internet Engineering Task Force.
	 * HTTP/2 is the first new version of HTTP since HTTP 1.1, which was standardized in RFC 2068 in 1997.
	 * The Working Group presented HTTP/2 to IESG for consideration as a Proposed Standard in December 2014,
	 * and IESG approved it to publish as Proposed Standard on February 17, 2015.
	 * The HTTP/2 specification was published as RFC 7540 in May 2015.
	 *
	 * @see https://en.wikipedia.org/wiki/HTTP/2
	 *
	 * @var string
	 */
	const HTTP_VERSION_2 = 'HTTP/2.0';
}