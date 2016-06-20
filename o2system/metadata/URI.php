<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 5/9/2016
 * Time: 3:46 PM
 */

namespace O2System\Metadata;


use O2System\Glob\ArrayObject;

class URI extends ArrayObject
{
	public function __construct()
	{
		parent::__construct(
			[
				'segments'  => [ ],
				'string'    => NULL,
				'rsegments' => [ ],
				'rstring'   => NULL,
				'isegments' => [ ],
				'istring'   => NULL,
			] );
	}

	public function setSegments( $segments, $which = 'segments' )
	{
		if ( is_string( $segments ) )
		{
			$this->offsetSet( $which, explode( '/', $segments ) );
		}
		else
		{
			$this->offsetSet( $which, $segments );
		}

		$this->reindexSegments( $which );

		switch ( $which )
		{
			case 'segments':
				$this->setString( $segments, 'string' );
				break;

			case 'rsegments':
				$this->setString( $segments, 'rstring' );
				break;

			case 'isegments':
				$this->setString( $segments, 'istring' );
				break;
		}
	}

	public function setString( $string, $which = 'string' )
	{
		if ( is_array( $string ) )
		{
			$this->offsetSet( $which, implode( '/', $string ) );
		}
		else
		{
			$this->offsetSet( $which, $string );
		}
	}

	/**
	 * Fetch URI Segment
	 *
	 * @param   int   $n         Index
	 * @param   mixed $no_result What to return if the segment index is not found
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function segment( $n, $no_result = NULL, $which = 'segments' )
	{
		$no_result = in_array( $no_result, [ 'segments', 'rsegments', [ 'isegments' ] ] ) ? NULL : $no_result;

		return isset( \O2System::$active[ 'URI' ]->{$which}[ $n ] ) ? \O2System::$active[ 'URI' ]->{$which}[ $n ] : $no_result;
	}

	// ------------------------------------------------------------------------

	public function hasSegment( $segment, $which = 'segments' )
	{
		return (bool) in_array( $segment, \O2System::$active[ 'URI' ]->{$which} );
	}

	public function reindexSegments( $which = 'segments' )
	{
		$segments = \O2System::$active[ 'URI' ]->{$which};

		if ( key( $segments ) == 0 )
		{
			array_unshift( $segments, NULL );
			unset( $segments[ 0 ] );
		}

		\O2System::$active[ 'URI' ]->{$which} = $segments;
	}

	/**
	 * Total number of segments
	 *
	 * @access  public
	 * @return  int
	 */
	public function totalSegments( $which = 'segments' )
	{
		return (int) count( \O2System::$active[ 'URI' ]->{$which} );
	}

	// --------------------------------------------------------------------

	/**
	 * Slash segment
	 *
	 * Fetches an URI segment with a slash.
	 *
	 * @param   int    $n     Index
	 * @param   string $where Where to add the slash ('trailing' or 'leading')
	 *
	 * @access  public
	 * @return  string
	 */
	public function slashSegment( $n, $where = 'trailing', $which = 'segments' )
	{
		if ( in_array( $where, [ 'segments', 'rsegments', 'isegments' ] ) )
		{
			$which = $where;
			$where = 'trailing';
		}

		$leading = $trailing = '/';

		if ( $where === 'trailing' )
		{
			$leading = '';
		}
		elseif ( $where === 'leading' )
		{
			$trailing = '';
		}

		return $leading . $this->segment( $n, $which ) . $trailing;
	}
}