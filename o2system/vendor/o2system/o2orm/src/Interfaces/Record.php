<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12/3/2015
 * Time: 9:32 AM
 */

namespace O2System\ORM\Interfaces;


use O2System\ORM\Exception;

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
	 * Default Record User
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $_record_user = NULL;

	protected function _setRecordUser( $id_user )
	{
		if ( is_numeric( $id_user ) )
		{
			$this->_record_user = $id_user;
		}

		return $this;
	}

	protected function _setRecordStatus( $status )
	{
		$status = strtoupper( $status );

		if ( in_array( $status, [ 'UNPUBLISH', 'PUBLISH', 'DRAFT', 'DELETE', 'ARCHIVE' ] ) )
		{
			$this->_record_status = $status;
		}

		return $this;
	}

	/**
	 * Process Record Fields
	 *
	 * @access  protected
	 */
	protected function _beforeProcessRecord( array $row )
	{
		if ( is_null( $this->_record_user ) )
		{
			if ( class_exists( 'O2System', FALSE ) )
			{
				if ( \O2System::$active->offsetExists( 'account' ) )
				{
					$this->_record_user = isset( \O2System::$active[ 'account' ]->id_user_account ) ? \O2System::$active[ 'account' ]->id_user_account : \O2System::$active[ 'account' ]->id;
				}

				if ( is_null( $this->_record_user ) )
				{
					throw new Exception( 'Undefined Record User' );
				}
			}
			else
			{
				throw new Exception( 'Undefined Record User' );
			}
		}

		$timestamp = $this->_unix_timestamp === TRUE ? strtotime( date( 'Y-m-d H:i:s' ) ) : date( 'Y-m-d H:i:s' );

		if ( ! isset( $row[ 'record_status' ] ) )
		{
			$row[ 'record_status' ] = $this->_record_status;
		}

		if ( isset( $this->primary_key ) )
		{
			if ( empty( $row[ $this->primary_key ] ) )
			{
				if ( ! isset( $row[ 'record_create_user' ] ) )
				{
					$row[ 'record_create_user' ] = $this->_record_user;
				}

				if ( ! isset( $row[ 'record_create_timestamp' ] ) )
				{
					$row[ 'record_create_timestamp' ] = $timestamp;
				}
			}
		}

		$row[ 'record_update_user' ] = $this->_record_user;

		if ( ! isset( $row[ 'record_update_timestamp' ] ) )
		{
			$row[ 'record_update_timestamp' ] = $timestamp;
		}

		if ( $row[ 'record_status' ] === 'DELETE' )
		{
			$row[ 'record_delete_user' ] = $this->_record_user;
			$row[ 'record_delete_timestamp' ] = $timestamp;
		}
		else
		{
			$row[ 'record_delete_user' ] = NULL;
			$row[ 'record_delete_timestamp' ] = NULL;
		}

		return $row;
	}
}