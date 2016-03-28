<?php
/**
 * O2Bootstrap
 *
 * An open source bootstrap components factory for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, PT. Lingkar Kreasi (Circle Creative).
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
 * @package     O2Bootstrap
 * @author      Circle Creative Dev Team
 * @copyright   Copyright (c) 2005 - 2015, .
 * @license     http://circle-creative.com/products/o2bootstrap/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com/products/o2parser.html
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Bootstrap\Factory;

use O2System\Bootstrap\Interfaces\FactoryInterface;

/**
 *
 * @package Tabs
 */
class Tabs extends FactoryInterface
{
    protected $_attributes = array(
        'class' => ['nav'],
        'role' => 'tab'
    );
    protected $_nav = NULL;
    protected $_active = NULL;

    // ------------------------------------------------------------------------

    /**
     * stacked
     * @param string $attr
     * @return object
     */
    public function stacked()
    {
        $this->add_class('nav-stacked');

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * justified
     * @param string $attr
     * @return object
     */
    public function justified()
    {
        $this->add_class('nav-justified');

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * build
     * @return object
     */
    public function build( )
    {
        @list($nav,$type) = func_get_args();

        if(is_string($nav))
        {
            $nav = array($nav);
        }

        $this->_nav = $nav;

        if ( isset( $for ) )
        {
            if(! isset($this->_attributes['id']))
            {
                $this->set_id( 'nav-' . $for );
            }
        }

        if(isset($type))
        {
            $this->add_class( 'nav-' . $type);
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * __call magic method
     * @param string $method
     * @param array $args
     * @return type
     */
    public function __call($method, $args = array())
    {
        $method = $method === 'standard' ? 'tabs' : $method;

        if(method_exists($this, $method))
        {
            return call_user_func_array(array($this, $method), $args);
        }
        else
        {
            $func = array('tabs','pills');

            if(in_array($method, $func))
            {
                @list($nav) = $args;

                return $this->build($nav, $method);
            }
            else
            {
                throw new Exception("Tabs::".$method."does not Exists!!", 1);

            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Render
     *
     * @return null|string
     */
    public function render()
    {
        if ( isset( $this->_nav ) )
        {
            //--- link tab ---\\
            $ul = new Tag('ul', NULL,$this->_attributes);

            //ul open  tag
            $output[] = $ul->open();

            foreach ($this->_nav as $label => $attributes) {

                if(isset($attributes['active']))
                {
                    $li = new Tag('li',NULL,['class'=>'active']);
                }
                else
                {
                    $li = new Tag('li',NULL,[]);
                }


                $output[] =  $li->open();

                //a open tag
                $a = new Tag('a',['href'=>'#'.$label,'role'=>'tab','data-toggle'=>'tab']);
                $output[] = $a->open();

                //for icon
                if(isset($nav['icon']))
                {
                    $output[] = (new Tag('i',['class'=>$attributes['icon']]))->render();
                }

                $output[] = $label;
                //a close tag
                $output[] = $a->close();

                $output[] = $li->close();


                //for content
                $active = (isset($attributes['active'])) ? 'active in' : '';
                $div = new Tag('div',['class'=>['tab-pane','fade',$active],'id'=>$label]);

                $content[] = $div->open();

                $content[] = $attributes['content'];

                $content[] = $div->close();


            }

            $output[] = $ul->close();

            //--- end link tab ---\\

            //--- content tab ---\\

            $divcontent = new Tag('div',['class'=>'tab-content']);

            $output[] = $divcontent->open();

            $output[] = implode( PHP_EOL, $content );

            $output[] = $divcontent->close();
            //--- end content tab ---\\

            return implode( PHP_EOL, $output );
        }

        return NULL;
    }
}