<?php
require 'common.php';
require 'Omni.php';

$all_groups = $sql->getById("SELECT id,name FROM `Group` WHERE group_type='normal' AND status='1' ORDER BY FIELD (type, 'executive','national','strat','fellow','volunteer')");
$all_cancellation_reasons = array('in-volunteer-unavailable','in-volunteer-engaged','in-volunteer-unassigned','in-other','ext-children-out','ext-children-doing-chores','ext-children-have-events','ext-children-unwell','ext-other', 'any');

$html = new HTML;
$param = new Parameters;
// All
$param->add("format", "select", "html", array('json','csv','html'));
$param->add("data_type", "select", "volunteer", array('volunteer','student','class','center'));
$param->add("display_header", "select", '1', array('Yes', 'No'), 'select', array('options' => array('No', 'Yes')));
$param->add("type", "select", "list", array('list','count'));
$param->add("action", 'submit', 'Generate', array(), 'button', array( 'class' => 'btn btn-primary'));

// Common
$param->add("city_id", "select", "0", $all_cities, 'select', array('options' => $all_cities), 'City.id');
$param->add("center_id", "select", "0", array('Any'), 'select', array('options' => array('Any')), "Center.id");
$param->add("sex", "select", "m", array('any','m','f'), 'select', array(), "%TABLE%.sex");

// Volunteers
$param->add("user_type", "select", "volunteer", array('volunteer','alumnai','well_wisher','let_go', 'any'), 'select', array(), 'User.user_type');
$param->add("group_id", "select", "0", array(), 'select', array('multiple' => 'multiple', 'options' => $all_groups), 'UserGroup.group_id');
$param->add("joined_on", 'date', array('2015-04-01', '2016-03-31'), false, 'range', array(), 'User.joined_on');
$param->add("left_on", 'date', array('2015-04-01', '2016-03-31'), false, 'range', array(), 'User.left_on');
$param->add("credit", 'number', array('0', '3'), false, 'range', array(), 'User.credit_on');
$param->add("name", "text", '', false, 'text', array(), '%TABLE%.name');
$param->add("email", "text", '', false, 'text', array(), '%TABLE%.email');
$param->add("phone", "text", '', false, 'text', array(), '%TABLE%.phone');

// Students
$param->add("grade", "select", "5", array('5','6','7','8','9','10'), 'select', array(), 'Level.grade');
$param->add("batch_id");
$param->add("level_id");

// Class
$param->add("class_date", 'date', array('2015-04-01', '2016-03-31'), array(), 'range', array(), 'Class.class_on');
$param->add("volunteer_count", 'number', array(0,100), false, 'range');
$param->add("student_count", 'number', array(0,100), false, 'range');
$param->add("class_type", "select", "happened", array('happened','projected','cancelled', 'any'));
$param->add("cancelled_reason", "select", "any", $all_cancellation_reasons);

if(isset($QUERY['action'])) {
	$data = $param->getChanged();
	$all_params = $param->getAll();

	$checks = array();

	if(strtolower($all_params['data_type']) == 'student') $table = 'Student';
	elseif(strtolower($all_params['data_type']) == 'class') $table = 'Class';
	elseif((strtolower($all_params['data_type']) == 'user') or (strtolower($all_params['data_type']) == 'volunteer')) $table = 'User';
	elseif(strtolower($all_params['data_type']) == 'center') $table = 'Center';

	$omni = new Omni($table);

	foreach ($data as $key => $value) {
		$omni->setCondition($key, $value);
	}
	$result = $omni->get();

	dump($result);


	exit;
}
render();

class Parameters {
	public $params;

	function add($name, $data_type='text', $default_value='', $possible_values=array(), $field_type='text', $options = array(), $check='') {
		$select_options = array();

		if(isset($options['options'])) {
			$select_options = $options['options'];
		} elseif($possible_values) {
			foreach ($possible_values as $value) {
				$select_options[$value] = format($value);
			}
		}

		$this->params[$name] = array(
				'name'				=> $name,
				'data_type'			=> $data_type,
				'default_value'		=> $default_value,
				'possible_values'	=> $select_options,
				'field_type'		=> $field_type,
				'options'			=> $options,
				'check'				=> $check,
			);
	}

	function buildInput($name, $title=false) {
		global $html;

		if($title === false) {
			$title = format(str_replace(array('_id'), array(''), $name));
		}

		$select_options = array();
		if(isset($this->params[$name]['options']['multiple'])) $select_options['multiple'] = 'multiple';
		if(isset($this->params[$name]['options']['class'])) $select_options['class'] = $this->params[$name]['options']['class'];
		if($this->params[$name]['data_type'] == 'select') $select_options['options'] = $this->params[$name]['possible_values'];
		if($this->params[$name]['data_type'] == 'date') $select_options['class'] = 'date-picker';

		$default_value = '';
		if(!is_array($this->params[$name]['default_value'])) $default_value = $this->params[$name]['default_value'];

		$data_type = $this->params[$name]['data_type'];
		if($data_type == 'date') $data_type = 'text';

		if($this->params[$name]['field_type'] == 'range') {
			$select_options['class'] = 'date-picker from';
			$select_options['no_br'] = true;
			$html->buildInput($name."_from", $title . " From", $data_type, $this->params[$name]['default_value'][0], $select_options);

			$select_options['class'] = 'date-picker to';
			unset($select_options['no_br']);
			$html->buildInput($name."_to", "To", $data_type, $this->params[$name]['default_value'][1], $select_options);
		} else {
			if(isset($select_options['multiple'])) $name = $name . '[]';
			$html->buildInput($name, $title, $data_type, $default_value, $select_options);
		}
	}

	function getChanged() {
		$returns = array();

		foreach ($this->params as $key => $data) {
			if($data['field_type'] == 'range') {
				$default_from = $data['default_value'][0];
				$default_to = $data['default_value'][1];

				$from = $this->_getValue($key.'_from', $data, 'from');
				$to = $this->_getValue($key.'_to', $data, 'to');

				if($from != $default_from) $returns[$key.'_from'] = $from;
				if($to != $default_to) $returns[$key.'_to'] = $to;

			} else {
				$value = $this->_getValue($key, $data);
				if($value != $data['default_value']) $returns[$key] = $value;
			}
		}

		return $returns;
	}

	function getAll() {
		$returns = array();

		foreach ($this->params as $key => $data) {
			if($data['field_type'] == 'range') {
				$returns[$key.'_from'] = $this->_getValue($key.'_from', $data, 'from');
				$returns[$key.'_to'] = $this->_getValue($key.'_to', $data, 'to');

			} else {
				$returns[$key] = $this->_getValue($key, $data);
			}
		}

		return $returns;
	}

	function _getValue($key, $data, $from_to = '') {
		global $QUERY;
		$from_to_array = array('from' => 0, 'to' => 1);

		$default_value = $data['default_value'];
		if($from_to) $default_value = $data['default_value'][$from_to_array[$from_to]];

		$value = i($QUERY, $key, $default_value);
		// if(!in_array($value, $data['possible_values'])) $value = $data['default_value']; // Check if the given value is in the possible values array. If not there, get default value
		return $value;
	}
}
