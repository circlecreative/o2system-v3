<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12/3/2015
 * Time: 9:53 AM
 */

namespace O2System\ORM\Interfaces;


trait Ordering
{
	/**
	 * Process Row Ordering
	 *
	 * @access  public
	 */
	protected function _before_process_row_ordering( array $row, $table = NULL )
	{
		$table = isset( $table ) ? $table : $this->table;

		if ( ! isset( $row[ 'ordering' ] ) )
		{
			$row[ 'record_ordering' ] = $this->db->count_all_results( $table ) + 1;
		}
	}
}