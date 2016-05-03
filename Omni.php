<?php
require_once("common.php");

// $omni = new Omni('User');
// $omni->getFields("id", "name", "email", "phone", "Center.name AS center_name");
// $omni->setCondition("sex", "m");
// $omni->setCondition("city_id", 6);

// $omni = new Omni("Class");
// $omni->getFields("COUNT(Class.id) AS count");

// // Period- Oct to March
// // Number of classes scheduled to happen
// $omni->setCondition("Class.class_on BETWEEN '2015-10-01 00:00:00' AND '2016-03-31 23:59:59'");

// // Number of classes marked
// $omni->setCondition("Class.status != 'projected'");

// // Number of classes marked cancelled
// // $omni->setCondition("Class.status = 'cancelled'");

// // dump($omni->dbtable->getSqlQuery());
// $count = $omni->get('one');
// dump($count);

// $data = $omni->get(); dump($data);


class Omni {
	public static $mode = 'd'; ///Mode - p = Production, d = Development and t = Testing (And x = disabled - nothing happens in this mode). Not fully implemented. Just used somewhere for error handling.
	public $dbtable;
	public $year = 2015;

	private $_table;

	function __construct($type = 'User') {
		if($type == 'User' or $type == 'Student' or $type == 'Class' or $type == 'Center') {
			$this->_table = $type;
		} else $this->_error("No data type called '$type'. Should be User, Student, Class or Center");

		$this->dbtable = new DBTable($type);
		$this->dbtable->select("$type.*");
	}

	function get($result_type = 'all') {
		return $this->dbtable->get($result_type);
	}

	function getFields() {
		$args = func_get_args();
		if($this->_table == 'User') {
			foreach ($args as $arg) {
				if(strpos($arg, "Center.name") !== false OR strpos($arg, "`Center`.name") !== false) {
					$this->_connectTable('Center');
				}
			}
		}

		$this->dbtable->select($args);
	}
	function sort() {
		$this->dbtable->sort(func_get_args());
	}

	function limit($limit, $offset=0) {
		if(is_numeric($limit) and is_numeric($offset))
			$this->dbtable->limit($limit, $offset);
		else $this->_error("Both arguments of limit($limit, $offset) should be numeric.");
	}



	/**
	 * Sets the WHERE conditions for the query.  
	 * Arguments: 	$name - the field name.
	 * 				$condition - the value of the field.
	 * Example: $omni->setCondition('sex', 'm');
	 *			$omni->setCondition('sex="m"');
	 */
	function setCondition($name, $condition = '') {
		if($condition) $this->dbtable->where(array($this->_getField($name) => $condition));
		else $this->dbtable->where($name);
	}
	function setConditionAny($name, $possibile_values) {
		$conditions = array();
		foreach ($possibile_values as $value) {
			$conditions[] = "`$name` = '$value'";
		}

		$this->where("(" . implode(" AND ", $conditions) . ")");
	}
	function where() {
		$this->dbtable->where(func_get_args());
	}

	function _getField($key) {
		if(strpos($key, ".") === false and strpos($key, ' ') === false and strpos($key, '`') === false) {
			$key = "`$this->_table`.`$key`";
		}

		return $key;
	}

	function _connectTable($far_table) {
		if($this->_table == 'User') {
			if($far_table == 'Center' or $far_table == 'Batch' or $far_table == 'Level')
			$this->dbtable->join("UserBatch", "User.id = UserBatch.user_id");

			if($far_table == 'Level') {
				$this->dbtable->join("Level", "Level.id = UserBatch.level_id");
				if($this->year) $this->dbtable->where("Level.year = $this->year");
			} else {
				$this->dbtable->join("Batch", "Batch.id = UserBatch.batch_id");
				if($this->year) $this->dbtable->where("Batch.year = $this->year");
				if($far_table == 'Center') $this->dbtable->join("Center", "Center.id = Batch.center_id");
			}
		}
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


