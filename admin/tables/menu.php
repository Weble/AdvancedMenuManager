<?php

class AdvancedmenusTableMenu extends FOFTable {
	/**
	 * Override table name
	 */
	function __construct($table, $key, &$db) {
		parent::__construct($table, $key, $db);

		$this->_tbl = '#__menu_types';
		$this->_tbl_key = 'id';
	}
}