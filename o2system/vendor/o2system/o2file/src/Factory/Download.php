<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 11/10/2015
 * Time: 9:51 PM
 */

namespace O2System\File\Factory;


class Download
{
	protected $_filename = NULL;
	protected $_data = FALSE;
	protected $_mime = 'application/octet-stream';
	protected $_speed = 0;
	protected $_partial_enabled = FALSE;

	// ------------------------------------------------------------------------

	public function __construct($filename = NULL)
	{
		if(isset($filename))
		{
			$this->set_file($filename);
		}
	}

	// ------------------------------------------------------------------------

	public function set_file($filename)
	{
		$this->_filename = $filename;
	}

	// ------------------------------------------------------------------------

	public function set_data($data)
	{
		$this->_data = $data;
	}

	// ------------------------------------------------------------------------

	public function partial()
	{
		$this->_partial_enabled = TRUE;
	}

	// ------------------------------------------------------------------------

	public function set_mime($mime)
	{

	}

	// ------------------------------------------------------------------------

	public function set_speed($speed)
	{

	}

	// ------------------------------------------------------------------------

	public function execute()
	{
		/* It was reported that browsers on Android 2.1 (and possibly older as well)
         * need to have the filename extension upper-cased in order to be able to
         * download it.
         *
         * Reference: http://digiblog.de/2011/04/19/android-and-the-download-file-headers/
         */
		if( isset($this->_filename) AND
			isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) AND
			preg_match( '/Android\s(1|2\.[01])/', $_SERVER[ 'HTTP_USER_AGENT' ] ) )
		{
			$x[ count( $x ) - 1 ] = strtoupper( $extension );
			$filename = implode( '.', $x );
		}

		if( $this->_data === FALSE AND ( $fp = @fopen( $this->_filename, 'rb' ) ) === FALSE )
		{
			return FALSE;
		}

		// Clean output buffer
		if( ob_get_level() !== 0 && @ob_end_clean() === FALSE )
		{
			@ob_clean();
		}

		// Check for partial download
		if( isset( $_SERVER[ 'HTTP_RANGE' ] ) AND
			$this->_partial_enabled === TRUE
		)
		{
			list ( $a, $range ) = explode( "=", $_SERVER[ 'HTTP_RANGE' ] );
			list ( $fbyte, $lbyte ) = explode( "-", $range );

			if( ! $lbyte )
			{
				$lbyte = $filesize - 1;
			}

			$new_length = $lbyte - $fbyte;

			header( "HTTP/1.1 206 Partial Content", TRUE );
			header( "Content-Length: $new_length", TRUE );
			header( "Content-Range: bytes $fbyte-$lbyte/$filesize", TRUE );
		}
		else
		{
			header( "Content-Length: " . $filesize );
		}

		// Common headers
		header( 'Content-Type: ' . $mime, TRUE );
		header( 'Content-Disposition: attachment; filename="' . pathinfo( $filename, PATHINFO_BASENAME ) . '"', TRUE );

		$expires = 604800; // (60*60*24*7)
		header( 'Expires:' . gmdate( 'D, d M Y H:i:s', time() + $expires ) . ' GMT' );

		header( 'Accept-Ranges: bytes', TRUE );
		header( "Cache-control: private", TRUE );
		header( 'Pragma: private', TRUE );

		// Open file
		if( $data === FALSE )
		{
			$file = fopen( $filename, 'r' );
			if( ! $file )
			{
				return FALSE;
			}
		}

		// Cut data for partial download
		if( isset( $_SERVER[ 'HTTP_RANGE' ] ) AND
			$this->_partial_enabled === TRUE
		)
		{
			if( $this->_data === FALSE )
			{
				fseek( $file, $range );
			}
			else
			{
				$data = substr( $data, $range );
			}
		}

		// Disable script time limit
		@set_time_limit( 0 );

		// Check for speed limit or file optimize
		if( $this->_speed > 0 OR $this->_data === FALSE )
		{
			if( $this->_data === FALSE )
			{
				$chunk_size = $this->_speed > 0 ?$this->_speed * 1024 : 512 * 1024;
				while( ! feof( $file ) and ( connection_status() == 0 ) )
				{
					$buffer = fread( $file, $chunk_size );
					echo $buffer;
					flush();
					if( $this->_speed > 0 )
					{
						sleep( 1 );
					}
				}
				fclose( $file );
			}
			else
			{
				$index = 0;
				$this->_speed *= 1024; //convert to kb
				while( $index < $filesize and ( connection_status() == 0 ) )
				{
					$left = $filesize - $index;
					$buffer_size = min( $left, $this->_speed );
					$buffer = substr( $this->_data, $index, $buffer_size );
					$index += $buffer_size;
					echo $buffer;
					flush();
					sleep( 1 );
				}
			}
		}
		else
		{
			echo $this->_data;
		}
	}
}