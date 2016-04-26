<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12/3/2015
 * Time: 10:06 AM
 */

namespace O2System\ORM\Interfaces;


trait Hierarchical
{
	/**
	 * Rebuild Tree
	 *
	 * Rebuild self hierarchical table
	 *
	 * @access public
	 *
	 * @param string $table Working database table
	 *
	 * @return numeric  rgt column value
	 */
	final public function _after_process_rebuild( $id_parent = 0, $left = 0, $depth = 0 )
	{
		$table = empty( $table ) ? $this->table : $table;

		/* the right value of this node is the left value + 1 */
		$right = $left + 1;

		/* get all children of this node */
		$this->db->select( 'id' )->where( 'id_parent', $id_parent )->order_by( 'record_ordering' );
		$query = $this->db->get( $table );

		if ( $query->num_rows() > 0 )
		{
			foreach ( $query->result() as $row )
			{
				/* does this page have children? */
				$right = $this->rebuild_tree( $table, $row->id, $right, $depth + 1 );
			}
		}

		/* update this page with the (possibly) new left, right, and depth values */
		$data = array( 'record_left' => $left, 'record_right' => $right, 'record_depth' => $depth - 1 );
		$this->db->update( $table, $data, [ 'id' => $id_parent ] );

		/* return the right value of this node + 1 */

		return $right + 1;
	}

	/**
	 * Find Parents
	 *
	 * Retreive parents of a record
	 *
	 * @param numeric $id Record ID
	 *
	 * @access public
	 * @return array
	 */
	final public function get_parents( $id = 0, &$parents = array() )
	{
		$query = $this->db->get_where( $this->table, [ 'id' => $id ] );

		if ( $query->num_rows() > 0 )
		{
			$parents[] = $query->first_row();

			if ( (int) $query->first_row()->id_parent > 0 )
			{
				$this->get_row_parents( $query->first_row()->id_parent, $parents );
			}
		}

		return array_reverse( $parents );
	}
	// ------------------------------------------------------------------------

	/**
	 * Find Childs
	 *
	 * Retreive all childs
	 *
	 * @param numeric $id_parent Parent ID
	 *
	 * @access public
	 * @return array
	 */
	public function get_childs( $id_parent = NULL )
	{
		if ( isset( $id_parent ) )
		{
			$this->db->where( 'id_parent', $id_parent )->or_where( 'id', $id_parent );
		}

		if ( $this->db->field_exists( 'record_left', $this->table ) )
		{
			$this->db->order_by( 'record_left', 'ASC' );
		}

		if ( $this->db->field_exists( 'record_ordering', $this->table ) )
		{
			$this->db->order_by( 'record_ordering', 'ASC' );
		}

		$query = $this->db->get( $this->table );

		if ( $query->num_rows() > 0 )
		{
			return $query->result();
		}

		return array();
	}
	// ------------------------------------------------------------------------

	/**
	 * Has Childs
	 *
	 * Check if there is a child rows
	 *
	 * @param string $table Working database table
	 *
	 * @access public
	 * @return bool
	 */
	final public function has_childs(  $id_parent = 0 )
	{
		$query = $this->db->select( 'id' )->where( 'id_parent', $id_parent )->get( $this->table );

		if ( $query->num_rows() > 0 )
		{
			return TRUE;
		}

		return FALSE;
	}
	// ------------------------------------------------------------------------

	/**
	 * Count Childs
	 *
	 * Num childs of a record
	 *
	 * @param numeric $id_parent Record Parent ID
	 *
	 * @access public
	 * @return bool
	 */
	final public function count_childs( $id_parent )
	{
		return $this->db->select( 'id' )->get_where( $this->table, [ 'id_parent' => $id_parent ])->num_rows();
	}
	// ------------------------------------------------------------------------
}