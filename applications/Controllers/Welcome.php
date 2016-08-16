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

namespace Applications\Controllers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core\Controller;
use O2System\Core\SPL\ArrayObject;

/**
 * Welcome Controller
 *
 * @package       Applications
 * @subpackage    Controllers
 * @category      Global Controller
 *
 * @version       1.0.0
 */
class Welcome extends Controller
{
	public function index()
	{
		$this->view->theme->set( FALSE );
		$this->view->vars->title->set( 'Welcome to O2System PHP Framework' );

		$this->view->load(
			'welcome', [
			'file' => new ArrayObject(
				[
					'view'       => $this->request->directories->current() . 'Views' . DIRECTORY_SEPARATOR . 'welcome.php',
					'controller' => __FILE__,
				] ),
		] );
	}

}