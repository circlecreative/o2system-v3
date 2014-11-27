<?php

class System_Model extends O2_Model
{
	public function __construct()
	{
		parent::__construct();
		print_lines(__CLASS__.' Loaded Successfull');
	}
}