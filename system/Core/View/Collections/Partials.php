<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 04-Aug-16
 * Time: 4:09 PM
 */

namespace O2System\Core\View\Collections;

use O2System\Core\Library\Collections;

class Partials extends Collections
{
	public function add( $name, $partial = NULL )
	{
		if ( is_array( $name ) )
		{
			foreach ( $name as $key => $partial )
			{
				$this->add( $key, $partial );
			}
		}
		else
		{
			\O2System::$view->vars->add( 'navigations', \O2System::$view->navigations );
			\O2System::$view->vars->add( 'partials', \O2System::$view->partials );

			if ( is_file( $partial ) )
			{
				$this->offsetSet( $name, \O2System::$view->parser->parseFile( $partial ) );
			}
			elseif ( FALSE !== ( $filepath = \O2System::$view->getFilePath( $partial ) ) )
			{
				$this->offsetSet( $name, \O2System::$view->parser->parseFile( $filepath ) );
			}
		}
	}
}