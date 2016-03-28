<?php
/**
 * Created by PhpStorm.
 * User: Steeven
 * Date: 28/10/2015
 * Time: 1:00
 */

namespace O2System\Template\Factory;


class Form
{
    /**
     * Form ID
     *
     * @access  public
     * @type    string
     */
    public $id = '';

    /**
     * Form Class
     *
     * @access  public
     * @type    string
     */
    public $class = '';

    /**
     * Form Action
     *
     * @access  public
     * @type    string
     */
    public $action = '';

    /**
     * Form Method
     *
     * @access  public
     * @type    string
     */
    public $method = 'post';

    /**
     * Form Encryption Type
     *
     * @access  public
     * @type    string
     */
    public $enctype = 'multipart/form-data';

    /**
     * Form Fieldsets
     *
     * @access  protected
     * @type    array
     */
    protected $_fieldsets = array();

    /**
     * Form Hiddens
     *
     * @access  protected
     * @type    array
     */
    protected $_hiddens = array();

    /**
     * Form Buttons
     *
     * @access  protected
     * @type    array
     */
    protected $_buttons = array();

    /**
     * Class Constructor
     *
     * @param array $config
     *
     * @access  public
     */
    public function __construct( $config = array() )
    {
        foreach( $config as $key => $value )
        {
            if( isset( $this->{$key} ) )
            {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Set Fields
     *
     * @params  array   $fields
     *
     * @access  public
     * @return  object
     */
    public function set_fields( array $fields = array() )
    {
        $this->_fieldsets[ 'blank' ] = $fields;

        return $this;
    }

    /**
     * Set Form Hidden
     *
     * @param array $hiddens List of hidden fields
     *
     * @access  public
     * @return  $this
     */
    public function set_hiddens( array $hiddens = array() )
    {
        $this->_hiddens = $hiddens;

        return $this;
    }
    // ------------------------------------------------------------------------

    /**
     * Set Form Buttons
     *
     * @access  public
     *
     * @param array $buttons
     *
     * @return $this Instance of O2System\Libraries\Form
     */
    public function set_buttons( array $buttons = array() )
    {
        $this->_buttons = $buttons;

        return $this;
    }
    // ------------------------------------------------------------------------

    /**
     * Form Render
     *
     * @access  public
     *
     * @param string $return Return type html|array|object|json
     *
     * @thrown  _class_exception()
     *
     * @return mixed
     */
    public function render()
    {
        if( ! empty($this->_fieldsets))
        {
            foreach($this->_fieldsets as $legend => $fields)
            {
                $output[ 'fieldsets' ][ ] = new Form\Fieldset($legend, $fields);
            }
        }

        print_out($output);

        if( empty( $this->_config[ 'tpl' ] ) )
        {
            $this->_config[ 'tpl' ] = 'standard';
        }

        if( ! empty( $this->_config[ 'data_id' ] ) )
        {
            $this->_hiddens[ 'ID' ] = array(
                'input' => array(
                    'type' => 'hidden',
                    'attr' => array(
                        'value' => $this->_config[ 'data_id' ]
                    )
                )
            );
        }

        if( ! empty( $this->_config[ 'redirect' ] ) )
        {
            $this->_hiddens[ 'redirect' ] = array(
                'input' => array(
                    'type' => 'hidden',
                    'attr' => array(
                        'aria-hidden' => 'true',
                        'class'       => 'input-redirect hidden',
                        'name'        => 'input-redirect',
                        'value'       => $this->_config[ 'redirect' ],
                    )
                )
            );
        }

        if( Config::csrf( 'protection' ) === TRUE )
        {
            $security =& Loader::core( 'Security' );

            $this->_hiddens[ 'csrf' ] = array(
                'input' => array(
                    'type' => 'hidden',
                    'attr' => array(
                        'aria-hidden' => 'true',
                        'class'       => 'input-redirect hidden',
                        'name'        => $security->get_csrf_token_name(),
                        'value'       => $security->get_csrf_hash(),
                    )
                )
            );
        }

        $output[ 'form_open' ] = html( 'form', $this->_config[ 'attr' ] );

        if( ! empty( $this->_fieldsets ) )
        {
            foreach( $this->_fieldsets as $legend => $fieldset )
            {
                $output[ 'fieldsets' ][ ] = $this->fieldset->render( $legend, $fieldset );
            }

        }

        if( ! empty( $this->_hiddens ) )
        {
            foreach( $this->_hiddens as $label => $field )
            {
                $hiddens[ ] = $this->field->render( $label, $field, 'html' );
            }

            $output[ 'hiddens' ] = implode( PHP_EOL, $hiddens );
        }
        else
        {
            $output[ 'hiddens' ] = NULL;
        }

        if( isset( $this->_buttons[ 'add_new' ] ) && isset( $this->_buttons[ 'edit' ] ) )
        {
            if( empty( $this->_config[ 'data_id' ] ) )
            {
                $output[ 'buttons' ] = $this->button->render( $this->_buttons[ 'add_new' ] );
            }
            else
            {
                $output[ 'buttons' ] = $this->button->render( $this->_buttons[ 'edit' ] );
            }
        }
        else
        {
            $output[ 'buttons' ] = $this->button->render( $this->_buttons );
        }

        $output[ 'form_close' ] = html( '/form' );

        if( $return === 'html' )
        {
            if( isset( $this->_config[ 'html' ] ) )
            {
                $parser = &Loader::driver( 'parser' );

                return $parser->parse_source_code( $this->_config[ 'html' ], $output );
            }
            else
            {
                $parser = &Loader::driver( 'parser' );

                foreach( $this->_tpl_paths as $path )
                {
                    $filepath = $path . strtolower( $this->_config[ 'tpl' ] ) . EXT;

                    if( is_file( $filepath ) )
                    {
                        return $parser->parse_view( $filepath, $output );
                        break;
                    }
                }

                _class_exception( get_called_class(), 'Unable to load the requested form template file: ' . $this->_config[ 'tpl' ] . EXT );
            }
        }
        elseif( $return === 'array' )
        {
            return $output;
        }
        elseif( $return === 'object' )
        {
            return ( object )$output;
        }
        elseif( $return === 'json' )
        {
            return json_encode( $output );
        }
    }
}