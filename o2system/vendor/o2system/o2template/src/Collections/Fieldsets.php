<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/14/2016
 * Time: 11:37 PM
 */

namespace O2System\Template\Collections;

use O2System\Bootstrap\Factory\Tag;
use O2System\Bootstrap\Factory\Fieldset;
use O2System\Glob\ArrayObject;

class Fieldsets extends ArrayObject
{
	public function __construct( array $fieldsets = array() )
	{
		if ( ! empty( $fieldsets ) )
		{
			foreach ( $fieldsets as $group => $fieldset )
			{
				foreach ( $fieldset as $legend => $set )
				{
					$fieldsets[ $group ][] = ( new Fieldset( Fieldset::PANEL_FIELDSET ) )
						->set_legend( $legend )
						->set_group_type( @$set[ 'type' ] )
						->add_attributes( $set[ 'attr' ] )
						->add_items( $set[ 'fields' ] );

					unset( $fieldsets[ $group ][ $legend ] );
				}
			}
		}

		parent::__construct( $fieldsets );
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