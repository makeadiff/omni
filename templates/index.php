<?php
$centers = array('0' => array('Any'));
foreach ($all_centers as $this_center_id => $center) {
	if(!isset($centers[$center['city_id']])) {
		$centers[$center['city_id']] = array('0' => 'Any');
	}
	$centers[$center['city_id']][$this_center_id] = $center['name'];
}
?>
<script type="text/javascript">
var centers = <?php echo json_encode($centers); ?>;
</script>

<h1 class="title">Volunteers</h1>

<form action="" method="post" class="form-area">
<table id="options-area">
<tr><td>
<?php
$param->buildInput("city_id");
$param->buildInput("center_id");
$param->buildInput("sex");

// Volunteers
$param->buildInput("user_type");
$param->buildInput("group_id");
$param->buildInput("joined_on");
$param->buildInput("left_on");
$param->buildInput("credit");
?>
</td><td>
<?php
$param->buildInput("name");
$param->buildInput("email");
$param->buildInput("phone");
?>
</td><td>
<?php
$param->buildInput("format");
$param->buildInput("data_type");
$param->buildInput("display_header");
$param->buildInput("type");
$param->buildInput("action", '&nbsp;');
?>
</td></tr></table>

</form>