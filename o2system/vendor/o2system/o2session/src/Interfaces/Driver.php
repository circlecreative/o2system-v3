<?php
/**
 * O2Session
 *
 * An open source Session Management Library for PHP 5.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, .
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
 * @package        O2System
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2005 - 2014, .
 * @license        http://o2system.in/features/o2session/license
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://o2system.in
 * @filesource
 */

// ------------------------------------------------------------------------

namespace O2System\Session\Interfaces;

// ------------------------------------------------------------------------

/**
 * CodeIgniter Session Driver Class
 *
 * @package       CodeIgniter
 * @subpackage    Libraries
 * @category      Sessions
 * @author        Andrey Andreev
 * @link          http://codeigniter.com/user_guide/libraries/sessions.html
 */
abstract class Driver implements \SessionHandlerInterface
{
    /**
     * Session Driver Configuration
     *
     * @access  protected
     * @type    array
     */
    protected $_config;

    /**
     * Session Driver Resource Handle
     *
     * @access  protected
     * @type    resource
     */
    protected $_handle;

    /**
     * Data fingerprint
     *
     * @access  protected
     * @type    bool
     */
    protected $_fingerprint;

    /**
     * Lock placeholder
     *
     * @access  protected
     * @type    mixed
     */
    protected $_lock = FALSE;

    /**
     * Read session ID
     *
     * Used to detect session_regenerate_id() calls because PHP only calls
     * write() after regenerating the ID.
     *
     * @access  protected
     * @type    string
     */
    protected $_session_id;

    // ------------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @param    array $params Configuration parameters
     */
    public function __construct( &$params )
    {
        $this->_config =& $params;
    }

    // ------------------------------------------------------------------------

    /**
     * Cookie destroy
     *
     * Internal method to force removal of a cookie by the client
     * when session_destroy() is called.
     *
     * @access  protected
     * @return  bool
     */
    protected function _cookie_destroy()
    {
        return setcookie(
            $this->_config[ 'cookie' ][ 'name' ],
            NULL,
            1,
            $this->_config[ 'cookie' ][ 'path' ],
            $this->_config[ 'cookie' ][ 'domain' ],
            $this->_config[ 'cookie' ][ 'secure' ],
            TRUE
        );
    }

    // ------------------------------------------------------------------------

    /**
     * Get lock
     *
     * A dummy method allowing drivers with no locking functionality
     * (databases other than PostgreSQL and MySQL) to act as if they
     * do acquire a lock.
     *
     * @param   string  $session_id
     *
     * @access  protected
     * @return  bool
     */
    protected function _get_lock( $session_id )
    {
        $this->_lock = TRUE;

        return TRUE;
    }

    // ------------------------------------------------------------------------

    /**
     * Release lock
     *
     * @access  protected
     * @return  bool
     */
    protected function _release_lock()
    {
        if( $this->_lock )
        {
            $this->_lock = FALSE;
        }

        return TRUE;
    }

}