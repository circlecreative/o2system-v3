<?php

class Dashboard_Inheritance_Class extends CMS_Inheritance_Class
{
	public function __construct()
	{
		parent::__construct();
		print_lines(__CLASS__.' Loaded Successfull');
	}
}