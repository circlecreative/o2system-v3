<?php 

class CMS_Dashboard_Model extends CMS_Model
{
	public function __construct()
	{
		parent::__construct();
		print_lines(__CLASS__.' Loaded Successfull');
	}
}