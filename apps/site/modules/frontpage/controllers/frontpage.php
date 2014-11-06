<?php

class Site_Frontpage extends Site_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		print_code(__CLASS__.':index');
	}

	public function testing()
	{
		print_code(__CLASS__.':testing');
	}
}