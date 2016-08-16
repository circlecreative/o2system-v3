<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12/3/2015
 * Time: 12:42 PM
 */

namespace O2System\ORM\Interfaces;


trait Modifier
{
	/**
	 * Insert Data
	 *
	 * Method to input data as well as equipping the data in accordance with the fields
	 * in the destination database table.
	 *
	 * @param   array $row          Insert Row Data
	 * @param   bool  $affectedRows Return num of affected rows
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function insert( $row, $affectedRows = FALSE )
	{
		print_out( $row );
		$return = FALSE;

		if ( $this->db->tableExists( $this->table ) AND ! empty( $row ) )
		{
			if ( ! isset( $row[ 'record_status' ] ) )
			{
				$row[ 'record_status' ] = 'PUBLISH';
			}

			$row[ 'record_create_user' ] = @$this->libraries->access->login->account()->id;

			if ( ! isset( $row[ 'record_create_timestamp' ] ) )
			{
				$row[ 'record_create_timestamp' ] = date( 'Y-m-d H:i:s' );
			}

			$row[ 'record_update_user' ] = @$this->libraries->access->login->account()->id;

			if ( ! isset( $row[ 'record_update_timestamp' ] ) )
			{
				$row[ 'record_update_timestamp' ] = date( 'Y-m-d H:i:s' );
			}

			$return = $this->db->insert( $this->table, $row );

			if ( $this->db->affectedRows() > 0 )
			{
				if ( $affectedRows === TRUE )
				{
					$return = $affectedRows === TRUE ? $this->db->insertId() : TRUE;
				}

				if ( $this->db->fieldExists( 'record_left', $this->table ) )
				{
					$this->rebuildTree( $this->table );
				}
			}
		}

		return $return;
	}

	/**
	 * Insert Many
	 *
	 * Insert multiple rows into the table. Returns an array of multiple IDs.
	 *
	 * @param   array $rows
	 * @param   bool  $affectedRows Return num of affected rows
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function insertMany( array $rows, $affectedRows = FALSE )
	{
		$return = FALSE;

		foreach ( $rows as $row )
		{
			$return = $this->insert( $row );
		}

		if ( $this->db->affectedRows() > 0 )
		{
			if ( $affectedRows === TRUE )
			{
				$return = $affectedRows === TRUE ? $this->db->insertId() : TRUE;
			}

			if ( $this->db->fieldExists( 'record_left', $this->table ) )
			{
				$this->rebuildTree( $this->table );
			}
		}

		return $return;
	}

	// ------------------------------------------------------------------------

	/**
	 * Update
	 *
	 * Method to update data as well as equipping the data in accordance with the fields
	 * in the destination database table.
	 *
	 * @param   array $row          Update row data
	 * @param   bool  $affectedRows Return num of affected rows
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function update( $row, $affectedRows = FALSE )
	{
		$return = FALSE;

		if ( ! isset( $row[ 'record_status' ] ) )
		{
			$row[ 'record_status' ] = 1;
		}

		$row[ 'record_update_timestamp' ] = date( 'Y-m-d H:i:s' );

		$return = $this->db->where( 'id', $row[ 'id' ] )->update( $this->table, $row );

		if ( $this->db->affectedRows() > 0 )
		{
			if ( $affectedRows === TRUE )
			{
				$return = $affectedRows === TRUE ? $this->db->affectedRows() : TRUE;
			}

			if ( $this->db->fieldExists( 'record_left', $this->table ) )
			{
				$this->rebuildTree( $this->table );
			}
		}

		return $return;
	}

	/**
	 * Update By
	 *
	 * Method to update data as well as equipping the data in accordance with the fields
	 * in the destination database table.
	 *
	 * @param   array $row          Update row data
	 * @param   array $conditions   Update conditions
	 * @param   bool  $affectedRows Return num of affected rows
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function updateBy( $row, array $conditions, $affectedRows = FALSE )
	{
		$return = FALSE;

		if ( ! isset( $row[ 'record_status' ] ) )
		{
			$row[ 'record_status' ] = 1;
		}

		$row[ 'record_update_timestamp' ] = date( 'Y-m-d H:i:s' );

		$return = $this->db->where( $conditions )->update( $this->table, $row );

		if ( $this->db->affectedRows() > 0 )
		{
			if ( $affectedRows === TRUE )
			{
				$return = $affectedRows === TRUE ? $this->db->affectedRows() : TRUE;
			}

			if ( $this->db->fieldExists( 'record_left', $this->table ) )
			{
				$this->rebuildTree( $this->table );
			}
		}

		return $return;
	}

	/**
	 * Updated a record based on sets of ids.
	 *
	 * @param array $ids
	 * @param array $data
	 *
	 * @return mixed
	 */
	protected function updateMany( array $rows, $affectedRows = FALSE )
	{
		$return = FALSE;

		foreach ( $rows as $row )
		{
			$return = $this->update( $row );
		}

		if ( $this->db->affectedRows() > 0 )
		{
			if ( $affectedRows === TRUE )
			{
				$return = $affectedRows === TRUE ? $this->db->affectedRows() : TRUE;
			}

			if ( $this->db->fieldExists( 'record_left', $this->table ) )
			{
				$this->rebuildTree( $this->table );
			}
		}

		return $return;
	}

	// ------------------------------------------------------------------------

	protected function trash( $id )
	{
		return $this->update( [ 'id' => $id, 'record_status' => 'TRASH' ] );
	}

	protected function trashBy( $id, $conditions = [ ] )
	{
		return $this->updateBy( [ 'id' => $id, 'record_status' => 'TRASH' ], $conditions );
	}

	/**
	 * Trash many rows from the database table based on sets of ids.
	 *
	 * @param array $ids
	 *
	 * @return mixed
	 */
	protected function trashMany( array $ids )
	{
		$return = FALSE;

		foreach ( $ids as $id )
		{
			$return = $this->trash( $id );
		}

		return $return;
	}

	// ------------------------------------------------------------------------

	protected function trashManyBy( array $ids, $conditions = [ ] )
	{
		$return = FALSE;

		foreach ( $ids as $id )
		{
			$return = $this->trashBy( $id, $conditions );
		}

		return $return;
	}

	protected function delete( $id, $force = FALSE, $affectedRows = FALSE )
	{
		$return = FALSE;

		// Recursive Search Parenting
		if ( $force === TRUE )
		{
			$query = $this->db->select( 'id' )->getWhere( $this->table, [ 'id_parent' => $id ] );

			if ( $query->numRows() > 0 )
			{
				foreach ( $query->result() as $row )
				{
					$return = $this->delete( $this->table, $row->id, $force );
				}
			}
		}

		// Recursive Search File
		$fields = [ 'file', 'image', 'picture', 'cover', 'avatar', 'photo', 'video' ];

		foreach ( $fields as $field )
		{
			if ( $this->db->fieldExists( $field, $this->table ) )
			{
				$query = $this->db->select( $field )->getWhere( $this->table, [ 'id' => $id ], 1 );

				if ( $query->numRows() > 0 )
				{
					if ( ! empty( $query->firstRow()->{$field} ) )
					{
						$directory = new \RecursiveDirectoryIterator( APPSPATH );
						$iterator  = new \RecursiveIteratorIterator( $directory );
						$results   = new \RegexIterator( $iterator, '/^.+\.properties/i', \RecursiveRegexIterator::GET_MATCH );

						foreach ( $results as $file )
						{
							if ( is_array( $file ) )
							{
								foreach ( $file as $filepath )
								{
									@unlink( $filepath );
								}
							}
						}
					}
				}
			}
		}

		// Finaly Delete This Data
		$return = $this->db->where( 'id', $id )->delete( $this->table );

		if ( $this->db->affectedRows() > 0 )
		{
			if ( $affectedRows === TRUE )
			{
				$return = $affectedRows === TRUE ? $this->db->affectedRows() : TRUE;
			}

			if ( $this->db->fieldExists( 'record_left', $this->table ) )
			{
				$this->rebuildTree( $this->table );
			}
		}

		return $return;
	}

	protected function deleteBy( $id, $conditions = [ ], $force = FALSE, $affectedRows = FALSE )
	{
		$this->db->where( $conditions );

		return $this->delete( $id );
	}

	/**
	 * Delete many rows from the database table based on sets of ids.
	 *
	 * @param array $ids
	 *
	 * @return mixed
	 */
	protected function deleteMany( array $ids, $force = FALSE, $affectedRows = FALSE )
	{
		$return = FALSE;

		foreach ( $ids as $id )
		{
			$this->delete( $id, $force );
		}

		if ( $this->db->affectedRows() > 0 )
		{
			if ( $affectedRows === TRUE )
			{
				$return = $affectedRows === TRUE ? $this->db->affectedRows() : TRUE;
			}

			if ( $this->db->fieldExists( 'record_left', $this->table ) )
			{
				$this->rebuildTree( $this->table );
			}
		}

		return $return;
	}

	protected function deleteManyBy( array $ids, $conditions = [ ], $force = FALSE, $affectedRows = FALSE )
	{
		$return = FALSE;

		foreach ( $ids as $id )
		{
			$this->deleteBy( $id, $conditions, $force );
		}

		if ( $this->db->affectedRows() > 0 )
		{
			if ( $affectedRows === TRUE )
			{
				$return = $affectedRows === TRUE ? $this->db->affectedRows() : TRUE;
			}

			if ( $this->db->fieldExists( 'record_left', $this->table ) )
			{
				$this->rebuildTree( $this->table );
			}
		}

		return $return;
	}
}