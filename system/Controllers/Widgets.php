<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/29/2016
 * Time: 1:55 PM
 */

namespace O2System\Controllers;


use O2System\Bootstrap\Components\Panel;
use O2System\Core\Controller;
use O2System\Registry\Metadata\Widget;

abstract class Widgets extends Controller
{
	public $metadata;
	public $header;
	public $body;
	public $footer;

	public function __construct()
	{
		parent::__construct();
	}

	final public function _route()
	{
		redirect( 'error/403' );
	}

	public function setTitle( $title )
	{
		return $this->setHeader( $title );
	}

	public function setHeader( $header )
	{
		$this->header = $header;

		return $this;
	}

	public function setContent( $content )
	{
		return $this->setBody( $content );
	}

	public function setBody( $body )
	{
		$this->body = $body;

		return $this;
	}

	public function setFooter( $footer )
	{
		$this->footer = $footer;

		return $this;
	}

	public function __get( $property )
	{
		if ( in_array( $property, [ 'header', 'title', 'body', 'content' ] ) )
		{
			switch ( $property )
			{
				case 'header':
				case 'title':

					if ( empty( $this->header ) )
					{
						if ( $this->metadata->offsetExists( 'title' ) )
						{
							$this->header = ucwords( $this->metadata[ 'title' ] );
						}
						elseif ( $this->metadata->offsetExists( 'name' ) )
						{
							$this->header = ucwords( $this->metadata[ 'name' ] );
						}
					}

					return $this->header;

					break;

				case 'body':
				case 'content':

					if ( empty( $this->body ) )
					{
						$this->body = $this->__loadBody();
					}

					return $this->body;

					break;
			}
		}
		elseif ( property_exists( $this, $property ) )
		{
			return $this->{$property};
		}
		elseif ( $this->metadata instanceof Widget AND
			$this->metadata->offsetExists( $property )
		)
		{
			return $this->metadata->offsetGet( $property );
		}

		return parent::__get( $property );
	}

	private function __loadBody()
	{
		$this->view->addPath( ROOTPATH . $this->metadata[ 'realpath' ] );

		return $this->view->load( $this->metadata->parameter, [ ], TRUE );
	}

	public function render()
	{
		$content = $this->__get( 'content' );
		$content = trim( $content );

		if ( $content === '' OR is_null( $content ) )
		{
			return '';
		}

		$panel = new Panel( Panel::DEFAULT_PANEL );
		$panel->setTitle( '<span>' . $this->__get( 'title' ) . '</span>', 'h5' );
		$panel->setContent( $content );

		$footer = $this->__get( 'footer' );
		$footer = trim( $footer );

		if ( $footer !== '' OR ! is_null( $footer ) )
		{
			$panel->setFooter( $footer );
		}

		return $panel->render();
	}

	public function __toString()
	{
		return $this->render();
	}
}