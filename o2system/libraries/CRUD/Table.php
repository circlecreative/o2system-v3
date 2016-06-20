<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 3/2/2016
 * Time: 5:27 PM
 */

namespace O2System\Libraries\CRUD;


use O2System\Bootstrap\Factory\Button;
use O2System\Bootstrap\Factory\Grid;
use O2System\Bootstrap\Factory\Group;
use O2System\Bootstrap\Factory\Image;
use O2System\Bootstrap\Factory\Input;
use O2System\Bootstrap\Factory\Link;
use O2System\Bootstrap\Factory\Pagination;
use O2System\Bootstrap\Factory\Panel;
use O2System\Bootstrap\Factory\Tag;
use O2System\Glob\ArrayObject;

class Table
{
	protected $_title        = NULL;
	protected $_show_entries = 10;
	protected $_attributes   = array();

	protected $_config = array(
		'show_nested'    => TRUE,
		'show_checkbox'  => TRUE,
		'show_id'        => TRUE,
		'show_numbering' => TRUE,
		'show_ordering'  => TRUE,
		'show_status'    => TRUE,
		'show_filter'    => TRUE,
		'show_toolbar'   => TRUE,
		'show_actions'   => TRUE,
		'show_labels'    => FALSE,
	);

	protected $_toolbar = array(
		'buttons' => array(
			'add-new'   => TRUE,
			'edit'      => FALSE,
			'publish'   => TRUE,
			'unpublish' => TRUE,
			'delete'    => TRUE,
			'archive'   => TRUE,
			'help'      => TRUE,
		),
	);

	protected $_actions = array(
		'view'    => TRUE,
		'copy'    => TRUE,
		'edit'    => TRUE,
		'delete'  => TRUE,
		'archive' => TRUE,
		'export'  => TRUE,
		'import'  => TRUE,
	);

	protected $_prepend_columns = array(
		'numbering' => array(
			'field'     => NULL,
			'label'     => '#',
			'attr'      => array(
				'name'  => '',
				'id'    => '',
				'class' => 'text-right',
				'width' => '3%',
				'style' => '',
			),
			'type'      => 'numbering',
			'show'      => [ 'lg', 'md', 'sm' ],
			'hidden'    => FALSE,
			'sorting'   => TRUE,
			'filtering' => FALSE,
			'options'   => FALSE,
			'nested'    => FALSE,
			'content'   => '',
		),
		'id'        => array(
			'field'     => 'id',
			'label'     => 'ID',
			'attr'      => array(
				'name'  => '',
				'id'    => '',
				'class' => 'text-right',
				'width' => '3%',
				'style' => '',
			),
			'type'      => 'txt',
			'show'      => [ 'lg', 'md', 'sm' ],
			'hidden'    => FALSE,
			'sorting'   => TRUE,
			'filtering' => FALSE,
			'options'   => FALSE,
			'nested'    => FALSE,
			'content'   => '',
		),
		'checkbox'  => array(
			'field'     => 'id',
			'label'     => '<div class="checkbox"><input type="checkbox" data-action="item-checkboxes"><label></label></div>',
			'attr'      => array(
				'name'  => '',
				'id'    => '',
				'class' => 'width-fit',
				'width' => '2%',
				'style' => '',
			),
			'type'      => 'checkbox',
			'show'      => [ 'lg', 'md', 'sm' ],
			'hidden'    => FALSE,
			'sorting'   => FALSE,
			'filtering' => FALSE,
			'options'   => FALSE,
			'nested'    => FALSE,
		),
		'images'    => array(
			'field'     => 'images',
			'label'     => NULL,
			'attr'      => array(
				'name'  => '',
				'id'    => '',
				'class' => '',
				'width' => '5%',
				'style' => '',
			),
			'type'      => 'image',
			'show'      => [ 'lg', 'md', 'sm' ],
			'hidden'    => FALSE,
			'sorting'   => FALSE,
			'filtering' => FALSE,
			'options'   => FALSE,
			'nested'    => FALSE,
		),
	);

	protected $_append_columns = array(
		'ordering'         => array(
			'field'     => 'ordering',
			'label'     => '',
			'attr'      => array(
				'name'  => '',
				'id'    => '',
				'class' => 'width-fit',
				'width' => '1%',
				'style' => '',
			),
			'type'      => 'ordering',
			'show'      => [ 'lg', 'md', 'sm' ],
			'hidden'    => FALSE,
			'sorting'   => FALSE,
			'filtering' => FALSE,
			'options'   => TRUE,
			'nested'    => FALSE,
		),
		'status'           => array(
			'field'     => 'record->status',
			'label'     => 'STATUS',
			'attr'      => array(
				'name'  => '',
				'id'    => '',
				'class' => '',
				'width' => '5%',
				'style' => '',
			),
			'type'      => 'status',
			'show'      => [ 'lg', 'md', 'sm' ],
			'hidden'    => FALSE,
			'sorting'   => TRUE,
			'filtering' => array(
				'options' => array(
					'PUBLISH'   => 'PUBLISH',
					'UNPUBLISH' => 'UNPUBLISH',
					'DRAFT'     => 'DRAFT',
					'ARCHIVE'   => 'ARCHIVE',
					'DELETE'    => 'DELETE',
				),
			),
			'grouping'  => TRUE,
			'options'   => TRUE,
			'nested'    => FALSE,
		),
		'create_timestamp' => array(
			'field'     => 'create_timestamp',
			'label'     => 'CREATED_DATE',
			'attr'      => array(
				'name'  => '',
				'id'    => '',
				'class' => '',
				'width' => 'width-fit',
				'style' => '',
			),
			'type'      => 'date-user',
			'show'      => FALSE,
			'hidden'    => TRUE,
			'sorting'   => TRUE,
			'filtering' => TRUE,
			'grouping'  => FALSE,
			'options'   => TRUE,
			'nested'    => FALSE,
		),
		'update_timestamp' => array(
			'field'     => 'update_timestamp',
			'label'     => 'MODIFIED_DATE',
			'attr'      => array(
				'name'  => '',
				'id'    => '',
				'class' => '',
				'width' => 'width-fit',
				'style' => '',
			),
			'type'      => 'date-user',
			'show'      => FALSE,
			'hidden'    => TRUE,
			'sorting'   => TRUE,
			'filtering' => FALSE,
			'grouping'  => FALSE,
			'options'   => TRUE,
			'nested'    => FALSE,
		),
		'actions'          => array(
			'field'     => NULL,
			'label'     => NULL,
			'attr'      => array(
				'name'  => '',
				'id'    => '',
				'class' => '',
				'width' => '22.3%',
				'style' => '',
			),
			'type'      => 'actions',
			'show'      => [ 'lg', 'md', 'sm' ],
			'hidden'    => FALSE,
			'sorting'   => FALSE,
			'filtering' => TRUE,
			'grouping'  => FALSE,
			'options'   => FALSE,
			'nested'    => FALSE,
		),
	);

	protected $_filter_columns;
	protected $_sorting_columns;
	protected $_options_columns;

	protected $_columns;
	protected $_rows;
	protected $_results;

	public function __construct()
	{
		\O2System::View()->assets->addAsset( 'crud-table' );
	}

	public function setTitle($title )
	{
		$this->_title = $title;
	}

	public function setAttributes(array $attributes )
	{
		$this->_attributes = $attributes;
	}

	public function setConfig(array $config )
	{
		$this->_config = array_merge( $this->_config, $config );
	}

	public function setShowEntries($entries )
	{
		$this->_show_entries = (int) $entries;
	}

	public function setToolbar(array $toolbar )
	{
		$this->_toolbar = array_merge( $this->_toolbar, $toolbar );
	}

	public function setActions(array $actions )
	{
		$this->_actions = array_merge( $this->_actions, $actions );
	}

	public function setColumns(array $columns )
	{
		$this->_columns = new ArrayObject();

		// Build Prepend Columns
		foreach ( $this->_prepend_columns as $column_key => $column_config )
		{
			if ( isset( $this->_config[ 'show_' . $column_key ] ) AND $this->_config[ 'show_' . $column_key ] === TRUE )
			{
				$this->_columns[ $column_key ] = new ArrayObject( $column_config );
			}
		}

		// Build Table Columns
		foreach ( $columns as $column_key => $column_config )
		{
			$this->_columns[ $column_key ] = new ArrayObject( $column_config );

			// Filtered Columns
			if ( element( 'filtering', $column_config ) == TRUE )
			{
				$this->_filter_columns[ $column_config[ 'field' ] ] = $column_config[ 'label' ];
			}

			// Sorting Columns
			if ( element( 'sorting', $column_config ) == TRUE )
			{
				$this->_sorting_columns[] = $column_key;
			}

			// Options Columns
			if ( element( 'options', $column_config ) == TRUE )
			{
				$this->_options_columns[ $column_config[ 'field' ] ] = $column_config[ 'label' ];
			}
		}

		// Build Append Columns
		foreach ( $this->_append_columns as $column_key => $column_config )
		{
			if ( $column_config[ 'type' ] === 'actions' )
			{
				$column_config[ 'label' ] = ( new Button( 'Show Filter', [ 'data-action' => 'table-filter-toggle' ] ) )->isTiny()->isBlock();
			}
			elseif ( isset( $column_config[ 'label' ] ) )
			{
				// Try From Table Lang
				$column_label = lang( 'TBL_COL_' . strtoupper( $column_key ) );

				// Try From Global Lang
				$column_label = ( $column_label == '' ? lang( strtoupper( $column_key ) ) : $column_label );

				// Define From Column Name
				if ( $column_label == '' )
				{
					$column_label = str_readable( $column_key );
					$column_label = str_capitalize( $column_label );
				}

				$column_config[ 'label' ] = $column_label;
			}

			if ( isset( $this->_config[ 'show_' . $column_key ] ) AND $this->_config[ 'show_' . $column_key ] === TRUE )
			{
				$this->_columns[ $column_key ] = new ArrayObject( $column_config );
			}
		}
	}

	public function setRows(array $rows )
	{
		$this->_rows = $rows;
	}

	public function setResults(ArrayObject $results )
	{
		$this->_results = $results;
	}

	public function render()
	{
		$panel = new Panel( Panel::DEFAULT_PANEL );
		$panel->setTitle( $this->_title );
		$panel->addAttribute( 'data-role', 'table-list-panel' );

		if ( $this->_config[ 'show_labels' ] === TRUE )
		{
			$toolbar_actions = array(
				'add-new'   => ( new Button( lang( 'BTN_ADDNEW' ), [ 'data-action' => 'toolbar-add-new' ] ) )->isMedium()->setIcon( 'fa fa-file' ),
				'edit'      => ( new Button( lang( 'BTN_EDIT' ), [ 'data-action' => 'toolbar-edit' ] ) )->isMedium()->setIcon( 'fa fa-edit' ),
				'publish'   => ( new Button( lang( 'BTN_SELECTED_PUBLISH' ), [ 'data-action' => 'toolbar-publish' ] ) )->isMedium()->setIcon( 'fa fa-eye' ),
				'unpublish' => ( new Button( lang( 'BTN_SELECTED_UNPUBLISH' ), [ 'data-action' => 'toolbar-unpublish' ] ) )->isMedium()->setIcon( 'fa fa-eye-slash' ),
				'delete'    => ( new Button( lang( 'BTN_SELECTED_DELETE' ), [ 'data-action' => 'toolbar-delete' ] ) )->isMedium()->setIcon( 'fa fa-trash' ),
				'archive'   => ( new Button( lang( 'BTN_ARCHIVE' ), [ 'data-action' => 'toolbar-archive' ] ) )->isMedium()->setIcon( 'fa fa-archive' ),
				'help'      => ( new Button( lang( 'BTN_HELP' ), [ 'data-action' => 'toolbar-help' ] ) )->isMedium()->setIcon( 'fa fa-question-circle' ),
			);
		}
		else
		{
			$toolbar_actions = array(
				'add-new'   => ( new Button( [ 'data-action' => 'toolbar-add-new' ] ) )->isMedium()->setIcon( 'fa fa-file' ),
				'edit'      => ( new Button( [ 'data-action' => 'toolbar-edit' ] ) )->isMedium()->setIcon( 'fa fa-edit' ),
				'publish'   => ( new Button( [ 'data-action' => 'toolbar-publish' ] ) )->isMedium()->setIcon( 'fa fa-eye' ),
				'unpublish' => ( new Button( [ 'data-action' => 'toolbar-unpublish' ] ) )->isMedium()->setIcon( 'fa fa-eye-slash' ),
				'delete'    => ( new Button( [ 'data-action' => 'toolbar-delete' ] ) )->isMedium()->setIcon( 'fa fa-trash' ),
				'archive'   => ( new Button( [ 'data-action' => 'toolbar-archive' ] ) )->isMedium()->setIcon( 'fa fa-archive' ),
				'help'      => ( new Button( [ 'data-action' => 'toolbar-help' ] ) )->isMedium()->setIcon( 'fa fa-question-circle' ),
			);
		}

		$toolbar_buttons = new Group( Group::BUTTON_GROUP );

		foreach ( $this->_toolbar[ 'buttons' ] as $toolbar_action => $toolbar_button )
		{
			if ( $toolbar_button === TRUE )
			{
				$toolbar_buttons->addItem( $toolbar_actions[ $toolbar_action ] );
			}
		}

		$panel->setOptions( $toolbar_buttons );


		$table = new Tag( 'table', $this->_attributes );
		$table->addAttribute( 'data-role', 'table-list' );
		$table->appendContent( $this->_renderThead() );
		$table->appendContent( $this->_renderTbody() );

		$grid_entries = new Grid();
		$grid_entries->setNumPerRows( 2 );

		foreach ( range( $this->_show_entries, 100, 10 ) as $entries )
		{
			$entries_options[ $entries ] = $entries;
		}

		$grid_entries->addItem( implode( PHP_EOL, array(
			( new Input( 'select' ) )
				->setAttributes( array(
					                  'data-action' => 'table-filter-entries',
					                  'name'        => 'entries',
					                  'class'       => 'select input-sm',
				                  ) )
				->setLabel( 'Entries: ' )
				->setOptions( $entries_options ),
			( new Input( 'select' ) )
				->setAttributes( array(
					                  'data-action' => 'table-filter-status',
					                  'name'        => 'status',
					                  'class'       => 'select input-sm',
				                  ) )
				->setLabel( 'Status: ' )
				->setOptions( array(
					               'PUBLISH'   => lang( 'PUBLISH' ),
					               'UNPUBLISH' => lang( 'UNPUBLISH' ),
					               'DRAFT'     => lang( 'DRAFT' ),
					               'ARCHIVE'   => lang( 'ARCHIVE' ),
					               'DELETE'    => lang( 'DELETE' ),
				               ) ),
		) ) );

		$panel->setBody( $grid_entries );

		$panel->setTable( new Tag( 'div', $table, [ 'class' => 'table-responsive' ] ) );

		$panel->setFooter( ( new Pagination( $this->_results->total->pages ) )
			                    ->setLink( current_url() )
			                    ->addAttribute( 'data-role', 'table-list-pagination' )
		);

		return ( new Tag( 'form', $panel, array(
			'data-form' => 'table-list-form',
			'data-role' => 'table-list-panel',
			'action'    => current_url(),
			'method'    => 'get',
		) ) )->render();
	}

	protected function _renderThead()
	{
		$thead = new Tag( 'thead' );

		$tr_label = new Tag( 'tr' );

		$tr_filter = new Tag( 'tr' );
		$tr_filter->addClass( 'hidden' );
		$tr_filter->addAttribute( 'data-role', 'table-filter' );

		foreach ( $this->_columns as $column_key => $column_config )
		{
			$attr = $column_config->attr;

			if ( empty( $attr[ 'id' ] ) )
			{
				$attr[ 'id' ] = 'col-label-' . $column_key;
			}

			$th_label = new Tag( 'th', $attr );
			$th_label->appendContent( $column_config->label );

			$attr[ 'id' ] = 'col-filter-' . $column_key;

			$th_filter = new Tag( 'th', $attr );

			if ( $column_config->filtering !== FALSE )
			{
				$column_config->key = $column_key;
				$th_filter->appendContent( $this->_renderThFilter( $column_config ) );
			}

			if ( isset( $column_config->show ) )
			{
				foreach ( [ 'lg', 'md', 'sm', 'xs' ] as $class_prefix )
				{
					if ( in_array( $class_prefix, $column_config->show ) )
					{
						continue;
					}

					$th_label->addClass( 'hidden-' . $class_prefix );
					$th_filter->addClass( 'hidden-' . $class_prefix );
				}
			}

			$th_label->addClass( 'col-thead-' . $column_key );
			$th_filter->addClass( 'col-tfilter-' . $column_key );

			$tr_label->appendContent( $th_label );
			$tr_filter->appendContent( $th_filter );
		}

		$thead->appendContent( $tr_label );
		$thead->appendContent( $tr_filter );

		return $thead;
	}

	protected function _renderThFilter($config )
	{
		switch ( $config->type )
		{
			default:

				return '';

				break;

			case 'edit':
			case 'text':

				$attr[ 'data-action' ] = 'col-filter';
				$attr[ 'class' ] = 'form-control input-sm';

				if ( isset( $config->filtering[ 'attr' ] ) )
				{
					$attr = array_merge( $attr, $config->filtering[ 'attr' ] );
				}

				return form_input( $config->key, \O2System::Input()->get( $config->key ), $attr );

				break;

			case 'price':
			case 'number':

				$attr[ 'data-action' ] = 'col-filter';
				$attr[ 'class' ] = 'form-control input-sm';

				if ( isset( $config->filtering[ 'attr' ] ) )
				{
					$attr = array_merge( $attr, $config->filtering[ 'attr' ] );
				}

				return form_number( $config->key, \O2System::Input()->get( $config->key ), $attr );

				break;

			case 'date':

				$attr[ 'data-action' ] = 'col-filter';
				$attr[ 'class' ] = 'form-control input-sm';

				if ( isset( $config->filtering[ 'attr' ] ) )
				{
					$attr = array_merge( $attr, $config->filtering[ 'attr' ] );
				}

				return form_date( $config->key, \O2System::Input()->get( $config->key ), $attr );


				break;

			case 'actions':

				$buttons = new Group( Group::BUTTON_GROUP );

				if($this->_config['show_labels'] === TRUE)
				{
					$buttons->addItem( ( new Button( lang( 'BTN_SUBMIT' ), [ 'data-action' => 'table-filter-submit' ] ) )->setIcon( 'fa fa-search' )->isSubmit()->isSmall() );
					$buttons->addItem( ( new Button( lang( 'BTN_RESET' ), [ 'data-action' => 'table-filter-reset' ] ) )->setIcon( 'fa fa-repeat' )->isReset()->isSmall() );
					$buttons->addItem( ( new Button( lang( 'BTN_RELOAD' ), [ 'data-action' => 'table-filter-reload' ] ) )->setIcon( 'fa fa-refresh' )->isReset()->isSmall() );
				}
				else
				{
					$buttons->addItem( ( new Button( [ 'data-action' => 'table-filter-submit' ] ) )->setIcon( 'fa fa-search' )->isSubmit()->isSmall() );
					$buttons->addItem( ( new Button( [ 'data-action' => 'table-filter-reset' ] ) )->setIcon( 'fa fa-repeat' )->isReset()->isSmall() );
					$buttons->addItem( ( new Button( [ 'data-action' => 'table-filter-reload' ] ) )->setIcon( 'fa fa-refresh' )->isReset()->isSmall() );
				}

				return $buttons;

				break;
		}
	}

	protected function _renderTbody()
	{
		$tbody = new Tag( 'tbody' );

		if ( empty( $this->_rows ) )
		{
			$tr = new Tag( 'tr' );

			if ( empty( $_GET ) )
			{
				$tr->addClass( 'info' );
				$message = 'Empty data';
			}
			else
			{
				$tr->addClass( 'danger' );
				$message = 'Result not found';
			}

			$td = new Tag( 'td', $message );
			$td->addClass( 'text-center' );
			$td->addAttribute( 'colspan', count( $this->_columns ) );

			$tr->appendContent( $td );
			$tbody->appendContent( $tr );
		}
		else
		{
			$numbering = 1;

			if ( isset( $_GET[ 'page' ] ) )
			{
				if ( $_GET[ 'page' ] > 1 )
				{
					$numbering = (int) ( $_GET[ 'page' ] * $this->_results->total->entries ) + 1;
				}
			}

			foreach ( $this->_rows as $row )
			{
				$tr = new Tag( 'tr' );
				$tr->addAttribute( 'data-id', $row->id );

				foreach ( $this->_columns as $column_key => $column_config )
				{
					$column_content = '';

					if ( $column_config->type === 'numbering' )
					{
						$column_content = $numbering++;
					}
					elseif ( isset( $column_config->field ) )
					{
						if ( strpos( $column_config->field, '->' ) !== FALSE )
						{
							$x_field = explode( '->', $column_config->field );

							$column_content = $row;
							foreach ( $x_field as $field )
							{
								if ( isset( $column_content[ $field ] ) )
								{
									$column_content = $column_content[ $field ];
								}
								elseif ( isset( $column_content->{$field} ) )
								{
									$column_content = $column_content->{$field};
								}
							}
						}
						else
						{
							$column_content = isset( $row[ $column_config->field ] ) ? $row[ $column_config->field ] : NULL;
						}
					}

					$td = new Tag( 'td', $column_config->attr );
					$td->appendContent( $this->_renderTd( $column_content, $column_config->type ) );

					if ( isset( $column_config->show ) )
					{
						foreach ( [ 'lg', 'md', 'sm', 'xs' ] as $class_prefix )
						{
							if ( in_array( $class_prefix, $column_config->show ) )
							{
								continue;
							}

							$td->addClass( 'hidden-' . $class_prefix );
						}
					}

					$td->addClass( 'col-tbody-' . $column_key );

					$tr->appendContent( $td );
				}

				$tbody->appendContent( $tr );
			}
		}

		return $tbody;
	}

	protected function _renderTd($content, $type )
	{
		switch ( $type )
		{
			default:
			case 'numbering':
			case 'text':
				return $content;
				break;

			case 'checkbox':
				return '<div class="checkbox"><input type="checkbox" data-action="item-checkbox" value="' . $content . '"><label></label></div>';
				break;

			case 'image':

				if ( is_array( $content ) )
				{
					$content = reset( $content );
				}

				if ( $content instanceof Image )
				{
					return $content->addAttributes( [ 'width' => 25, 'height' => 25 ] );
				}

				return new Image( $content, Image::THUMBNAIL_IMAGE, [ 'width' => 25, 'height' => 25 ] );

				break;

			case 'number':
				if ( $content instanceof ArrayObject )
				{
					return $content->amount;
				}

				return $content;
				break;

			case 'price':
				if ( $content instanceof ArrayObject )
				{
					return number_price( $content->amount, $content->currency, TRUE );
				}

				return number_price( $content );

				break;

			case 'timestamp':
				return format_date( $content );
				break;

			case 'date':
				return format_date( $content, '%Y-%m-%d' );
				break;

			case 'status':

				if ( $content === 'PUBLISH' )
				{
					return ( new Button( [ 'data-action' => 'item-publish' ] ) )->isSmall()->setIcon( 'fa fa-eye' );
				}
				else
				{
					return ( new Button( [ 'data-action' => 'item-unpublish' ] ) )->isSmall()->setIcon( 'fa fa-eye-slash' );
				}

				break;

			case 'actions':

				if ( $this->_config[ 'show_labels' ] === TRUE )
				{
					$actions = array(
							'views'   => ( new Button( lang( 'BTN_VIEW' ), [ 'data-action' => 'item-view' ] ) )->isSmall()->setIcon( 'fa fa-eye' ),
							'copy'    => ( new Button( lang( 'BTN_COPY' ), [ 'data-action' => 'item-copy' ] ) )->isSmall()->setIcon( 'fa fa-clone' ),
							'edit'    => ( new Button( lang( 'BTN_EDIT' ), [ 'data-action' => 'item-edit' ] ) )->isSmall()->setIcon( 'fa fa-edit' ),
							'delete'  => ( new Button( lang( 'BTN_DELETE' ), [ 'data-action' => 'item-delete', 'data-confirm' => 'Are you sure?' ] ) )->isSmall()->setIcon( 'fa fa-trash' ),
							'archive' => ( new Button( lang( 'BTN_ARCHIVE' ), [ 'data-action' => 'item-archive' ] ) )->isSmall()->setIcon( 'fa fa-achive' ),
							'export'  => ( new Button( lang( 'BTN_EXPORT' ), [ 'data-action' => 'item-export' ] ) )->isSmall()->setIcon( 'fa fa-mail-forward' ),
							'import'  => ( new Button( lang( 'BTN_IMPORT' ), [ 'data-action' => 'item-import' ] ) )->isSmall()->setIcon( 'fa fa-mail-reply' ),
					);
				}
				else
				{
					$actions = array(
							'views'   => ( new Button( [ 'data-action' => 'item-view' ] ) )->isSmall()->setIcon( 'fa fa-eye' ),
							'copy'    => ( new Button( [ 'data-action' => 'item-copy' ] ) )->isSmall()->setIcon( 'fa fa-clone' ),
							'edit'    => ( new Button( [ 'data-action' => 'item-edit' ] ) )->isSmall()->setIcon( 'fa fa-edit' ),
							'delete'  => ( new Button( [ 'data-action' => 'item-delete', 'data-confirm' => 'Are you sure?' ] ) )->isSmall()->setIcon( 'fa fa-trash' ),
							'archive' => ( new Button( [ 'data-action' => 'item-archive' ] ) )->isSmall()->setIcon( 'fa fa-achive' ),
							'export'  => ( new Button(  [ 'data-action' => 'item-export' ] ) )->isSmall()->setIcon( 'fa fa-mail-forward' ),
							'import'  => ( new Button( [ 'data-action' => 'item-import' ] ) )->isSmall()->setIcon( 'fa fa-mail-reply' ),
					);
				}

				$buttons = new Group( Group::BUTTON_GROUP );

				foreach ( $this->_actions as $action => $button )
				{
					if ( $button === TRUE )
					{
						$buttons->addItem( $actions[ $action ] );
					}
				}

				return $buttons;

				break;
		}
	}

	public function __toString()
	{
		return $this->render();
	}
}