<?php

class Frontpage_Inheritance_Class extends Site_Inheritance_Class
{
	public function __construct()
	{
		parent::__construct();
		print_lines(__CLASS__.' Library Loaded Successfull');
	}
}