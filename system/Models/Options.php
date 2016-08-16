<?php
/**
 * O2System
 *
 * Application development framework for PHP 5.1.6 or newer
 *
 * @package       O2System
 * @author        Steeven Andrian Salim
 * @copyright     Copyright (c) 2010 - 2011 PT. Lingkar Kreasi (Circle Creative)
 * @license       http://www.o2system.net/license.html
 * @link          http://www.o2system.net | http://www.circle-creative.com
 */
// ------------------------------------------------------------------------
/**
 * Options Model Class
 *
 * @package       Application
 * @subpackage    Models
 * @category      Plugin Model
 * @version       1.0 Build 22.09.2011
 * @author        Steeven Lim
 * @contributor   Gen Gen Pikri Adam
 * @copyright     Copyright (c) 2010 - 2011 PT. Lingkar Kreasi (Circle Creative)
 * @license       http://www.o2system.net/license.html
 * @link          http://www.o2system.net | http://www.circle-creative.com
 */
// ------------------------------------------------------------------------
namespace O2System\Models;

use O2System\Core;

class Options extends Core\Model
{
	/**
	 * Option Config
	 *
	 * @access public
	 */
	var $option_config = [
		'root_option' => 'Choose {db}',
		'root_value'  => NULL,
		'default'     => NULL,
	];

	/**
	 * Controller Constructor
	 *
	 * @access public
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function build( $db, $root_option = TRUE )
	{
		$config = ( ! is_array( $db ) ? $this->option_config : $db );

		$db = ( ! is_array( $db ) ? $db : element( 'db', $db ) );

		$db_fields = $this->db->listFields( $db );

		if ( in_array( 'lang', $db_fields ) )
		{
			$this->db->where( 'lang', $this->active_language );
		}

		if ( element( 'conditions', $config ) )
		{
			$this->db->where( $config[ 'conditions' ] );

		}

		if ( in_array( 'ordering', $db_fields ) AND ! in_array( 'lft', $db_fields ) )
		{
			$this->db->orderBy( 'ordering', 'asc' );
		}
		elseif ( in_array( 'lft', $db_fields ) )
		{
			$this->db->orderBy( 'lft', 'asc' );
		}

		$query = $this->db->get( $db );

		$options = [ ];

		if ( $root_option === TRUE )
		{
			$root_option = element( 'root_option', $config, $this->option_config[ 'root_option' ] );
			$root_option = str_replace( '{db}', str_alias( str_readable( $db ) ), $root_option );

			$options[ element( 'root_value', $config, 0 ) ] = $root_option;
		}

		$option = element( 'option', $config, 'name' );
		$value  = element( 'value', $config, 'id' );

		if ( $query->numRows() > 0 )
		{
			foreach ( $query->result() as $row )
			{
				if ( $option == 'full_name' )
				{
					$_option = $row->first_name;

					if ( ! empty( $row->middle_name ) )
					{
						$_option .= ' ' . $row->middle_name;
					}

					if ( ! empty( $row->last_name ) )
					{
						$_option .= ' ' . $row->last_name;
					}
				}
				elseif ( is_serialized( $row->$option ) )
				{
					$_option = unserialize( $row->$option );
					$_option = element( $this->active_language, $_option );
				}
				else
				{
					$_option = $row->$option;
				}

				if ( isset( $row->depth ) AND $row->depth > 0 )
				{
					$sub     = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $row->depth ) . '&#8627;&nbsp;&nbsp;';
					$_option = $sub . $_option;
				}

				if ( $root_option == 'flip' )
				{
					$options[ $_option ] = $row->$value;
				}
				else
				{
					$options[ $row->$value ] = $_option;
				}
			}

			return $options;
		}

		return $options;
	}

	public function buildFlat( $db )
	{
		$config = ( ! is_array( $db ) ? $this->option_config : $db );

		$db = ( ! is_array( $db ) ? $db : element( 'db', $db ) );

		$db_fields = $this->db->listFields( $db );

		if ( in_array( 'lang', $db_fields ) )
		{
			$this->db->where( 'lang', $this->active_language );
		}

		if ( element( 'conditions', $config ) )
		{
			$this->db->where( $config[ 'conditions' ] );

		}

		if ( in_array( 'ordering', $db_fields ) AND ! in_array( 'lft', $db_fields ) )
		{
			$this->db->orderBy( 'ordering', 'asc' );
		}
		elseif ( in_array( 'lft', $db_fields ) )
		{
			$this->db->orderBy( 'lft', 'asc' );
		}

		$query = $this->db->get( $db );

		$options = [ ];

		$option = element( 'option', $config, 'name' );
		$value  = element( 'value', $config, 'id' );

		if ( $query->numRows() > 0 )
		{
			foreach ( $query->result() as $row )
			{
				if ( is_serialized( $row->$option ) )
				{
					$_option = unserialize( $row->$option );
					$_option = element( $this->active_language, $_option );
				}
				else
				{
					$_option = $row->$option;
				}

				$options[ $row->$value ] = $_option;
			}

			return $options;
		}

		return FALSE;
	}

	public function buildTree( $db, $parent_id = 0 )
	{
		$this->option_config = ( ! is_array( $db ) ? $this->option_config : $db );

		$db = ( ! is_array( $db ) ? $db : element( 'db', $db ) );

		$db_fields = $this->db->listFields( $db );

		if ( in_array( 'lang', $db_fields ) )
		{
			$this->db->where( 'lang', $this->active_language );
		}

		if ( in_array( 'parent_id', $db_fields ) )
		{
			$this->db->where( 'parent_id', $parent_id );
		}

		if ( element( 'conditions', $this->option_config ) )
		{
			$this->db->where( $this->option_config[ 'conditions' ] );

		}

		if ( in_array( 'ordering', $db_fields ) AND ! in_array( 'lft', $db_fields ) )
		{
			$this->db->orderBy( 'ordering', 'asc' );
		}
		elseif ( in_array( 'lft', $db_fields ) )
		{
			$this->db->orderBy( 'lft', 'asc' );
		}

		$query = $this->db->get( $db );

		$options = [ ];

		$option = element( 'option', $this->option_config, 'name' );
		$value  = element( 'value', $this->option_config, 'id' );

		if ( $query->numRows() > 0 )
		{
			foreach ( $query->result() as $row )
			{
				if ( is_serialized( $row->$option ) )
				{
					$_option = unserialize( $row->$option );
					$_option = element( $this->active_language, $_option );
				}
				else
				{
					$_option = $row->$option;
				}

				if ( $this->hasChild( $db, $row->id ) )
				{
					$_value_child = $this->buildTree( $db, $row->id );

					if ( ! empty( $_value_child ) )
					{
						$_value = [ $row->$value ];
						$_value = array_merge( $_value, $_value_child );
					}
				}
				else
				{
					$_value = $row->$value;
				}

				$options[ $_option ] = $_value;
			}

			return $options;
		}

		return FALSE;
	}

	public function get( $db, $root_option = TRUE )
	{
		$this->plugin( $db, $root_option );
	}

	public function plugin( $db, $root_option = TRUE )
	{
		$config = ( ! is_array( $db ) ? $this->option_config : $db );

		$db = ( ! is_array( $db ) ? $db : element( 'db', $db ) );

		$db_fields = $this->db->listFields( 'plugin_' . $db );

		if ( in_array( 'lang', $db_fields ) )
		{
			$this->db->where( 'lang', $this->active_language );
		}

		if ( in_array( 'ordering', $db_fields ) )
		{
			$this->db->orderBy( 'ordering', 'asc' );
		}

		if ( element( 'conditions', $config ) )
		{
			$this->db->where( $config[ 'conditions' ] );

		}

		$query = $this->db->get( 'plugin_' . $db );

		$options = [ ];

		if ( $root_option === TRUE )
		{
			$root_option = element( 'root_option', $config, $this->option_config[ 'root_option' ] );
			$root_option = str_replace( '{db}', readable( $db, TRUE ), $root_option );

			$options[ element( 'root_value', $config, 0 ) ] = $root_option;
		}

		if ( in_array( 'name', $db_fields ) )
		{
			$default_option = 'name';
		}
		elseif ( in_array( 'title', $db_fields ) )
		{
			$default_option = 'title';
		}

		$option = element( 'option', $config, $default_option );
		$value  = element( 'value', $config, 'id' );

		if ( $query->numRows() > 0 )
		{
			foreach ( $query->result() as $row )
			{
				if ( is_serialized( $row->$option ) )
				{
					$_option = unserialize( $row->$option );
					$_option = element( $this->active_language, $_option );
				}
				else
				{
					$_option = $row->$option;
				}

				$options[ $row->$value ] = $_option;
			}

			return $options;
		}

		return FALSE;
	}

	public function options( $parameter, $root_option = FALSE )
	{
		$config = ( is_array( $parameter ) ? $parameter : [ 'root_option' => 'please choose', 'root_value' => NULL ] );

		$parameter = ( is_array( $parameter ) ? element( 'parameter', $parameter ) : $parameter );

		if ( element( 'conditions', $config ) )
		{
			$this->db->where( $config[ 'conditions' ] );
		}

		$query = $this->db->orderBy( 'ordering', 'asc' )->getWhere( 'plugin_options', [ 'parameter' => $parameter ] );

		$options = [ ];

		if ( $root_option === TRUE )
		{
			$root_option = element( 'root_option', $config, $this->option_config[ 'root_option' ] );

			$options[ element( 'root_value', $config, NULL ) ] = $root_option;
		}


		foreach ( $query->result() as $row )
		{
			$option                 = ( is_serialized( $row->options ) ? unserialize( $row->options ) : $row->options );
			$option                 = ( is_array( $option ) ? element( $this->active_language, $option ) : $option );
			$options[ $row->value ] = $option;
		}

		return $options;
	}

	public function lookup( $parameter, $value )
	{
		$query = $this->db->getWhere( 'plugin_options', [ 'parameter' => $parameter, 'value' => $value ] );
		$row   = $query->firstRow();
		if ( $query->numRows() > 0 )
		{
			$option = ( is_serialized( $row->options ) ? unserialize( $row->options ) : $row->options );
			$option = ( is_array( $option ) ? element( $this->active_language, $option ) : $option );

			return $option;
		}

		return FALSE;

	}

	public function bloodType()
	{
		$result = [
			'o'  => 'O',
			'a'  => 'A',
			'b'  => 'B',
			'ab' => 'AB',
		];

		return $result;
	}

	public function tshirtSize()
	{
		$result = [
			'xs'   => 'XS',
			's'    => 'S',
			'm'    => 'M',
			'l'    => 'L',
			'xl'   => 'XL',
			'xxl'  => 'XXL',
			'xxxl' => 'XXXL',
		];

		return $result;
	}

	public function onlineMessengers()
	{
		$result = [
			'yahoo'    => 'Yahoo! Messenger',
			'skype'    => 'Windows Live MSN / Skype',
			'hangouts' => 'GTalk / Hangouts',
			'bbm'      => 'Blackberry Messenger',
			'wechat'   => 'WeChat',
			'line'     => 'Line',
			'whatsapp' => 'WhatsApp',
			'kakao'    => 'Kakao Talk',
		];

		return $result;
	}

	public function socialNetworks()
	{
		$result = [
			'facebook' => 'Facebook',
			'twitter'  => 'Twitter',
			'linkedin' => 'LinkedIn',
			'google+'  => 'Google+',
			'Path'     => 'Path',
		];

		return $result;
	}

	public function years()
	{
		$years = range( '1900', date( 'Y' ) );

		foreach ( $years as $year )
		{
			$result[ $year ] = $year;
		}

		return $result;
	}

	public function status()
	{
		return [
			'PUBLISH'   => 'Publish',
			'UNPUBLISH' => 'Unpublish',
			'DRAFT'     => 'Draft',
			'ARCHIVE'   => 'Archive',
		];
	}

	public function geographics( $db, $root_option = TRUE )
	{
		$config = ( ! is_array( $db ) ? $this->option_config : $db );

		$db = ( ! is_array( $db ) ? element( 'db', $db ) : $db );

		$query = $this->db->get( 'plugin_' . $db );

		if ( $root_option === TRUE )
		{
			$root_option = element( 'root_option', $config, $this->option_config[ 'root_option' ] );
			$root_option = str_replace( '{db}', $db, $root_option );

			$options[ $root_option ] = element( 'root_value', $config, NULL );
		}

		$options = [ ];
		$option  = element( 'option', $config, 'name' );
		$value   = element( 'value', $config, 'id' );

		if ( $query->numRows() > 0 )
		{
			foreach ( $query->result() as $row )
			{
				$options[ $row->$option ] = $row->$value;
			}

			return $options;
		}

		return FALSE;
	}

	public function currency( $root_option = 'please choose currency', $root_value = NULL )
	{
		$this->db->select( 'currency_name, currency_code' );
		$query                    = $this->db->getWhere( 'plugin_countries', [ 'currency_code' => 'idr' ] );
		$currency[ $root_option ] = $root_value;
		if ( $query->numRows() > 0 )
		{
			foreach ( $query->resultArray() as $row )
			{
				if ( element( 'currency_name', $row ) and element( 'currency_code', $row ) )
				{
					$currency[ $row[ 'currency_name' ] . ' (' . strtoupper( $row[ 'currency_code' ] ) . ')' ] = strtoupper( $row[ 'currency_code' ] );
				}
			}
		}

		return $currency;
	}

	public function months( $value = 'id', $option = 'title', $root_option = NULL, $root_value = NULL )
	{
		$query = $this->db->get( 'months' );

		if ( ! empty( $root_option ) )
		{
			$components[ $root_option ] = $root_value;
		}

		if ( $query->numRows() > 0 )
		{
			foreach ( $query->result() as $row )
			{
				$title                = unserialize( $row->$option );
				$title                = $title[ $this->active_language ];
				$components[ $title ] = $row->$value;
			}

			return $components;
		}

		return FALSE;
	}

	public function templates( $option = '', $root_option = TRUE )
	{
		$templates = $this->templates->packages();

		if ( $root_option === TRUE )
		{
			$options[ 0 ] = lang( 'CHOOSE_TEMPLATE' );
		}

		foreach ( $templates as $template )
		{
			$options[ $template->parameter ] = $template->details->name;
		}

		return $options;
	}

	public function templatesPositions( $option = '', $root_option = TRUE )
	{
		$templates = $this->templates->packages();

		if ( $root_option === TRUE )
		{
			$options[ 0 ] = lang( 'CHOOSE_TEMPLATE_POSITION' );
		}

		foreach ( $templates as $template )
		{
			foreach ( $template->settings->$option as $position )
			{
				$options[ $template->details->name ][ $template->parameter . ':' . alias( $position ) ] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8627;&nbsp;&nbsp;' . capitalize( normalize_alias( $position ) );
			}
		}

		return $options;
	}

	public function components( $option = '', $root_option = TRUE )
	{
		$components = $this->components->packages();

		if ( $root_option === TRUE )
		{
			$options[ 0 ] = lang( 'CHOOSE_COMPONENT' );
		}

		foreach ( $components as $component )
		{
			$options[ $component->parameter ] = $component->details->name;
		}

		return $options;
	}

	public function componentsMethods( $option = '', $root_option = TRUE )
	{
		$components = $this->components->packages();

		if ( $root_option === TRUE )
		{
			$options[ 0 ] = lang( 'CHOOSE_COMPONENT' );
		}

		foreach ( $components as $component )
		{
			if ( in_array( $component->parameter, [ 'administrator', 'developer' ] ) )
			{
				continue;
			}

			$methods = $this->components->methods( $component->id );

			@$options[ $component->code ] = $component->details->name;

			if ( ! empty( $methods ) )
			{
				foreach ( $methods as $method )
				{
					$label = $method->name;

					if ( isset( $method->depth ) AND $method->depth > 0 )
					{
						$sub   = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $method->depth ) . '&#8627;&nbsp;&nbsp;';
						$label = $sub . $label;
					}

					if ( $method->parameter != alias( $component->parameter ) )
					{
						@$options[ $method->code ] = $label;
					}
				}
			}
		}

		return $options;
	}

	public function plugins( $option = '', $root_option = TRUE )
	{
		$plugins = $this->plugins->packages();

		if ( $root_option === TRUE )
		{
			$options[ 0 ] = lang( 'CHOOSE_PLUGIN' );
		}

		foreach ( $plugins as $plugin )
		{
			$options[ $plugin->parameter ] = $plugin->details->name;
		}

		return $options;
	}

	public function pluginsMethods( $option = '', $root_option = TRUE )
	{
		$plugins = $this->plugins->packages();

		if ( $root_option === TRUE )
		{
			$options[ 0 ] = lang( 'CHOOSE_PLUGIN' );
		}

		foreach ( $plugins as $plugin )
		{
			$methods = $this->plugins->methods( $plugin->parameter );

			if ( ! empty( $methods ) )
			{
				foreach ( $this->plugins->methods( $plugin->parameter ) as $method )
				{
					$label = $method->name;

					if ( isset( $method->depth ) AND $method->depth > 0 )
					{
						$sub   = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $method->depth ) . '&#8627;&nbsp;&nbsp;';
						$label = $sub . $label;
					}

					@$options[ $plugin->details->name ][ $method->code ] = $label;
				}
			}
		}

		return $options;
	}

	public function linkTypes( $root_option = TRUE )
	{
		if ( $root_option === TRUE )
		{
			$options[ 0 ] = lang( 'CHOOSE_LINK_TYPE' );
		}

		$options[ 'Application Link' ] = [
			'component' => 'Component',
			'page'      => 'Page',
		];

		$options[ 'System Link' ] = [
			'menu-heading'   => 'Menu Heading',
			'text-separator' => 'Text Separator',
			'external-url'   => 'External URL',
			'wrapper-url'    => 'Wrapper URL',
		];

		return $options;
	}

	public function getExportDatabaseOptions( $root_option = '', $root_value = '' )
	{
		$export = [
			$root_option                                         => $root_value,
			'Microsoft Excel 2003 (*.xls)'                       => 'xls',
			'Comma Separated Value (*.csv)'                      => 'csv',
			'Extensible Markup Language (*.xml)'                 => 'xml',
			'Structured Query Language (*.sql)'                  => 'sql',
			'Zip File Structured Query Language (*.zip)'         => 'zip',
			'GZip File Structured Query Language (*.gzip)'       => 'gzip',
			'Structured Query Language Text File Format (*.txt)' => 'txt',
		];

		return $export;
	}

	public function getExportDataOptions( $root_option = '', $root_option_value = '' )
	{
		$export = [
			$root_option                         => $root_option_value,
			'Portable Document Format (*.pdf)'   => 'pdf',
			'Microsoft Word 2003 (*.doc)'        => 'doc',
			'Rich Text Format (*.rtf)'           => 'rtf',
			'Text Format (*.txt)'                => 'txt',
			'Extensible Markup Language (*.xml)' => 'xml',
			'HyperText Markup Language (*.html)' => 'html',
		];

		return $export;
	}

	public function getLookup( $id, $database, $value = 'title' )
	{
		$this->db->select( $value );
		$query = $this->db->getWhere( $database, [ 'id' => $id ] );
		if ( $query->numRows() > 0 )
		{
			$row = $query->result();
			if ( ! empty( $row[ 0 ] ) )
			{
				$data_lookup = $row[ 0 ]->$value;
				if ( is_serialized( $data_lookup ) )
				{
					$data_array = unserialize( $data_lookup );
					$data       = $data_array[ $this->active_language ];
				}
				else
				{
					$data = $data_lookup;
				}

				return $data;
			}
			else
			{
				return FALSE;
			}
		}

		return FALSE;
	}
}