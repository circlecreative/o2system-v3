<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/13/2016
 * Time: 3:06 PM
 */

namespace O2System\Template\Drivers;

use Countable;
use ArrayAccess;
use IteratorAggregate;
use O2System\Glob\Interfaces\DriverInterface;
use Sunra\PhpSimple\HtmlDomParser;
use Traversable;

class Partials extends DriverInterface implements IteratorAggregate, Countable, ArrayAccess
{
	private $storage = array();

	public function __set( $index, $content )
	{
		$this->offsetSet( $index, $content );
	}

	public function __get( $offset )
	{
		return $this->offsetGet( $offset );
	}

	public function set_partials( array $partials )
	{
		$this->storage = $partials;
	}

	public function add_partials( array $partials )
	{
		foreach ( $partials as $partial => $content )
		{
			$this->add_partial( $partial, $content );
		}
	}

	public function add_partial( $index, $content )
	{
		$this->offsetSet( $index, $content );
	}

	/**
	 * Whether a offset exists
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset <p>
	 *                      An offset to check for.
	 *                      </p>
	 *
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	public function offsetExists( $offset )
	{
		return (bool) isset( $this->storage[ $offset ] );
	}

	/**
	 * Offset to retrieve
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to retrieve.
	 *                      </p>
	 *
	 * @return mixed Can return all value types.
	 * @since 5.0.0
	 */
	public function offsetGet( $offset )
	{
		if ( $this->offsetExists( $offset ) )
		{
			$partial = $this->storage[ $offset ];
			$vars = $this->_library->_cached_vars;
			$vars[ 'partials' ] = $this;

			$partial = $this->_library->view->load( $partial, $vars, TRUE );

			if ( ! empty( $partial ) )
			{
				$HTMLDom = HtmlDomParser::str_get_html( $partial );

				if ( is_object( $HTMLDom ) )
				{
					$scripts = $HTMLDom->find( 'script' );

					if ( count( $scripts ) > 0 )
					{
						foreach ( $scripts as $script )
						{
							$this->_library->assets->add_js( $script->innertext, 'inline' );
						}

						$partial = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $partial );
					}
				}

				$HTMLDom = HtmlDomParser::str_get_html( $partial );

				if ( is_object( $HTMLDom ) )
				{
					$styles = $HTMLDom->find( 'style' );

					if ( count( $styles ) > 0 )
					{
						foreach ( $styles as $style )
						{
							$this->_library->assets->add_css( $style->innertext, 'inline' );
						}

						$partial = preg_replace( '#<style(.*?)>(.*?)</style>#is', '', $partial );
					}
				}
			}

			return $partial;
		}

		return '';
	}

	/**
	 * Offset to set
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to assign the value to.
	 *                      </p>
	 * @param mixed $value  <p>
	 *                      The value to set.
	 *                      </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetSet( $offset, $value )
	{
		$this->storage[ $offset ] = $value;
	}

	/**
	 * Offset to unset
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to unset.
	 *                      </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset( $offset )
	{
		unset( $this->storage[ $offset ] );
	}

	/**
	 * Count elements of an object
	 *
	 * @link  http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 *        </p>
	 *        <p>
	 *        The return value is cast to an integer.
	 * @since 5.1.0
	 */
	public function count()
	{
		return count( $this->storage );
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 *        <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator()
	{
		return new \ArrayIterator( $this->storage );
	}
}