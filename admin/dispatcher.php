<?php
// Protect from unauthorized access
defined('_JEXEC') or die();

class AdvancedmenusDispatcher extends FOFDispatcher
{
	public function __construct($config = array()) 
	{
		$this->defaultView = 'menus';
			
		parent::__construct($config);
	}
}