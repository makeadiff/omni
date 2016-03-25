<?php
require("common.php");

$omni = new Omni('User');
$omni->setCondition("sex", "m");
$omni->setCondition("city_id", 6);
$omni->get();


class Omni {
	public static $mode = 'd'; ///Mode - p = Production, d = Development and t = Testing (And x = disabled - nothing happens in this mode). Not fully implemented. Just used somewhere for error handling.

	private $_query = '';
	private $_select = array('id', 'name');
	private $_where = array();
	private $_table = '';

	function __construct($type = 'User') {
		if($type) $this->_table = $this->_getType($type);
	}


	function get($type = '', $result_type = 'all') {
		if($type) $this->_table = $this->_getType($type);

		$sql_query = $this->_makeQuery();

	}
	function getUsers($result_type = 'all') { $this->get("User", $result_type); }
	function getStudents($result_type = 'all') { $this->get("Student", $result_type); }
	function getCenters($result_type = 'all') { $this->get("Center", $result_type); }
	function getClasses($result_type = 'all') { $this->get("Class", $result_type); }

	function getField($fields = array()) {
		$this->_select = $fields;
	}



	function setParam() {

	}


	/**
	 * Sets the WHERE conditions for the query.  
	 * Arguments: 	$name - the field name.
	 * 				$condition - the value of the field.
	 * Example: $omni->setCondition('sex', 'm');
	 *			$omni->setCondition('sex="m"');
	 */
	function setCondition($name, $condition = '') {
		if($condition) {
			$this->_where[] = $this->_getField($name) . " = '" . $condition . "'";
		} else {
			$this->_where[] = $name;
		}
	}

	function _makeQuery() {
		$sql_query = "SELECT ";
		if($this->_select) {
			$selects = array();
			foreach ($this->_select as $key) {
				$selects[] = $this->_getField($key);
			}

			$sql_query .= implode(", ", $selects) . "\n";
		}

		if($this->_table) $sql_query .= " FROM {$this->_table}\n";
		else $this->_error("Can't figure out which table to fetch from. Make sure Omni constuctor has a table argument. For eg. <code>\$omni = new Omni('User');</code>");

		if($this->_where) {
			$sql_query .= " WHERE " . implode(" AND ", $this->_where) . "\n";
		}

		dump($sql_query);

		$this->_query = $sql_query;
		return $sql_query;
	}

	function _getField($key) {
		if(strpos($key, ".") === false) {
			$key = $this->_table . "." . $key;
		}

		return $key;
	}


	function _getType($type) {
		$return = '';
		$type = strtolower($type);
		if(		$type == 'vol' or $type == 'volunteer' or $type == 'user' 
			or 	$type == 'vols' or $type == 'volunteers' or $type == 'users') {
			$return = 'User';

		} elseif(	$type == 'kid' or $type == 'student' or $type == 'child' 
			or 		$type == 'kids' or $type == 'students' or $type == 'children') {
			$return = 'Student';

		} elseif(	$type == 'class' or $type == 'classes') {
			$return = 'Class';

		} elseif(	$type == 'center' or $type == 'centers' or $type == 'centre') {
			$return = 'Center';
		}


		if(!$return) $this->_error("Unrecogized type - $type");

		return $return;
	}

	/**
	 * Handles the errors depending on what mode we are in.
	 * Argument : $error - The error that occured.
	 */
	private function _error($error) {
		$error_message = "<p>Omni Error : <code>" . $error . "</code></p>";
		$this->error_message = $error_message;

		if(self::$mode == 'd') {
			die("Omni Error: $error_message");

		} elseif(self::$mode == 't') {
			print($error_message);
		}
	}
}


