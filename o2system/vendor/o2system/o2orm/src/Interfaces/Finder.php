<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12/3/2015
 * Time: 12:38 PM
 */

namespace O2System\ORM\Interfaces;


trait Finder
{
	/**
	 * Find
	 *
	 * Find single or many record base on criteria by specific field
	 *
	 * @param   string      $criteria Criteria value
	 * @param   string|null $field    Table column field name | set to primary key by default
	 *
	 * @access  protected
	 * @return  null|object  O2System\ORM\Factory\Result
	 */
	protected function find( $criteria, $field = NULL )
	{
		if ( is_array( $criteria ) )
		{
			return $this->find_in( $criteria, $field );
		}

		$field = isset( $field ) ? $field : $this->primary_key;

		$result = $this->db->limit( 1 )->where( $field, $criteria )->get( $this->table );

		if ( $result->num_rows() > 0 )
		{
			return $result;
		}

		return NULL;
	}
	// ------------------------------------------------------------------------

	/**
	 * Find By
	 *
	 * Find single record based on certain conditions
	 *
	 * @param   array $conditions List of conditions with criteria
	 *
	 * @access  protected
	 * @return  null|object O2System\ORM\Factory\Result
	 */
	protected function find_by( array $conditions )
	{
		$result = $this->db->limit( 1 )->where( $conditions )->get( $this->table );

		if ( $result->num_rows() > 0 )
		{
			return $result;
		}

		return NULL;
	}
	// ------------------------------------------------------------------------

	/**
	 * Find In
	 *
	 * Find many records within criteria on specific field
	 *
	 * @param   array  $in_criteria List of criteria
	 * @param   string $field       Table column field name | set to primary key by default
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function find_in( array $in_criteria, $field = 'id' )
	{
		$field = isset( $field ) ? $field : $this->primary_key;

		$result = $this->db->where_in( $field, $in_criteria )->get( $this->table );

		if ( $result->num_rows() > 0 )
		{
			return $result;
		}

		return array();
	}
	// ------------------------------------------------------------------------

	/**
	 * Find In
	 *
	 * Find many records not within criteria on specific field
	 *
	 * @param   array  $not_in_criteria List of criteria
	 * @param   string $field           Table column field name | set to primary key by default
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function find_not_in( array $not_in_criteria, $field = 'id' )
	{
		$field = isset( $field ) ? $field : $this->primary_key;

		$result = $this->db->where_in( $field, $not_in_criteria )->get( $this->table );

		if ( $result->num_rows() > 0 )
		{
			return $result;
		}

		return array();
	}
	// ------------------------------------------------------------------------

	/**
	 * Find Many
	 *
	 * Find many records within criteria on specific field
	 *
	 * @param   array  $criteria Criteria value
	 * @param   string $field    Table column field name | set to primary key by default
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function find_many( $criteria, $field = NULL )
	{
		$field = isset( $field ) ? $field : $this->primary_key;

		$result = $this->db->where( $field, $criteria )->get( $this->table );

		if ( $result->num_rows() > 0 )
		{
			return $result;
		}

		return array();
	}
	// ------------------------------------------------------------------------

	/**
	 * Find Many By
	 *
	 * Find many records based on certain conditions
	 *
	 * @access  protected
	 *
	 * @param array $conditions list of conditions with criteria
	 *
	 * @return null|object  O2System\ORM\Factory\Result
	 */
	protected function find_many_by( array $conditions )
	{
		foreach ( $conditions as $field => $in_criteria )
		{
			if ( is_string( $in_criteria ) )
			{
				$this->db->where( $field, $in_criteria );
			}
			elseif ( is_array( $in_criteria ) )
			{
				$this->db->where_in( $field, $in_criteria );
			}
		}

		$result = $this->db->get( $this->table );

		if ( $result->num_rows() > 0 )
		{
			return $result;
		}

		return array();
	}
	// ------------------------------------------------------------------------
}