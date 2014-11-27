<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * O2System
 *
 * Application development framework for PHP 5.1.6 or newer
 *
 * @package      O2System
 * @author       Steeven Andrian Salim
 * @copyright    Copyright (c) 2010 - 2013 PT. Lingkar Kreasi (Circle Creative)
 * @license      http://www.o2system.net/license.html
 * @link         http://www.o2system.net | http://www.circle-creative.com
 */
// ------------------------------------------------------------------------
/**
 * XML Libraries for CodeIgniter
 *
 * @package     Application
 * @subpackage  Libraries
 * @category    Library
 * @author      Steeven Andrian Salim
 * @copyright   Copyright (c) 2010 - 2013 PT. Lingkar Kreasi (Circle Creative)
 * @link        http://circle-creative.com/products/o2system/user-guide/libraries/XML.html
 */
// ------------------------------------------------------------------------
class O2_XML
{
    /**
     * XML Class Variables
     * @access  public
     */     
    var $file;
    var $raw_contents;
    var $array;
    var $object;
    var $remove_node = array();
    var $version = '1.0';
    var $encoding = 'UTF-8';

    public function load($file)
    {
        if( ! file_exists($file))
        {
            show_error('Failed Load XML:' . $file);
        }

        $this->file = $file;
        $this->raw_contents = file_get_contents($file);
    }

    public function parse($file = '', $return = 'array')
    {
        $file = ($file == '' ? $this->file : $file);
        $this->load($file);

        $this->object = simplexml_load_file($file); 

        if(count($this->remove_node) > 0)
        {
            remove_object($this->remove_node,$this->object);
        }

        $this->array = object_to_array($this->object);

        return ($return == 'array' ? $this->array : $this->object);
    }

    public function parse_node($nodes)
    {
        if(is_array($nodes))
        {
            $data = array();
            
            foreach($nodes as $key => $node)
            {
                $sub_key = substr($key, 0, -1);

                if(isset($node[$sub_key]))
                {
                    $data[$key] = $this->parse_node($node[$sub_key]);
                }
                else
                {
                    $data[$key] = $this->parse_node($node);
                }
            }

            return $data;
        }
        else
        {
            if(preg_match('[,]', $nodes))
            {
                return preg_split('/[\s,]+/', $nodes, 0, PREG_SPLIT_NO_EMPTY);
            }
            elseif(preg_match('[load_ini]', $nodes))
            {
                // Parse ini string
                $nodes = parse_ini_string($nodes);

                return $this->parse_ini($nodes['load_ini']);                
            }
            elseif(preg_match('[load_json]', $nodes))
            {
                // Parse ini string
                $nodes = parse_ini_string($nodes);
                
                return $this->parse_json($nodes['load_json']);
            }
            else
            {
                return $nodes;
            }
        }
    }

    public function parse_ini($ini_file)
    {
        // Explode folder path
        $folder = explode('/', $this->file);

        // Remove last call xml file
        array_pop($folder);

        // Implode folder path
        $folder = implode('/', $folder) . '/';
        
        // Parse ini
        $ini_file = $folder . $ini_file;

        if(file_exists($ini_file)) 
        {
            return parse_ini_file($ini_file, true);
        }
        else
        {
            show_error('Error Loading INI File:' . $ini_file);
        }        
    }    

    public function remove_node($node = array())
    {
        $this->remove_node = ( ! is_array($node) ? array($node) : $node);
    }


    public function generate($array, $first_node = 'root')
    {
        $output = "<?xml version=\"$this->version\" encoding=\"$this->encoding\"?>\r\n";
        if (element('attributes', $array))
        {
            $output .= "<$first_node";
            foreach ($array['attributes'] as $attributes_key => $attributes_value)
            {
                $attributes_key = str_replace('-', '_', $attributes_key);
                $output .= " $attributes_key=\"$attributes_value\"";
            }
            $output .= ">\r\n";
            $array = remove_element('attributes', $array);
        }
        elseif (element('@attributes', $array))
        {
            $output .= "<$first_node ";
            foreach ($array['@attributes'] as $attributes_key => $attributes_value)
            {
                $attributes_key = str_replace('-', '_', $attributes_key);
                $output .= "$attributes_key=\"$attributes_value\" ";
            }
            $output .= ">\r\n";
            $array = remove_element('@attributes', $array);
        }
        else
        {
            $output .= "<$first_node>\r\n";
        }
        $output .= $this->_spacer($array);
        $output .= "</$first_node>\r\n";
        return $output;
    }

    private function _spacer($array, $depth = 1)
    {
        $output = '';
        foreach ($array as $key => $value)
        {
            $key = str_replace('-', '_', $key);
            $spacer = str_repeat('    ', $depth);
            if (!is_array($value))
            {
                $output .= "$spacer<$key>$value</$key>\r\n";
            }
            else
            {
                $output .= "$spacer<$key>\r\n";
                $output .= "$spacer" . $this->array_transform($value, count($value) - 1);
                $output .= "$spacer</$key>\r\n";
            }
        }
        return $output;
    }    
}

/* End of file XML.php */
/* Location: ./application/libraries/XML.php */