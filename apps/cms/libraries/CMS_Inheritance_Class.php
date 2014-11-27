<?php

class Cms_Inheritance_Class extends App_Inheritance_Class
{
	public function __construct()
	{
		parent::__construct();
		print_lines(__CLASS__.' Loaded Successfull');
	}
}