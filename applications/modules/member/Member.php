<?php
namespace Applications\Modules\Controllers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

use O2System\Controller;
use O2System\Glob\ArrayObject;

class Test extends Controller
{
    public function index()
    {
        echo 'sub test';
    }
}