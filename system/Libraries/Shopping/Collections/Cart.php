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

namespace O2System\Libraries\Shopping\Collections;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core\Collectors\Errors;
use O2System\Core\Library\Collections;
use O2System\Core\SPL\ArrayAccess;
use O2System\Core\SPL\ArrayObject;
use Api\Metadata\Money;

/**
 * Shopping Cart Driver Class
 *
 * @package        O2System
 * @subpackage     libraries/Access/drivers
 * @category       Driver Class
 * @author         Steeven Andrian Salim
 * @link           http://o2system.center/products/o2system-ci3.0.0/user-guide/libraries/shopping/cart.html
 */
class Cart extends ArrayAccess
{
	public function __construct()
	{
		$this->storage =& $_SESSION[ 'shoppingCart' ];
	}

	public function destroy()
	{
		$this->clearStorage();
	}

	public function isEmpty()
	{
		return (bool) ( $this->count() == 0 ? TRUE : FALSE );
	}

	public function addItems( array $items )
	{
		foreach ( $items as $item )
		{
			$this->addItem( $item );
		}
	}

	public function addItem( ArrayObject $item )
	{
		print_out($item);
		if ( $item instanceof ArrayObject )
		{
			if ( $item->offsetExists( 'id' ) === FALSE )
			{
				$this->throwError( 'E_SHOPPING_CART_ITEM_ID', 12101 );
			}

			if ( $item->offsetExists( 'price' ) === FALSE )
			{
				$this->throwError( 'E_SHOPPING_CART_ITEM_PRICE', 12102 );
			}

			if ( isset( $this->storage[ $item->id ] ) )
			{
				return $this->updateItem( $item->id, $this->storage[ $item->id ][ 'quantity' ] + 1 );
			}
			else
			{
				$item[ 'quantity' ] = 1;
				$item[ 'price' ]    = new ArrayObject(
					[
						'amount'        => $item[ 'price' ]->amount,
						'currency'      => $currency = ( isset( $item[ 'price' ]->currency ) ? $item[ 'price' ]->currency : \O2System::$config[ 'currency' ] ),
						'with_currency' => currency_format( $item[ 'price' ]->amount, $currency, TRUE ),
					] );

				$item[ 'sub_total' ] = new ArrayObject(
					[
						'price' => new ArrayObject(
							[
								'amount'        => $amount = ( $item->attributes->is_discounted !== FALSE ? $item[ 'quantity' ] * $item[ 'discount' ]->price->amount : $item[ 'quantity' ] * $item[ 'price' ]->amount ),
								'currency'      => $currency = ( isset( $item->price[ 'currency' ] ) ? $item->price[ 'currency' ] : \O2System::$config[ 'currency' ] ),
								'with_currency' => currency_format( $amount, $currency, TRUE ),
							] ),

						'weight' => new ArrayObject(
							[
								'amount'    => $amount = $item[ 'quantity' ] * $item[ 'specification' ]->weight->amount,
								'unit'      => $unit = ( isset( $item[ 'specification' ]->weight->unit ) ? $item[ 'specification' ]->weight->unit : \O2System::$config[ 'weight' ] ),
								'with_unit' => unit_format( $amount, $unit, TRUE ),
							] ),
					] );

				$this->storage[ $item->id ] = $item;

				return TRUE;
			}
		}

		return FALSE;
	}

	public function updateItem( $id, $quantity )
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
				$this->storage[ $id ][ 'quantity' ] = $quantity;

				$this->storage[ $id ][ 'sub_total' ] = new ArrayObject(
					[
						'price' => new ArrayObject(
							[
								'amount'        => $amount = ( $this->storage[ $id ]->attributes->is_discounted !== FALSE ? $quantity * $this->storage[ $id ][ 'discount' ]->price->amount : $quantity * $this->storage[ $id ]->price[ 'amount' ] ),
								'currency'      => $currency = ( isset( $this->storage[ $id ]->price[ 'currency' ] ) ? $this->storage[ $id ]->price[ 'currency' ] : \O2System::$config[ 'currency' ] ),
								'with_currency' => currency_format( $amount, $currency, TRUE ),
							] ),

						'weight' => new ArrayObject(
							[
								'amount'    => $amount = $this->storage[ $id ][ 'quantity' ] * $this->storage[ $id ][ 'specification' ]->weight->amount,
								'unit'      => $unit = ( isset( $this->storage[ $id ][ 'specification' ]->weight->unit ) ? $this->storage[ $id ][ 'specification' ]->weight->unit : \O2System::$config[ 'weight' ] ),
								'with_unit' => unit_format( $amount, $unit, TRUE ),
							] ),
					] );
			}

			return TRUE;
		}

		return FALSE;
	} // End of updateItem() method.

	// Removes an item from the cart:
	public function deleteItem( $id )
	{
		// Remove it:
		if ( $this->offsetExists( $id ) )
		{
			unset( $this->storage[ $id ] );

			return TRUE;
		}

		return FALSE;
	}

	public function getTotalPrice( $return = 'amount' )
	{
		$total_price = 0;

		if ( ! $this->isEmpty() )
		{
			foreach ( $this->storage as $item )
			{
				$total_price = $total_price + $item[ 'sub_total' ]->price[ 'amount' ];
			}
		}

		$total = new ArrayObject(
			[
				'amount'        => $total_price,
				'currency'      => $currency = ( isset( $item->price[ 'currency' ] ) ? $item->price[ 'currency' ] : \O2System::$config[ 'currency' ] ),
				'with_currency' => currency_format( $total_price, $currency, TRUE ),
			] );

		return $total->offsetGet( $return );
	}

	public function getTotalWeight( $return = 'amount' )
	{
		$total_weight = 0;

		if ( ! $this->isEmpty() )
		{
			foreach ( $this->storage as $item )
			{
				$total_weight = $total_weight + $item[ 'sub_total' ]->weight[ 'amount' ];
			}
		}

		$total = new ArrayObject(
			[
				'amount'    => $total_weight,
				'unit'      => $unit = ( isset( $item[ 'specification' ]->weight->unit ) ? $item[ 'specification' ]->weight->unit : \O2System::$config[ 'weight' ] ),
				'with_unit' => unit_format( $total_weight, $unit, TRUE ),
			] );

		return $total->offsetGet( $return );
	}
}