<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 3/7/2016
 * Time: 4:35 PM
 */

namespace O2System\Libraries\CRUD;


use O2System\Bootstrap\Factory\Fieldset;
use O2System\Bootstrap\Factory\Group;
use O2System\Bootstrap\Factory\Panel;
use O2System\Bootstrap\Factory\Tag;
use O2System\Glob\ArrayObject;

class Form
{
	const DEFAULT_FORM    = 'FORM_GROUP';
	const VERTICAL_FORM   = 'FORM_GROUP_VERTICAL';
	const HORIZONTAL_FORM = 'FORM_GROUP_HORIZONTAL';
	const INLINE_FORM     = 'FORM_GROUP_INLINE';

	protected $_config = array(
		'primary_key' => 'id',
		'buttons'     => array(
			'insert' => array(
				'cancel' => TRUE,
				'reset'  => TRUE,
				'save'   => TRUE,
			),
			'update' => array(
				'cancel' => TRUE,
				'reset'  => TRUE,
				'delete' => TRUE,
				'update' => TRUE,
				'as-new' => TRUE,
			),
		),
	);

	protected $_attributes = array(
		'class' => 'form-horizontal',
	);

	protected $_insert_buttons = array(
		'cancel ' => array(
			'label'      => 'BTN_CANCEL',
			'type'       => 'CANCEL',
			'contextual' => 'default',
			'icon'       => 'fa-undo',
			'attr'       => array(
				'type'        => 'button',
				'name'        => 'form-cancel',
				'class'       => 'btn-cancel',
				'data-action' => 'cancel',
			),
		),
		'reset'   => array(
			'label'      => 'BTN_RESET',
			'type'       => 'RESET',
			'contextual' => 'danger',
			'icon'       => 'fa-refresh',
			'attr'       => array(
				'type'        => 'reset',
				'name'        => 'form-reset',
				'class'       => 'btn-reset',
				'data-action' => 'reset',
			),
		),
		'save'    => array(
			'label'      => 'BTN_SAVE',
			'type'       => 'SUBMIT',
			'contextual' => 'primary',
			'icon'       => 'fa-floppy-o',
			'attr'       => array(
				'type'        => 'submit',
				'name'        => 'form-save',
				'class'       => 'btn-save',
				'data-action' => 'save',
			),
		),
	);

	protected $_update_buttons = array(
		'cancel ' => array(
			'label'      => 'BTN_CANCEL',
			'type'       => 'CANCEL',
			'contextual' => 'default',
			'icon'       => 'fa-undo',
			'attr'       => array(
				'type'        => 'button',
				'name'        => 'form-cancel',
				'class'       => 'btn-cancel',
				'data-action' => 'cancel',
			),
		),
		'reset'   => array(
			'label'      => 'BTN_RESET',
			'type'       => 'RESET',
			'contextual' => 'warning',
			'icon'       => 'fa-refresh',
			'attr'       => array(
				'type'        => 'reset',
				'name'        => 'form-reset',
				'class'       => 'btn-reset',
				'data-action' => 'reset',
			),
		),
		'delete'  => array(
			'label'      => 'BTN_DELETE',
			'type'       => 'DELETE',
			'contextual' => 'danger',
			'icon'       => 'fa-trash-o',
			'attr'       => array(
				'type'        => 'submit',
				'name'        => 'form-delete',
				'class'       => 'btn-delete',
				'data-action' => 'delete',
			),
		),
		'update'  => array(
			'label'      => 'BTN_UPDATE',
			'type'       => 'SUBMIT',
			'contextual' => 'primary',
			'icon'       => 'fa-floppy-o',
			'attr'       => array(
				'type'        => 'submit',
				'name'        => 'form-update',
				'class'       => 'btn-update',
				'data-action' => 'update',
			),
		),
		'as-new'  => array(
			'label'      => 'BTN_SAVE_AS_NEW',
			'type'       => 'SUBMIT',
			'contextual' => 'success',
			'icon'       => 'fa-floppy-o',
			'attr'       => array(
				'type'        => 'submit',
				'name'        => 'form-save-as-new',
				'class'       => 'btn-as-new',
				'data-action' => 'save-as-new',
			),
		),
	);

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
		\O2System::View()->assets->add_asset( 'crud-form' );
	}

	public function set_data( ArrayObject $data )
	{
		$this->_data = $data;
	}

	public function set_config( array $config )
	{
		$this->_config = array_merge( $this->_config, $config );
	}

	public function set_attributes( array $attributes )
	{
		$this->_attributes = $attributes;
	}

	public function set_title( $title )
	{
		$this->_title = $title;
	}

	public function set_fieldsets( array $fieldsets, $group = 'main' )
	{
		$this->_fieldsets[ $group ] = $fieldsets;
	}

	public function set_fieldset( array $fieldset, $legend = '', $group = 'main' )
	{
		if ( array_key_exists( $group, $this->_fieldsets ) )
		{
			$this->_fieldsets[ $group ][ $legend ] = $fieldset;
		}
	}

	public function set_buttons( array $buttons )
	{
		$this->_buttons = $buttons;

		return $this;
	}

	/**
	 * @param array $fields
	 *
	 * @return $this
	 */
	public function set_fields( array $fields = array() )
	{
		$this->_fieldsets[ 'blank' ][ 'fields' ] = $fields;

		return $this;
	}

	/**
	 * @param array $hiddens
	 */
	public function set_hiddens( array $hiddens = array() )
	{
		foreach ( $hiddens as $hidden )
		{
			$this->add_hidden( $hidden );
		}
	}

	public function add_hidden( array $hidden )
	{
		$this->_hiddens[] = $hidden;
	}

	public function render()
	{
		$panel[ 'header' ] = new Panel( Panel::DEFAULT_PANEL );
		$panel[ 'header' ]->set_title( $this->_title );
		$panel[ 'header' ]->add_attribute( 'data-role', 'form-panel-header' );

		foreach ( $this->_fieldsets as $group => $fieldsets )
		{
			$panel[ $group ] = new Group( Group::PANEL_GROUP );

			foreach ( $fieldsets as $role => $fieldset )
			{
				$attr = isset( $fieldset[ 'attr' ] ) ? $fieldset[ 'attr' ] : [ ];
				$attr[ 'data-role' ] = $role;

				if ( isset( $fieldset[ 'collapse' ] ) AND $fieldset[ 'collapse' ] === TRUE )
				{
					$attr[ 'data-state' ] = 'collapse';
				}

				if ( isset( $fieldset[ 'type' ] ) )
				{
					$panel[ $group ]->add_item( ( new Fieldset( $fieldset[ 'legend' ], Fieldset::PANEL_FIELDSET ) )
						                            ->add_items( $fieldset[ 'fields' ] )
						                            ->set_attributes( $attr )
						                            ->set_group_type( $fieldset[ 'type' ] ) );
				}
				else
				{
					$panel[ $group ]->add_item( ( new Fieldset( $fieldset[ 'legend' ], Fieldset::PANEL_FIELDSET ) )
						                            ->add_items( $fieldset[ 'fields' ] )
						                            ->set_attributes( $attr ) );
				}

			}
		}

		if ( isset( $panel[ 'sidebar' ] ) )
		{
			$panel[ 'main' ]->add_class( 'col-sm-8' );
			$panel[ 'sidebar' ]->add_class( 'col-sm-4' );
		}

		return ( new Tag( 'form', implode( PHP_EOL, $panel ), $this->_attributes ) )->render();
	}

	public function __toString()
	{
		return $this->render();
	}
}