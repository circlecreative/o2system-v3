<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 4:45 PM
 */

namespace O2System\Core\View\Handlers\Parser\Interfaces;


interface Engine
{
	/**
	 * Initialize Engine
	 *
	 * @param   $settings   Parser Config
	 *
	 * @access  public
	 * @return  Parser Engine Adapter Object
	 */
	public function initialize( $settings = [ ] );

	/**
	 * Parse String
	 *
	 * @param   string   String Source Code
	 * @param   array    Array of variables data to be parsed
	 *
	 * @access  public
	 * @return  string  Parse Output Result
	 */
	public function parseString( $string, $vars = [ ] );

	/**
	 * Register Plugin
	 *
	 * Registers a plugin for use in a Twig template.
	 *
	 * @access  public
	 */
	public function registerPlugin();
}