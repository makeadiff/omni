<?php
require 'common.php';
require '../../exdon/includes/classes/API.php';
require '../Omni.php';

header("Access-Control-Allow-Origin: *");

$sql->options['error_handling'] = 'die';
$api = new API;


$api->request("/user/search", function () {
	global $QUERY;
	$omni = new Omni('User');
	// Default Conditions... :TODO: Need better way to do this.
	$omni->setCondition('user_type', 'volunteer');
	$omni->setCondition('status', '1');
	$omni->sort('city_id', 'name');

	$omni->getFields('id', 'name', 'email', 'phone', 'joined_on', 'address');

	$allowed_fields_for_search = array('name','email','phone','city_id', 'user_type', 'vertical_id'); // :TODO: Add more

	foreach($QUERY as $key => $value) {
		if(in_array($key, $allowed_fields_for_search)) {
			$omni->setCondition($key, $value);

			if($key == 'user_type' and $value == 'applicant') {
				$omni->sort("joined_on DESC");
			}
		}
	}

	// DBTable::$mode = 'd';
	// $limit = i($QUERY, 'limit', 100);
	// $offset = i($QUERY, 'offset', 0);
	// $omni->limit($limit, $offset);

	$return_type = i($QUERY, 'return_type', 'list');
	$return_format = 'all';
	if($return_type == 'count') {
		$omni->setReturnType($return_type);
		$return_format = 'one';
	}

	$return = $omni->get($return_format);

	showSuccess("Data fetched.", array('data' => $return));
});

$api->handle();


function showSuccess($message, $extra = array()) {
	showSituation('success', $message, $extra);
}
function showError($message, $extra = array()) {
	showSituation('error', $message, $extra);
}

function showSituation($status, $message, $extra) {
	$other_status = ($status == 'success') ? 'error' : 'success';
	$return = array($status => true, $other_status => false);

	if(is_string($message)) {
		$return[$status] = $message;

	} elseif(is_array($message)) {
		$return = array_merge($return, $message);
	} 

	$return = array_merge($return, $extra);

	print json_encode($return);
}