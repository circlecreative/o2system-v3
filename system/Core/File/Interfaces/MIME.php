<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 6:38 PM
 */

namespace O2System\Core\File\Interfaces;


interface MIME
{
	const TEXT_PLAIN       = 'text/plain';
	const TEXT_HTML        = 'text/html';
	const TEXT_HTML_UTF8   = 'text/html; charset=UTF-8';
	const APPLICATION_JSON = 'application/json';
	const APPLICATION_XML  = 'application/xml';
}