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

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * O2System Object Helpers
 *
 * @package     O2System
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Steeven Andrian Salim
 * @link        http://circle-creative.com/products/o2system/user-guide/helpers/array.html
 */

// ------------------------------------------------------------------------

if (!function_exists('object_element'))
{
    /**
     * Object Element
     *
     * Lets you determine whether an object element is set and whether it has a value.
     * If the element is empty it returns FALSE (or whatever you specify as the default value.)
     *
     * @param   string
     * @param   array
     * @param   mixed
     * @return  mixed   depends on what the array contains
     */
    function object_element($item, $object, $default = false)
    {
        if (is_object($object))
        {
            $obj = get_object_vars($object);
            if (element($item, $obj))
            {
                return $object->$item;
            }
        }
        return $default;
    }
}
// ------------------------------------------------------------------------

if (!function_exists('object_to_array'))
{
    /**
     * Object to Array
     *
     * Convert multidimensional object into multidimensional array
     *
     * @param   object
     * @return  array depends on what the object contains
     */
    function object_to_array($object)
    {
        $array = array();
        
        if (is_object($object))
        {
            $object = (array) $object;
        }
        
        if (is_array($object))
        {
            foreach ($object as $keys => $value)
            {
                $keys = preg_replace("/^\\0(.*)\\0/", "", $keys);
                $keys = str_replace('@', '', $keys);
                $array[$keys] = object_to_array($value);
            }
        }
        else
        {
            $array = $object;
        }
        return $array;
    }
}
// ------------------------------------------------------------------------

/* End of file object_helper.php */
/* Location: ./system/helpers/object_helper.php */