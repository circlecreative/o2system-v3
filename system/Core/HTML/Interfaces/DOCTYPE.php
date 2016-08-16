<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 9:13 AM
 */

namespace O2System\Core\HTML\Interfaces;


interface DOCTYPE
{
	/**
	 * HTML 5
	 *
	 * This DTD contains all HTML elements and attributes, INCLUDING
	 * mobile ready elements and attributes.
	 *
	 * @type int
	 */
	const HTML5 = 1;

	/**
	 * HTML 4.01 Strict
	 *
	 * This DTD contains all HTML elements and attributes, but does NOT INCLUDE
	 * presentational or deprecated elements (like font). Framesets are not allowed.
	 *
	 * @type int
	 */
	const HTML4_STRICT = 2;

	/**
	 * HTML 4.01 Transitional
	 *
	 * This DTD contains all HTML elements and attributes, INCLUDING
	 * presentational and deprecated elements (like font). Framesets are not allowed.
	 *
	 * @type int
	 */
	const HTML4_TRANSITIONAL = 3;

	/**
	 * HTML 4.01 Frameset
	 *
	 * This DTD is equal to HTML 4.01 Transitional, but allows the use of frameset content.
	 *
	 * @type int
	 */
	const HTML4_FRAMESET = 4;

	/**
	 * XHTML 1.0 Strict
	 *
	 * This DTD contains all HTML elements and attributes, but does NOT INCLUDE
	 * presentational or deprecated elements (like font). Framesets are not allowed.
	 * The markup must also be written as well-formed XML.
	 *
	 * @type int
	 */
	const XHTML1_STRICT = 5;

	/**
	 * XHTML 1.0 Transitional
	 *
	 * This DTD contains all HTML elements and attributes, INCLUDING
	 * presentational and deprecated elements (like font). Framesets are not allowed.
	 * The markup must also be written as well-formed XML.
	 *
	 * @type int
	 */
	const XHTML1_TRANSITIONAL = 6;

	/**
	 * XHTML 1.0 Frameset
	 *
	 * This DTD is equal to XHTML 1.0 Transitional, but allows the use of frameset content.
	 *
	 * @type int
	 */
	const XHTML1_FRAMESET = 7;

	/**
	 * XHTML 1.1
	 *
	 * This DTD is equal to XHTML 1.0 Strict, but allows you to add modules
	 * (for example to provide ruby support for East-Asian languages).
	 *
	 * @type int
	 */
	const XHTML11 = 8;
}