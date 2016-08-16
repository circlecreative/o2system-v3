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

namespace Wiki\Controllers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core\Controller;

/**
 * Welcome Controller
 *
 * @package       Applications
 * @subpackage    Controllers
 * @category      Global Controller
 *
 * @version       1.0.0
 */
class Wiki extends Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->view->theme->set( FALSE );
	}

    public function index()
    {
        $wiki = new \O2System\Template\Factory\Wiki;
        $wiki->load( $this->request->directories->current() . 'pages/');

        $this->view->load( 'wiki', [ 'wiki' => $wiki->tocify() ] );
    }

}