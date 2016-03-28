<?php
/**
 * YukBisnis.com
 *
 * Application Engine under O2System Framework for PHP 5.4 or newer
 *
 * This content is released under PT. Yuk Bisnis Indonesia License
 *
 * Copyright (c) 2015, PT. Yuk Bisnis Indonesia.
 *
 * @package        Applications
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2015, PT. Yuk Bisnis Indonesia.
 * @since          Version 2.0.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace Apps\Controllers;
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

/**
 * Welcome Controller
 *
 * @package       Applications
 * @subpackage    Controllers
 * @category      Global Controller
 *
 * @version       1.0.0
 * @author        Steeven Andrian Salim
 */
class Welcome extends \O2System\Core\Controller
{
    protected function __reconstruct()
    {
        parent::__reconstruct();
    }

    public function index()
    {
        $this->load->view( 'welcome', array(
            'controller' => array(
                'view' => 'applications/views/welcome.tpl',
                'file' => 'applications/controllers/Welcome.php'
            )
        ) );
    }

}