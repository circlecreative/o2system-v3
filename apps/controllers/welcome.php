<?php

class App_Welcome extends App_Controller 
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		echo 'Welcome to O2App';
	}
}