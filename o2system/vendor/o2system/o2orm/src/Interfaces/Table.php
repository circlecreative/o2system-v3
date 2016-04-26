<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 11/12/2015
 * Time: 8:57 PM
 */

namespace O2System\ORM\Interfaces;


abstract class Table
{
	/**
	 * List of Possibles Table Prefixes
	 *
	 * @access  public
	 * @type    array
	 */
	public static $prefixes = array(
		'', // none prefix
		'tm_', // table master prefix
		't_', // table data prefix
		'tr_', // table relation prefix
		'ts_', // table statistic prefix
		'tb_', // table buffer prefix
	);
}