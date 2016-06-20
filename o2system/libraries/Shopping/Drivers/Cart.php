<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, .
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, .
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Libraries\Shopping\Drivers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use IteratorAggregate;
use Countable;
use ArrayAccess;
use O2System\Glob\ArrayObject;
use O2System\Glob\Interfaces\DriverInterface;
use O2System\Libraries\Shopping\Metadata\Item;
use Traversable;

/**
 * Shopping Cart Driver Class
 *
 * @package        O2System
 * @subpackage     libraries/Access/drivers
 * @category       Driver Class
 * @author         Steeven Andrian Salim
 * @link           http://o2system.center/products/o2system-ci3.0.0/user-guide/libraries/shopping/cart.html
 */
class Cart extends DriverInterface implements IteratorAggregate, ArrayAccess, Countable
{
	public function destroy()
	{
		unset( $_SESSION[ 'shopping_cart' ] );
	}

	public function isEmpty()
	{
		return (bool) ( $this->count() == 0 ? TRUE : FALSE );
	}

	public function addItems(array $items )
	{
		foreach ( $items as $item )
		{
			$this->addItem( $item );
		}
	}

	public function addItem(ArrayObject $item )
	{
		if ( $item instanceof ArrayObject )
		{
			if ( $item->offsetExists( 'id' ) === FALSE )
			{
				throw new \Exception( 'The Cart Item requires unique ID' );
			}

			if ( $item->offsetExists( 'price' ) === FALSE )
			{
				throw new \Exception( 'The Cart Item requires price' );
			}

			if ( isset( $_SESSION[ 'shopping_cart' ][ $item->id ] ) )
			{
				return $this->updateItem( $item->id, $_SESSION[ 'shopping_cart' ][ $item->id ][ 'quantity' ] + 1 );
			}
			else
			{
				$item[ 'quantity' ] = 1;
				$item[ 'price' ] = new ArrayObject( array(
					                                    'amount'        => $item[ 'price' ]->amount,
					                                    'currency'      => $currency = ( isset( $item[ 'price' ]->currency ) ? $item[ 'price' ]->currency : \O2System::$config[ 'currency' ] ),
					                                    'with_currency' => number_price( $item[ 'price' ]->amount, $currency, TRUE ),
				                                    ) );

				$item[ 'sub_total' ] = new ArrayObject( array(
					                                        'price' => new ArrayObject( array(
						                                                                    'amount'        => $amount = ( $item[ 'discount' ]->is_discounted !== FALSE ? $item[ 'quantity' ] * $item[ 'discount' ]->price->amount : $item[ 'quantity' ] * $item[ 'price' ]->amount ),
						                                                                    'currency'      => $currency = ( isset( $item->price[ 'currency' ] ) ? $item->price[ 'currency' ] : \O2System::$config[ 'currency' ] ),
						                                                                    'with_currency' => number_price( $amount, $currency, TRUE ),
					                                                                    ) ),

					                                        'weight' => new ArrayObject( array(
						                                                                     'amount'    => $amount = $item[ 'quantity' ] * $item[ 'specification' ]->weight->amount,
						                                                                     'unit'      => $unit = ( isset( $item[ 'specification' ]->weight->unit ) ? $item[ 'specification' ]->weight->unit : \O2System::$config[ 'weight' ] ),
						                                                                     'with_unit' => number_weight( $amount, $unit, TRUE ),
					                                                                     ) ),
				                                        ) );

				$_SESSION[ 'shopping_cart' ][ $item->id ] = $item;

				return TRUE;
			}
		}

		return FALSE;
	}

	public function updateItem($id, $quantity )
	{
		if ( $this->offsetExists( $id ) )
		{
			// Delete or update accordingly:
			if ( $quantity === 0 )
			{
				$this->deleteItem( $id );
			}
			else
			{
				$_SESSION[ 'shopping_cart' ][ $id ][ 'quantity' ] = $quantity;

				$_SESSION[ 'shopping_cart' ][ $id ][ 'sub_total' ] = new ArrayObject( array(
					                                                                      'price' => new ArrayObject( array(
						                                                                                                  'amount'        => $amount = ( $_SESSION[ 'shopping_cart' ][ $id ][ 'discount' ]->is_discounted !== FALSE  ? $quantity * $_SESSION[ 'shopping_cart' ][ $id ][ 'discount' ]->price->amount : $quantity * $_SESSION[ 'shopping_cart' ][ $id ]->price[ 'amount' ] ),
						                                                                                                  'currency'      => $currency = ( isset( $_SESSION[ 'shopping_cart' ][ $id ]->price[ 'currency' ] ) ? $_SESSION[ 'shopping_cart' ][ $id ]->price[ 'currency' ] : \O2System::$config[ 'currency' ] ),
						                                                                                                  'with_currency' => number_price( $amount, $currency, TRUE ),
					                                                                                                  ) ),

					                                                                      'weight' => new ArrayObject( array(
						                                                                                                   'amount'    => $amount = $_SESSION[ 'shopping_cart' ][ $id ][ 'quantity' ] * $_SESSION[ 'shopping_cart' ][ $id ][ 'specification' ]->weight->amount,
						                                                                                                   'unit'      => $unit = ( isset( $_SESSION[ 'shopping_cart' ][ $id ][ 'specification' ]->weight->unit ) ? $_SESSION[ 'shopping_cart' ][ $id ][ 'specification' ]->weight->unit : \O2System::$config[ 'weight' ] ),
						                                                                                                   'with_unit' => number_weight( $amount, $unit, TRUE ),
					                                                                                                   ) ),
				                                                                      ) );
			}

			return TRUE;
		}

		return FALSE;
	} // End of updateItem() method.

	// Removes an item from the cart:
	public function deleteItem($id )
	{
		// Remove it:
		if ( $this->offsetExists( $id ) )
		{
			unset( $_SESSION[ 'shopping_cart' ][ $id ] );

			return TRUE;
		}

		return FALSE;
	}

	public function getTotalPrice($return = 'amount' )
	{
		$total_price = 0;

		if ( ! $this->isEmpty() )
		{
			foreach ( $_SESSION[ 'shopping_cart' ] as $item )
			{
				$total_price = $total_price + $item[ 'sub_total' ]->price[ 'amount' ];
			}
		}

		$total = new ArrayObject( array(
			                          'amount'        => $total_price,
			                          'currency'      => $currency = ( isset( $item->price[ 'currency' ] ) ? $item->price[ 'currency' ] : \O2System::$config[ 'currency' ] ),
			                          'with_currency' => number_price( $total_price, $currency, TRUE ),
		                          ) );

		return $total->offsetGet( $return );
	}

	public function getTotalWeight($return = 'amount' )
	{
		$total_weight = 0;

		if ( ! $this->isEmpty() )
		{
			foreach ( $_SESSION[ 'shopping_cart' ] as $item )
			{
				$total_weight = $total_weight + $item[ 'sub_total' ]->weight[ 'amount' ];
			}
		}

		$total = new ArrayObject( array(
			                          'amount'    => $total_weight,
			                          'unit'      => $unit = ( isset( $item[ 'specification' ]->weight->unit ) ? $item[ 'specification' ]->weight->unit : \O2System::$config[ 'weight' ] ),
			                          'with_unit' => number_weight( $total_weight, $unit, TRUE ),
		                          ) );

		return $total->offsetGet( $return );
	}


	// Required by Countable:
	public function count()
	{
		return isset( $_SESSION[ 'shopping_cart' ] ) ? count( $_SESSION[ 'shopping_cart' ] ) : 0;
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
		return $this->isEmpty() ? new \ArrayIterator( [ ] ) : new \ArrayIterator( $_SESSION[ 'shopping_cart' ] );
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
		return (bool) isset( $_SESSION[ 'shopping_cart' ][ $offset ] );
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
		$_SESSION[ 'shopping_cart' ][ $offset ];
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
		$_SESSION[ 'shopping_cart' ][ $offset ] = $value;
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
		unset( $_SESSION[ 'shopping_cart' ][ $offset ] );
	}
}