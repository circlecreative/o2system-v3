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
	protected $isUnixTimestamp = FALSE;

	/**
	 * Default Record Status
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $recordStatus = 'PUBLISH';

	/**
	 * Default Record User
	 *
	 * @access  protected
	 * @type    int
	 */
	protected $recordUser = NULL;

	protected function _setRecordUser( $id_user )
	{
		if ( is_numeric( $id_user ) )
		{
			$this->recordUser = $id_user;
		}

		return $this;
	}

	protected function _setRecordStatus( $status )
	{
		$status = strtoupper( $status );

		if ( in_array( $status, [ 'UNPUBLISH', 'PUBLISH', 'DRAFT', 'DELETE', 'ARCHIVE' ] ) )
		{
			$this->recordStatus = $status;
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
		if ( is_null( $this->recordUser ) )
		{
			if ( \O2System::$active->offsetExists( 'account' ) )
			{
				$this->recordUser = isset( \O2System::$active[ 'account' ]->id_user_account ) ? \O2System::$active[ 'account' ]->id_user_account : \O2System::$active[ 'account' ]->id;
			}

			if ( is_null( $this->recordUser ) )
			{
				throw new Exception( 'Undefined Record User' );
			}
		}

		$timestamp = $this->isUnixTimestamp === TRUE ? strtotime( date( 'Y-m-d H:i:s' ) ) : date( 'Y-m-d H:i:s' );

		if ( ! isset( $row[ 'record_status' ] ) )
		{
			$row[ 'record_status' ] = $this->recordStatus;
		}

		if ( empty( $this->primary_keys ) )
		{
			$primary_key = isset( $this->primary_key ) ? $this->primary_key : 'id';

			if ( empty( $row[ $primary_key ] ) )
			{
				if ( ! isset( $row[ 'record_create_user' ] ) )
				{
					$row[ 'record_create_user' ] = $this->recordUser;
				}

				if ( ! isset( $row[ 'record_create_timestamp' ] ) )
				{
					$row[ 'record_create_timestamp' ] = $timestamp;
				}
			}
		}
		else
		{
			foreach ( $this->primary_keys as $primary_key )
			{
				if ( empty( $row[ $primary_key ] ) )
				{
					if ( ! isset( $row[ 'record_create_user' ] ) )
					{
						$row[ 'record_create_user' ] = $this->recordUser;
					}

					if ( ! isset( $row[ 'record_create_timestamp' ] ) )
					{
						$row[ 'record_create_timestamp' ] = $timestamp;
					}
				}
			}
		}

		$row[ 'record_update_user' ] = $this->recordUser;

		if ( ! isset( $row[ 'record_update_timestamp' ] ) )
		{
			$row[ 'record_update_timestamp' ] = $timestamp;
		}

		if ( $row[ 'record_status' ] === 'DELETE' )
		{
			$row[ 'record_delete_user' ]      = $this->recordUser;
			$row[ 'record_delete_timestamp' ] = $timestamp;
		}
		else
		{
			$row[ 'record_delete_user' ]      = 0;
			$row[ 'record_delete_timestamp' ] = '0000-00-00 00:00:00';
		}

		return $row;
	}
}