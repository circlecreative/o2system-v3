<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/14/2016
 * Time: 11:37 PM
 */

namespace O2System\Template\Collections;

use O2System\Bootstrap\Factory\Tag;
use O2System\Bootstrap\Factory\Button;
use O2System\Glob\ArrayObject;

class Buttons extends ArrayObject
{
	public function __construct( array $buttons = array() )
	{
		if ( ! empty( $buttons ) )
		{
			foreach ( $buttons as $key => $button )
			{
				if ( class_exists( 'O2System', FALSE ) )
				{
					$label = \O2System::$language->line( $button[ 'label' ] );
				}

				if ( empty( $label ) )
				{
					$label = str_replace( [ 'BTN', '_' ], [ '', ' ' ], $button[ 'label' ] );
					$label = strtolower( $label );
					$label = ucwords( $label );
				}

				$contextual = isset( $button[ 'contextual' ] ) ? $button[ 'contextual' ] : 'default';

				if ( isset( $button[ 'type' ] ) )
				{
					switch ( $button[ 'type' ] )
					{
						case 'reset':
						case 'RESET':

							$button[ 'attr' ][ 'type' ] = 'reset';

							break;

						case 'submit':
						case 'SUBMIT':

							$button[ 'attr' ][ 'type' ] = 'reset';

							break;

						default:

							$button[ 'attr' ][ 'type' ] = 'button';

							break;
					}
				}

				$buttons[ $key ] = ( new Button( $label, $contextual, $button[ 'attr' ] ) )->set_icon( @$button[ 'icon' ] );
			}
		}

		parent::__construct( $buttons );
	}

	public function render()
	{
		if ( $this->__isEmpty() === FALSE )
		{
			return implode( PHP_EOL, $this->getArrayCopy() );
		}

		return '';
	}

	public function __toString()
	{
		return $this->render();
	}
}