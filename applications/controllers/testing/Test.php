<?php
namespace Applications\Controllers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

use O2System\Controller;
use O2System\Glob\ArrayObject;

class Test extends Controller
{
    public function index()
    {
        $this->view->load('testing/test');
    }

    public function ujian()
    {
        echo 'ujian';
    }

}