<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 3/7/2016
 * Time: 4:35 PM
 */

namespace O2System\Libraries\CRUD;


use O2System\Bootstrap\Components\Fieldset;
use O2System\Bootstrap\Components\Group;
use O2System\Bootstrap\Components\Panel;
use O2System\Bootstrap\Components\Tag;
use O2System\Core\SPL\ArrayObject;

class Form
{
	const DEFAULT_FORM    = 'FORM_GROUP';
	const VERTICAL_FORM   = 'FORM_GROUP_VERTICAL';
	const HORIZONTAL_FORM = 'FORM_GROUP_HORIZONTAL';
	const INLINE_FORM     = 'FORM_GROUP_INLINE';

	protected $_config = [
		'primary_key' => 'id',
		'buttons'     => [
			'insert' => [
				'cancel' => TRUE,
				'reset'  => TRUE,
				'save'   => TRUE,
			],
			'update' => [
				'cancel' => TRUE,
				'reset'  => TRUE,
				'delete' => TRUE,
				'update' => TRUE,
				'as-new' => TRUE,
			],
		],
	];

	protected $_attributes = [
		'class' => 'form-horizontal',
	];

	protected $_insert_buttons = [
		'cancel ' => [
			'label'      => 'BTN_CANCEL',
			'type'       => 'CANCEL',
			'contextual' => 'default',
			'icon'       => 'fa-undo',
			'attr'       => [
				'type'        => 'button',
				'name'        => 'form-cancel',
				'class'       => 'btn-cancel',
				'data-action' => 'cancel',
			],
		],
		'reset'   => [
			'label'      => 'BTN_RESET',
			'type'       => 'RESET',
			'contextual' => 'danger',
			'icon'       => 'fa-refresh',
			'attr'       => [
				'type'        => 'reset',
				'name'        => 'form-reset',
				'class'       => 'btn-reset',
				'data-action' => 'reset',
			],
		],
		'save'    => [
			'label'      => 'BTN_SAVE',
			'type'       => 'SUBMIT',
			'contextual' => 'primary',
			'icon'       => 'fa-floppy-o',
			'attr'       => [
				'type'        => 'submit',
				'name'        => 'form-save',
				'class'       => 'btn-save',
				'data-action' => 'save',
			],
		],
	];

	protected $_update_buttons = [
		'cancel ' => [
			'label'      => 'BTN_CANCEL',
			'type'       => 'CANCEL',
			'contextual' => 'default',
			'icon'       => 'fa-undo',
			'attr'       => [
				'type'        => 'button',
				'name'        => 'form-cancel',
				'class'       => 'btn-cancel',
				'data-action' => 'cancel',
			],
		],
		'reset'   => [
			'label'      => 'BTN_RESET',
			'type'       => 'RESET',
			'contextual' => 'warning',
			'icon'       => 'fa-refresh',
			'attr'       => [
				'type'        => 'reset',
				'name'        => 'form-reset',
				'class'       => 'btn-reset',
				'data-action' => 'reset',
			],
		],
		'delete'  => [
			'label'      => 'BTN_DELETE',
			'type'       => 'DELETE',
			'contextual' => 'danger',
			'icon'       => 'fa-trash-o',
			'attr'       => [
				'type'        => 'submit',
				'name'        => 'form-delete',
				'class'       => 'btn-delete',
				'data-action' => 'delete',
			],
		],
		'update'  => [
			'label'      => 'BTN_UPDATE',
			'type'       => 'SUBMIT',
			'contextual' => 'primary',
			'icon'       => 'fa-floppy-o',
			'attr'       => [
				'type'        => 'submit',
				'name'        => 'form-update',
				'class'       => 'btn-update',
				'data-action' => 'update',
			],
		],
		'as-new'  => [
			'label'      => 'BTN_SAVE_AS_NEW',
			'type'       => 'SUBMIT',
			'contextual' => 'success',
			'icon'       => 'fa-floppy-o',
			'attr'       => [
				'type'        => 'submit',
				'name'        => 'form-save-as-new',
				'class'       => 'btn-as-new',
				'data-action' => 'save-as-new',
			],
		],
	];

	protected $_title;
	protected $_fieldsets;
	protected $_fields;
	protected $_hiddens;
	protected $_buttons;

	/**
	 * ArrayObject of Form Data
	 *
	 * @type    ArrayObject
	 */
	protected $_data;

	public function __construct()
	{
		\O2System::$view->assets->addAsset( 'crud-form' );
	}

	public function setData( ArrayObject $data )
	{
		$this->_data = $data;
	}

	public function setConfig( array $config )
	{
		$this->_config = array_merge( $this->_config, $config );
	}

	public function setAttributes( array $attributes )
	{
		$this->_attributes = $attributes;
	}

	public function setTitle( $title )
	{
		$this->_title = $title;
	}

	public function setFieldsets( array $fieldsets, $group = 'main' )
	{
		$this->_fieldsets[ $group ] = $fieldsets;
	}

	public function setFieldset( array $fieldset, $legend = '', $group = 'main' )
	{
		if ( array_key_exists( $group, $this->_fieldsets ) )
		{
			$this->_fieldsets[ $group ][ $legend ] = $fieldset;
		}
	}

	public function setButtons( array $buttons )
	{
		$this->_buttons = $buttons;

		return $this;
	}

	/**
	 * @param array $fields
	 *
	 * @return $this
	 */
	public function setFields( array $fields = [ ] )
	{
		$this->_fieldsets[ 'blank' ][ 'fields' ] = $fields;

		return $this;
	}

	/**
	 * @param array $hiddens
	 */
	public function setHiddens( array $hiddens = [ ] )
	{
		foreach ( $hiddens as $hidden )
		{
			$this->addHidden( $hidden );
		}
	}

	public function addHidden( array $hidden )
	{
		$this->_hiddens[] = $hidden;
	}

	public function render()
	{
		$panel[ 'header' ] = new Panel( Panel::DEFAULT_PANEL );
		$panel[ 'header' ]->setTitle( $this->_title );
		$panel[ 'header' ]->addAttribute( 'data-role', 'form-panel-header' );

		foreach ( $this->_fieldsets as $group => $fieldsets )
		{
			$panel[ $group ] = new Group( Group::PANEL_GROUP );

			foreach ( $fieldsets as $role => $fieldset )
			{
				$attr                = isset( $fieldset[ 'attr' ] ) ? $fieldset[ 'attr' ] : [ ];
				$attr[ 'data-role' ] = $role;

				if ( isset( $fieldset[ 'collapse' ] ) AND $fieldset[ 'collapse' ] === TRUE )
				{
					$attr[ 'data-state' ] = 'collapse';
				}

				if ( isset( $fieldset[ 'type' ] ) )
				{
					$panel[ $group ]->addItem(
						( new Fieldset( $fieldset[ 'legend' ], Fieldset::PANEL_FIELDSET ) )
							->addItems( $fieldset[ 'fields' ] )
							->setAttributes( $attr )
							->setGroupType( $fieldset[ 'type' ] ) );
				}
				else
				{
					$panel[ $group ]->addItem(
						( new Fieldset( $fieldset[ 'legend' ], Fieldset::PANEL_FIELDSET ) )
							->addItems( $fieldset[ 'fields' ] )
							->setAttributes( $attr ) );
				}

			}
		}

		if ( isset( $panel[ 'sidebar' ] ) )
		{
			$panel[ 'main' ]->addClass( 'col-sm-8' );
			$panel[ 'sidebar' ]->addClass( 'col-sm-4' );
		}

		return ( new Tag( 'form', implode( PHP_EOL, $panel ), $this->_attributes ) )->render();
	}

	public function __toString()
	{
		return $this->render();
	}
}