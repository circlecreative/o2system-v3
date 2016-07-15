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
	final public function _afterProcessRebuild( $id_parent = 0, $left = 0, $depth = 0 )
	{
		$table = empty( $table ) ? $this->table : $table;

		/* the right value of this node is the left value + 1 */
		$right = $left + 1;

		/* get all children of this node */
		$this->db->select( 'id' )->where( 'id_parent', $id_parent )->orderBy( 'record_ordering' );
		$query = $this->db->get( $table );

		if ( $query->numRows() > 0 )
		{
			foreach ( $query->result() as $row )
			{
				/* does this page have children? */
				$right = $this->rebuildTree( $table, $row->id, $right, $depth + 1 );
			}
		}

		/* update this page with the (possibly) new left, right, and depth values */
		$data = [ 'record_left' => $left, 'record_right' => $right, 'record_depth' => $depth - 1 ];
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
	final public function getParents( $id = 0, &$parents = [ ] )
	{
		$query = $this->db->getWhere( $this->table, [ 'id' => $id ] );

		if ( $query->numRows() > 0 )
		{
			$parents[] = $query->firstRow();

			if ( (int) $query->firstRow()->id_parent > 0 )
			{
				$this->getRowParents( $query->firstRow()->id_parent, $parents );
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
	public function getChilds( $id_parent = NULL )
	{
		if ( isset( $id_parent ) )
		{
			$this->db->where( 'id_parent', $id_parent )->orWhere( 'id', $id_parent );
		}

		if ( $this->db->fieldExists( 'record_left', $this->table ) )
		{
			$this->db->orderBy( 'record_left', 'ASC' );
		}

		if ( $this->db->fieldExists( 'record_ordering', $this->table ) )
		{
			$this->db->orderBy( 'record_ordering', 'ASC' );
		}

		$query = $this->db->get( $this->table );

		if ( $query->numRows() > 0 )
		{
			return $query->result();
		}

		return [ ];
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
	final public function hasChilds( $id_parent = 0 )
	{
		$query = $this->db->select( 'id' )->where( 'id_parent', $id_parent )->get( $this->table );

		if ( $query->numRows() > 0 )
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
	final public function countChilds( $id_parent )
	{
		return $this->db->select( 'id' )->getWhere( $this->table, [ 'id_parent' => $id_parent ] )->numRows();
	}
	// ------------------------------------------------------------------------
}