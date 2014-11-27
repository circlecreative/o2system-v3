<?php

class App_Inheritance_Class extends O2_Inheritance_Class
{
	public function __construct()
	{
		parent::__construct();
		print_lines(__CLASS__.' Library Loaded Successfull');
	}
}