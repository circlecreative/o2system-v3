<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, PT. Lingkar Kreasi (Circle Creative).
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     O2System
 * @author      Steeven Andrian Salim
 * @copyright   Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license     http://circle-creative.com/products/o2system/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @since       Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Template Assets Driver Class
 *
 * @package	      Template
 * @subpackage	  Library
 * @category	  Driver
 * @version       1.0 Build 11.09.2012
 * @author	      Steeven Andrian Salim
 * @copyright	  Copyright (c) 2005 - 2014 PT. Lingkar Kreasi (Circle Creative)
 * @license	      http://www.circle-creative.com/products/o2system/license.html
 * @link	      http://www.circle-creative.com
 */
// ------------------------------------------------------------------------

class O2_Template_Assets extends CI_Driver 
{
    /**
     * Class variables
     * @access private                        
     */
    private $_import_css = array();
    private $_link_css = array();
    private $_inline_css = array();

    /**
     * Valid CSS Media Attributes
     *
     * @access private                        
     */
    private $_valid_media_css = array(
            'all', // Used for all media type devices
            'aural', // Used for speech and sound synthesizers
            'braille', // Used for braille tactile feedback devices
            'embossed', // Used for paged braille printers
            'handheld', // Used for small or handheld devices
            'print', // Used for printers
            'projection', // Used for projected presentations, like slides
            'screen', // Used for computer screens
            'tty', // Used for media using a fixed-pitch character grid, like teletypes and terminals
            'tv' // Used for television-type devices
        );

    /**
     * Valid CSS Rel Attributes
     *
     * @access private                        
     */
    private $_valid_rel_css = array(
            'alternate', // Links to an alternate version of the document (i.e. print page, translated or mirror)
            'author', // Links to the author of the document
            'help', // Links to a help document
            'icon', // Imports an icon to represent the document
            'license', // Links to copyright information for the document
            'next', // Indicates that the document is a part of a series, and that the next document in the series is the referenced document
            'prefetch', // Specifies that the target resource should be cached
            'prev', // Indicates that the document is a part of a series, and that the previous document in the series is the referenced document
            'search', // Links to a search tool for the document
            'stylesheet' // URL to a style sheet to import
        );

    /**
     * Default CSS Attributes
     *
     * @access private                        
     */
    private $_default_attributes_css = array(
            'media' => 'screen',
            'rel'   => 'stylesheet',
            'type'  => 'text/css'
        );

    private $_link_header_js = array();
    private $_link_footer_js = array();
    private $_inline_header_js = array();
    private $_inline_footer_js =  array();

    private $_default_attributes_js = array(
            'type' => 'text/javascript'
        );

    private $_valid_type_js = array(
            'text/javascript',
            'text/ecmascript',
            'application/ecmascript',
            'application/javascript',
            'text/vbscript'
        );

    private $_output_header = '';
    private $_output_footer = '';

    private $_output_link_header_js = '';
    private $_output_link_footer_js = '';
    private $_output_inline_header_js = '';
    private $_output_inline_footer_js = '';

    static $last_position = 0;
    static $loaded_js;
    static $loaded_css;

    /**
     * Import CSS
     * 
     * @access  public
     * @param   string $filepath
     * @param   string $media
     * @return  void
     */
    public function import_css($filepath, $media = 'all')
    {
        // Media validation
        $media = $this->_validate_media_css($media);

        array_push($this->_import_css, "@import url($filepath) $media;");
    }

    // --------------------------------------------------------------------

    /**
     * Link CSS
     * 
     * @access  public
     * @param   string   $href
     * @param   array    $attributes
     * @return  void
     */
    public function link_css($href, $attributes = array())
    {
        $attributes = (empty($attributes) ? $this->_default_attributes_css : array_merge($this->_default_attributes_css, $attributes));

        $media = $this->_validate_media_css($attributes['media']);

        $rel = (in_array($attributes['rel'], $this->_valid_rel_css) ? $attributes['rel'] : $this->_default_attributes_css['rel']);

        array_push($this->_link_css, "<link rel=\"$rel\" media=\"$media\" type=\"text/css\" href=\"$href\">");
    }

    // --------------------------------------------------------------------

    /**
     * Inline CSS
     * 
     * @access  public
     * @param   string   $inline_code
     * @param   array    $attributes 
     */
    public function inline_css($inline_code, $attributes = array())
    {
        $attributes = (empty($attributes) ? $this->_default_attributes_css : array_merge($this->_default_attributes_css, $attributes));
        
        $media = $this->_validate_media_css($attributes['media']);

        $rel = (in_array($attributes['rel'], $this->_valid_rel_css) ? $attributes['rel'] : $this->_default_attributes_css['rel']);

        $rel_media = "rel=\"$rel\" media=\"$media\"";   
        
        $this->_inline_css[$rel_media][] = $inline_code;
    }

    // --------------------------------------------------------------------

    /**
     * Validate Inline CSS
     * 
     * @access  private
     * @param   string  $media
     * @return  string 
     */
    private function _validate_media_css($media)
    {
        if (strrpos($media, ',') !== FALSE)
        {
            $media = explode(',', $media);

            foreach($media as $name)
            {
                $name = trim($name);

                if(in_array($name, $this->_validate_media_css))
                {
                    $valid_media[] = $name;
                } 
            }

            return (empty($valid_media) ? $this->_default_attributes_css['media'] : implode(', ',$valid_media));
        }
        else
        {
            return (in_array($media, $this->_valid_media_css) ? $media : $this->_default_attributes_css['media']);
        }
    }
    // --------------------------------------------------------------------

    /**
     * CSS
     * 
     * @access  private
     * @return  string
     */
    private function css()
    {
        // Collect Link CSS
        if (count($this->_link_css) > 0)
        {
            $this->_link_css = array_unique($this->_link_css);
            $this->_output_header.= implode($this->_link_css, PHP_EOL);
        }

        // Collect Inline CSS
        if (count($this->_inline_css) > 0)
        {
            foreach ($this->_inline_css as $rel_media => $inline_css)
            {                
                $inline_css = array_unique($inline_css);

                $this->_output_header.= "<style type=\"text/css\" $rel_media>\n";
                    $this->_output_header.= implode($inline_css, PHP_EOL);
                $this->_output_header.= "</style> \n";
            }
        }

        // Collect Import CSS
        if (count($this->_css_import) > 0)
        {
            $this->_css_import = array_unique($this->_css_import);

            $this->_output_header = "<style type=\"text/css\">\n";
                $this->_output_header.= implode($this->_css_import, PHP_EOL);
            $this->_output_header = "</style> \n";
        }
    }
    // --------------------------------------------------------------------

    /**
     * Javascript Link
     * 
     * @access  public
     * @param   string   $href
     * @param   array    $attributes
     * @param   integer  $index
     * @param   string   $position
     */
    public function link_js($href, $attributes = array(), $index = '', $position = 'footer')
    {
        if(!is_array($attributes))
        {
            $position = $attributes;
            $attributes = $this->_default_attributes_js;
        }

        $attributes = (empty($attributes) ? $this->_default_attributes_js : array_merge($this->_default_attributes_js, $attributes));

        $js_output= "<script src=\"" . $href . "\"";

        foreach($attributes as $tag => $value)
        {
            if($tag == 'type')
            {
                if(in_array($value,$this->_valid_type_js))
                {
                    $js_output.= " type=\"$value\"";
                }
            }
            else
            {
                if(!empty($value))
                {
                    $js_output.= " $tag=\"$value\"";
                }
                else
                {
                    $js_output.= " $tag";
                }
            }
        }

        $js_output.= "></script>";

        if($position == 'header')
        {
            array_push($this->_link_header_js, $js_output);
        }
        else
        {
            array_push($this->_link_footer_js, $js_output);
        }
    }
    // -------------------------------------------------------------------- 

    /**
     * Inline Javascript Code
     * 
     * @access  public
     * @param   string   $inline_code
     * @param   array    $attributes
     * @param   string   $position   
     */
    public function inline_js($inline_code, $attributes = array(), $position = 'footer')
    {
        if(!is_array($attributes))
        {
            if(in_array($attributes, array('header','footer')))
            {
                $position = $attributes;
            }

            $attributes = $this->_default_attributes_js;
        }        

        $attributes = (empty($attributes) ? $this->_default_attributes_js : array_merge($this->_default_attributes_js, $attributes));

        $attributes_output = '';

        foreach($attributes as $tag => $value)
        {
            if($tag == 'type')
            {
                if(in_array($value,$this->_valid_type_js))
                {
                    $attributes_output.= " type=\"$value\"";
                }
            }
            else
            {
                if(!empty($value))
                {
                    $attributes_output.= " $tag=\"$value\"";
                }
                else
                {
                    $attributes_output.= " $tag";
                }
            }
        }

        if($position == 'header')
        {
            $this->_inline_header_js[$attributes_output][] = $inline_code;
        }
        else
        {
            $this->_inline_footer_js[$attributes_output][] = $inline_code;
        }
    }
    // --------------------------------------------------------------------   

    /**
     * Javascript
     *
     * An asset function that returns a script embed tag
     *
     * @access public
     * @param $file
     * @return string
     */
    public function js($return = 'all')
    {
        // Collect Header JS
        if (count($this->_link_header_js) > 0)
        {
            $_link_header_js = array_unique($this->_link_header_js);

            foreach ($_link_header_js as $_header_js)
            {
                $this->_output_link_header_js.= "$_header_js\n";
            }

            // Collect to Header Output
            $this->_output_header.= $this->_output_link_header_js;
        }

        if (count($this->_inline_header_js) > 0)
        {
            foreach ($this->_inline_header_js as $attributes => $inline_js)
            {
                $this->_output_inline_header_js.= "<script$attributes>\n";

                $inline_js = array_unique($inline_js);

                foreach($inline_js as $inline_code)
                {
                    $this->_output_inline_header_js.= "$inline_code\n";
                }
                $this->_output_inline_header_js.= "</script> \n";
            }
            
            // Collect to Header Output
            $this->_output_header.= $this->_output_inline_header_js;
        }

        // Collect Footer JS
        if (count($this->_link_footer_js) > 0)
        {
            $_link_footer_js = array_unique($this->_link_footer_js);

            foreach ($_link_footer_js as $_footer_js)
            {
                $this->_output_link_footer_js.= "$_footer_js\n";
            }

            // Collect to Footer Output
            $this->_output_footer.= $this->_output_link_footer_js;
        }     

        if (count($this->_inline_footer_js) > 0)
        {
            foreach ($this->_inline_footer_js as $attributes => $inline_js)
            {
                $this->_output_inline_footer_js.= "<script$attributes>\n";

                $inline_js = array_unique($inline_js);

                foreach($inline_js as $inline_code)
                {
                    $this->_output_inline_footer_js.= "$inline_code\n";
                }
                $this->_output_inline_footer_js.= "</script> \n";
            }
            
            // Collect to Footer Output
            $this->_output_footer.= $this->_output_inline_footer_js;
        }
    }

    protected function _parse_request($request)
    {
        // Create Request Object
        $request_obj = new Template_Assets_Params;

        if( strpos($request,'=') )
        {
            // Parse File
            $request = parse_ini_string($request);
            $request_obj->file = key($request);

            // Parse Params
            $request_obj->params = reset($request);

            $request_obj->params = preg_replace('(\\[(.*?)\\:(.*?)\\])', '$1=$2::', $request_obj->params);
            $request_obj->params = preg_split('[::]',$request_obj->params,-1,PREG_SPLIT_NO_EMPTY);

            foreach($request_obj->params as $param)
            {
                // Parse Load Request
                $param_ini = parse_ini_string($param);

                // Define parameter key
                $param_key = key($param_ini);

                if($param_key == 'attr')
                {
                    // Fixed JSON Format
                    $attr = preg_replace("/(\b)/", '$1"', reset($param_ini));
                    $attr = str_replace('"/"','/', $attr);
                    $attr = str_replace('"-"','-',$attr);

                    $request_obj->attr = json_decode($attr);
                }
                else
                {
                    $request_obj->$param_key = reset($param_ini);
                }
            }
        }
        else
        {
            self::$last_position = self::$last_position+1;
            $request_obj->filename = $request;
        }

        return $request_obj;
    }

    protected function _parse_path($path)
    {
        $x_path = preg_split('[/]', $path, -1, PREG_SPLIT_NO_EMPTY);
        $group_path = reset($x_path);

        $path = new stdClass;

        if(in_array($group_path, array('core', 'template')))
        {
            array_shift($x_path);

            if($group_path == 'core')
            {
                $path->folder = SYSPATH;
                $path->URL = system_url();
            }
            else
            {
                $path->folder = $this->directory;
                $path->URL = template_url();
            }
        }

        $path->folder = @$path->folder.implode('/', $x_path).'/';
        $path->URL = @$path->URL.implode('/', $x_path).'/';

        return $path;
    }

    protected function _load($file, $path, $ext)
    {
        // Parse File Request
        $request_file = $this->_parse_request($file);

        // Parse Path Request
        $request_path = $this->_parse_path($path);

        // Possible Filenames, always try to load minified asset first
        $filenames = array(
            $request_file->filename.'.min.'.$ext, // Minified Asset
            $request_file->filename.'.'.$ext      // Raw Asset 
        );

        $link_asset = 'link_'.$ext;

        if(is_dir($request_path->folder))
        {
            foreach($filenames as $filename)
            {
                if(is_file($request_path->folder.$filename))
                {
                    $this->$link_asset($request_path->URL.$filename, $request_file->attr, $request_file->index, $request_file->position);

                    break;
                }
            }
        }
    }

    public function load_js($js, $path = 'core')
    {
        $path = $path.'/assets/js/';
        $this->_load($js, $path, 'js');
    }

    function load_css($css, $path = 'core')
    {
        $path = $path.'/assets/css/';
        $this->_load($css, $path, 'css');
    }

    public function load_packages($package, $path = 'core')
    {
        if($path == 'core')
        {
            $asset_path = SYSPATH.'assets/packages/';
        }
        elseif($path == 'template')
        {
            $asset_path = $this->directory.'assets/packages/';
        }

        // Re-Define Package Folder
        $package_folder = $asset_path.$package.'/';

        if(is_dir($package_folder))
        {
            // Has Request
            if( strpos($package,'=') )
            {
                // Parse Package Request
                $package_ini = parse_ini_string($package);
                $package = key($package_ini);
                $requests = reset($package_ini);

                // Load Main Package
                $this->load_packages_js($package, $path);
                $this->load_packages_css($package, $path);

                $requests = preg_replace('(\\[(.*?)\\:(.*?)\\])', '$1=$2::', $requests);
                $requests = preg_split('[::]',$requests,-1,PREG_SPLIT_NO_EMPTY);

                foreach($requests as $request)
                {
                    // Parse Load Request
                    $load_request = parse_ini_string($request);

                    // Define Package Loader
                    $loader = 'load_'.key($load_request);

                    $files = reset($load_request);
                    $files = preg_split('[,]',$files,-1,PREG_SPLIT_NO_EMPTY);

                    foreach($files as $file)
                    {
                        $this->$loader($package, $file, $path);
                    }
                }

                // Load Init Package
                $this->load_packages_js($package.'.init', $path);
                $this->load_packages_css($package.'.init', $path);
            }
            else
            {
                // Load Default Package
                $files = array($package, $package.'.init');

                foreach($files as $file)
                {
                    $this->load_packages_js($package, $file, $path);
                    $this->load_packages_css($package, $file, $path);
                }
            }
        }
    }

    public function load_packages_js($package, $js, $path = 'core')
    {
        $path = $path.'/assets/packages/'.$package.'/';
        $this->_load($js, $path, 'js');
    }

    public function load_packages_css($package, $css, $path = 'core')
    {
        $path = $path.'/assets/packages/'.$package.'/';
        $this->_load($css, $path, 'css');
    }

    public function load_packages_themes($package, $theme, $path = 'core')
    {
        // Re-Define Path
        $path = $path.'/assets/packages/'.$package.'/themes/'.$theme.'/';

        $this->_load($theme, $path, 'js');
        $this->_load($theme, $path, 'css');
    }

    public function load_packages_plugins($package, $plugin, $path = 'core')
    {
        // Re-Define Path
        $path = $path.'/assets/packages/'.$package.'/plugins/'.$plugin.'/';

        $this->_load($plugin, $path, 'js');
        $this->_load($plugin, $path, 'css');
    }

    public function load_inline($script)
    {
        //print_out($script);
    }

    public function load_browser()
    {

    }

    public function load_favicon()
    {

    }

    public function load_fonts($font, $path = 'core')
    {
        // Check Request
        if( preg_match("/\b".'google:'."\b/i", $font) )
        {
            $font = str_replace('google:','',$font);
            $request = preg_split('[:]',$font,-1,PREG_SPLIT_NO_EMPTY);

            $font = reset($request);
            $type = end($request);

            $this->load_google_font($font, $type);
        }
        else
        {
            // Re-Define Path
            $path = $path.'/assets/fonts/'.$font.'/';
            $this->_load($font, $path, 'css');
        }
    }

    public function load_google_font($font, $type = '')
    {
        if(is_array($type) AND count($type) > 0)
        {
            $type = implode(',',$type);
        }

        $font = str_replace(' ','+',$font);

        $font_url = "http://fonts.googleapis.com/css?family=$font:$type";

        $this->link_css($font_url);
    }

    public function load_json($json)
    {
        if(is_file($json))
        {
            $json_file = $json;
        }
        else
        {
            $json_file = $this->directory.'assets/json/'.$json.'.json';
        }

        $json_file = @file_get_contents($json_file);

        $json_assets = json_decode($json_file);

        if(!empty($json_assets))
        {
            if(is_object($json_assets))
            {
                foreach($json_assets as $path => $assets)
                {
                    if(is_object($assets))
                    {
                        foreach($assets as $type => $files)
                        {
                            $loader = 'load_'.$type;

                            if($type == 'packages')
                            {
                                foreach($files as $package_name => $package_loaders)
                                {
                                    if($package_name == 'pack')
                                    {
                                        if(is_array($package_loaders))
                                        {
                                            foreach($package_loaders as $package_file)
                                            {
                                                $this->$loader($package_file, $path);
                                            }
                                        }
                                        elseif(is_string($package_loaders))
                                        {
                                            $this->$loader($package_loaders, $path);
                                        }
                                    }
                                    else
                                    {
                                        if(!isset($package_loaders->js))
                                        {
                                            $package_loaders->js = $package_name;
                                        }

                                        if(!isset($package_loaders->css))
                                        {
                                            $package_loaders->css = $package_name;
                                        }

                                        if(!isset($package_loaders->themes))
                                        {
                                            $package_loaders->themes = 'default';
                                        }

                                        foreach($package_loaders as $package_loader => $package_files)
                                        {
                                            $package_loader = $loader.'_'.$package_loader;

                                            if(is_array($package_files))
                                            {
                                                foreach($package_files as $package_file)
                                                {
                                                    $this->$package_loader($package_name, $package_file, $path);
                                                }
                                            }
                                            else
                                            {
                                                $this->$package_loader($package_name, $package_files, $path);
                                            }
                                        }
                                    }
                                }
                            }
                            else
                            {
                                if(is_array($files))
                                {
                                    foreach($files as $file)
                                    {
                                        $this->$loader($file, $path);
                                    }
                                }
                                else
                                {
                                    $this->$loader($files, $path);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Render Assets
     *
     * @access public   
     * @return string                    
     */
    public function render()
    {
        // Collect CSS
        $this->css();

        // Collect JS
        $this->js();

        $output = new stdClass;
        $output->{'header'} = $this->_output_header;
        $output->{'footer'} = $this->_output_footer;

        return $output; 
    }
}

class Template_Assets_Params
{
    public $file;
    public $params;
    public $position;
    public $index;
    public $path;
    public $attr;
}

/* End of file Template_Assets.php */
/* Location: ./system/libraries/Template/drivers/Template_Assets.php */