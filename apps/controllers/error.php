<?php

class App_Error extends App_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index($code = '404')
	{
		print_code(__CLASS__.':404');
	}
}