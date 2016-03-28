<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12/3/2015
 * Time: 12:39 PM
 */

namespace O2System\ORM\Interfaces;


trait Getter
{
	/**
	 * All
	 *
	 * Get all rows of table
	 *
	 * @param   array $conditions Where clause conditions
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function all( $conditions = array() )
	{
		// Sort by record left
		if ( $this->db->field_exists( 'record_left', $this->table ) )
		{
			$this->db->order_by( 'record_left', 'ASC' );
		}

		// Sort by record ordering
		if ( $this->db->field_exists( 'record_ordering', $this->table ) )
		{
			$this->db->order_by( 'record_ordering', 'ASC' );
		}

		if ( ! empty( $conditions ) )
		{
			$this->db->where( $conditions );
		}

		$result = $this->db->get( $this->table );

		if ( $result->num_rows() > 0 )
		{
			return $result;
		}

		return array();
	}

	// ------------------------------------------------------------------------

	/**
	 * Rows
	 *
	 * Alias for All Method
	 *
	 * @param   array $conditions Where clause conditions
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function rows( $conditions = array() )
	{
		return $this->all( $conditions );
	}
	// ------------------------------------------------------------------------

	/**
	 * Row
	 *
	 * Get single row of model query result
	 *
	 * @access  protected
	 *
	 * @uses    O2System\ORM\Factory\Query()
	 *
	 * @return null|object  O2System\ORM\Factory\Result
	 */
	protected function row()
	{
		$result = $this->db->from( $this->table )->get();

		if ( $result->num_rows() > 0 )
		{
			return $result->row();
		}

		return NULL;
	}
}