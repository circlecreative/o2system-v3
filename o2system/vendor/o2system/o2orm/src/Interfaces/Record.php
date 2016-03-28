<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12/3/2015
 * Time: 9:32 AM
 */

namespace O2System\ORM\Interfaces;


trait Record
{
	/**
	 * Unix Timestamp Flag
	 *
	 * @access  protected
	 * @type    bool
	 */
	protected $_unix_timestamp = FALSE;

	/**
	 * Default Record Status
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $_record_status = 'PUBLISH';

	/**
	 * Process Record Fields
	 *
	 * @access  protected
	 */
	protected function _before_process_record( $row )
	{
		$timestamp = $this->_unix_timestamp === TRUE ? strtotime( date( 'Y-m-d H:i:s' ) ) : date( 'Y-m-d H:i:s' );

		if ( ! isset( $row[ 'record_status' ] ) )
		{
			$row[ 'record_status' ] = $this->_record_status;
		}

		if ( empty( $row[ $this->primary_key ] ) )
		{
			if ( isset( $this->access->user ) AND $this->access->user->is_login() )
			{
				if ( ! isset( $row[ 'record_create_user' ] ) )
				{
					$row[ 'record_create_user' ] = $this->access->user->account()->id;
				}
			}

			if ( ! isset( $row[ 'record_create_timestamp' ] ) )
			{
				$row[ 'record_create_timestamp' ] = $timestamp;
			}
		}

		if ( isset( $this->access->user ) AND $this->access->user->is_login() )
		{
			$row[ 'record_update_user' ] = @$this->access->user->account()->id;
		}

		if ( ! isset( $row[ 'record_update_timestamp' ] ) )
		{
			$row[ 'record_update_timestamp' ] = $timestamp;
		}

		return $row;
	}

	/**
	 * Trash Data
	 *
	 * Method to mark as trash data
	 *
	 * @access  public
	 * @final   This method cannot be overwritten
	 *
	 * @param   int    $id    Table Row ID
	 * @param   string $table Table Name
	 *
	 * @param   bool   $force Force Delete
	 *
	 * @return bool
	 */
	final public function trash( $id, $table = NULL )
	{
		$table = isset( $table ) ? $table : $this->table;

		return $this->update( [ 'id' => $id, 'record_status' => 'TRASH' ], $table );
	}
	// ------------------------------------------------------------------------
}