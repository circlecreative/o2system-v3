<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/27/2016
 * Time: 2:44 AM
 */

namespace O2System\Bootstrap\Factory;


use O2System\Bootstrap\Interfaces\FactoryInterface;
use O2System\Bootstrap\Interfaces\ItemsInterface;

class Grid extends FactoryInterface
{
	use ItemsInterface;

	protected $_tag        = 'div';
	protected $_attributes = array(
		'class' => [ 'row' ],
	);

	public function build()
	{
		@list( $attr ) = func_get_args();

		if ( isset( $attr ) AND is_array( $attr ) )
		{
			$this->add_attributes( $attr );
		}

		return $this;
	}

	public function render()
	{
		if ( ! empty( $this->_items ) )
		{
			if ( $this->_num_rows > 2 )
			{
				$col_xs = @round( 12 / ( $this->_num_per_rows - 2 ) );
				$col_sm = @round( 12 / ( $this->_num_per_rows - 1 ) );
			}

			$col_md = round( 12 / ( $this->_num_per_rows ) );
			$col_lg = round( 12 / ( $this->_num_per_rows ) );

			$container = new Tag( 'div', [ 'class' => 'row' ] );

			foreach ( $this->_items as $key => $item )
			{
				$column = new Tag( 'div', $item, [ 'class' => 'grid-item' ] );

				if ( isset( $col_xs ) )
				{
					$column->add_class( 'col-xs-' . $col_xs );
				}

				if ( isset( $col_sm ) )
				{
					$column->add_class( 'col-sm-' . $col_sm );
				}

				$column->add_class( 'col-md-' . $col_md );
				$column->add_class( 'col-lg-' . $col_lg );

				$this->_items[ $key ] = $column;
			}

			$container->set_content( implode( PHP_EOL, $this->_items ) );

			return ( new Tag( $this->_tag, $container, $this->_attributes ) )->render();
		}

		return '';
	}
}