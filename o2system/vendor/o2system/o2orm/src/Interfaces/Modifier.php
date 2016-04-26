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
	 * @param   array $row           Insert Row Data
	 * @param   bool  $affected_rows Return num of affected rows
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function insert( $row, $affected_rows = FALSE )
	{
		print_out($row);
		$return = FALSE;

		if ( $this->db->table_exists( $this->table ) AND ! empty( $row ) )
		{
			if ( ! isset( $row[ 'record_status' ] ) )
			{
				$row[ 'record_status' ] = 'PUBLISH';
			}

			$row[ 'record_create_user' ] = @$this->access->login->account()->id;

			if ( ! isset( $row[ 'record_create_timestamp' ] ) )
			{
				$row[ 'record_create_timestamp' ] = date( 'Y-m-d H:i:s' );
			}

			$row[ 'record_update_user' ] = @$this->access->login->account()->id;

			if ( ! isset( $row[ 'record_update_timestamp' ] ) )
			{
				$row[ 'record_update_timestamp' ] = date( 'Y-m-d H:i:s' );
			}

			$return = $this->db->insert( $this->table, $row );

			if ( $this->db->affected_rows() > 0 )
			{
				if ( $affected_rows === TRUE )
				{
					$return = $affected_rows === TRUE ? $this->db->insert_id() : TRUE;
				}

				if ( $this->db->field_exists( 'record_left', $this->table ) )
				{
					$this->rebuild_tree( $this->table );
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
	 * @param   bool  $affected_rows Return num of affected rows
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function insert_many( array $rows, $affected_rows = FALSE )
	{
		$return = FALSE;

		foreach ( $rows as $row )
		{
			$return = $this->insert( $row );
		}

		if ( $this->db->affected_rows() > 0 )
		{
			if ( $affected_rows === TRUE )
			{
				$return = $affected_rows === TRUE ? $this->db->insert_id() : TRUE;
			}

			if ( $this->db->field_exists( 'record_left', $this->table ) )
			{
				$this->rebuild_tree( $this->table );
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
	 * @param   array $row           Update row data
	 * @param   bool  $affected_rows Return num of affected rows
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function update( $row, $affected_rows = FALSE )
	{
		$return = FALSE;

		if ( ! isset( $row[ 'record_status' ] ) )
		{
			$row[ 'record_status' ] = 1;
		}

		$row[ 'record_update_timestamp' ] = date( 'Y-m-d H:i:s' );

		$return = $this->db->where( 'id', $row[ 'id' ] )->update( $this->table, $row );

		if ( $this->db->affected_rows() > 0 )
		{
			if ( $affected_rows === TRUE )
			{
				$return = $affected_rows === TRUE ? $this->db->affected_rows() : TRUE;
			}

			if ( $this->db->field_exists( 'record_left', $this->table ) )
			{
				$this->rebuild_tree( $this->table );
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
	 * @param   array $row           Update row data
	 * @param   array $conditions    Update conditions
	 * @param   bool  $affected_rows Return num of affected rows
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function update_by( $row, array $conditions, $affected_rows = FALSE )
	{
		$return = FALSE;

		if ( ! isset( $row[ 'record_status' ] ) )
		{
			$row[ 'record_status' ] = 1;
		}

		$row[ 'record_update_timestamp' ] = date( 'Y-m-d H:i:s' );

		$return = $this->db->where( $conditions )->update( $this->table, $row );

		if ( $this->db->affected_rows() > 0 )
		{
			if ( $affected_rows === TRUE )
			{
				$return = $affected_rows === TRUE ? $this->db->affected_rows() : TRUE;
			}

			if ( $this->db->field_exists( 'record_left', $this->table ) )
			{
				$this->rebuild_tree( $this->table );
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
	protected function update_many( array $rows, $affected_rows = FALSE )
	{
		$return = FALSE;

		foreach ( $rows as $row )
		{
			$return = $this->update( $row );
		}

		if ( $this->db->affected_rows() > 0 )
		{
			if ( $affected_rows === TRUE )
			{
				$return = $affected_rows === TRUE ? $this->db->affected_rows() : TRUE;
			}

			if ( $this->db->field_exists( 'record_left', $this->table ) )
			{
				$this->rebuild_tree( $this->table );
			}
		}

		return $return;
	}

	// ------------------------------------------------------------------------

	protected function trash( $id )
	{
		return $this->update( [ 'id' => $id, 'record_status' => 'TRASH' ] );
	}

	protected function trash_by( $id, $conditions = array() )
	{
		return $this->update_by( [ 'id' => $id, 'record_status' => 'TRASH' ], $conditions );
	}

	/**
	 * Trash many rows from the database table based on sets of ids.
	 *
	 * @param array $ids
	 *
	 * @return mixed
	 */
	protected function trash_many( array $ids )
	{
		$return = FALSE;

		foreach ( $ids as $id )
		{
			$return = $this->trash( $id );
		}

		return $return;
	}

	// ------------------------------------------------------------------------

	protected function trash_many_by( array $ids, $conditions = array() )
	{
		$return = FALSE;

		foreach ( $ids as $id )
		{
			$return = $this->trash_by( $id, $conditions );
		}

		return $return;
	}

	protected function delete( $id, $force = FALSE, $affected_rows = FALSE )
	{
		$return = FALSE;

		// Recursive Search Parenting
		if ( $force === TRUE )
		{
			$query = $this->db->select( 'id' )->get_where( $this->table, [ 'id_parent' => $id ] );

			if ( $query->num_rows() > 0 )
			{
				foreach ( $query->result() as $row )
				{
					$return = $this->delete( $this->table, $row->id, $force );
				}
			}
		}

		// Recursive Search File
		$fields = array( 'file', 'image', 'picture', 'cover', 'avatar', 'photo', 'video' );

		foreach ( $fields as $field )
		{
			if ( $this->db->field_exists( $field, $this->table ) )
			{
				$query = $this->db->select( $field )->get_where( $this->table, [ 'id' => $id ], 1 );

				if ( $query->num_rows() > 0 )
				{
					if ( ! empty( $query->first_row()->{$field} ) )
					{
						$directory = new \RecursiveDirectoryIterator( APPSPATH );
						$iterator = new \RecursiveIteratorIterator( $directory );
						$results = new \RegexIterator( $iterator, '/^.+\.properties/i', \RecursiveRegexIterator::GET_MATCH );

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

		if ( $this->db->affected_rows() > 0 )
		{
			if ( $affected_rows === TRUE )
			{
				$return = $affected_rows === TRUE ? $this->db->affected_rows() : TRUE;
			}

			if ( $this->db->field_exists( 'record_left', $this->table ) )
			{
				$this->rebuild_tree( $this->table );
			}
		}

		return $return;
	}

	protected function delete_by( $id, $conditions = array(), $force = FALSE, $affected_rows = FALSE )
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
	protected function delete_many( array $ids, $force = FALSE, $affected_rows = FALSE )
	{
		$return = FALSE;

		foreach ( $ids as $id )
		{
			$this->delete( $id, $force );
		}

		if ( $this->db->affected_rows() > 0 )
		{
			if ( $affected_rows === TRUE )
			{
				$return = $affected_rows === TRUE ? $this->db->affected_rows() : TRUE;
			}

			if ( $this->db->field_exists( 'record_left', $this->table ) )
			{
				$this->rebuild_tree( $this->table );
			}
		}

		return $return;
	}

	protected function delete_many_by( array $ids, $conditions = array(), $force = FALSE, $affected_rows = FALSE )
	{
		$return = FALSE;

		foreach ( $ids as $id )
		{
			$this->delete_by( $id, $conditions, $force );
		}

		if ( $this->db->affected_rows() > 0 )
		{
			if ( $affected_rows === TRUE )
			{
				$return = $affected_rows === TRUE ? $this->db->affected_rows() : TRUE;
			}

			if ( $this->db->field_exists( 'record_left', $this->table ) )
			{
				$this->rebuild_tree( $this->table );
			}
		}

		return $return;
	}
}