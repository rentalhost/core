<?php

	if(isset($_GET['class'])
	&& $_GET['class'] !== substr($name, 13, -4)) {
		$type = 'unavailable';
		$percentage = '0.00';
		$result = null;
	}
	else {
		$accepted_lines = 1;
		$total_lines = 1;
		$result = load('coverage/file', array(
			'lang' => $lang,
			'name' => $name,
			'file' => $file,
			'lines' => $lines,
			'accepted_lines' => &$accepted_lines,
			'total_lines' => &$total_lines
		), true);

		$percentage = number_format(100 / $total_lines * $accepted_lines, 2, '.', '');
		$type = $percentage === '100.00' ? 'success' : 'failed';
	}

	if($type !== 'failed'
	&& isset($_GET['hidden-success']) === true)
		return true;

?>

<div class="unit-class <?php echo $type; ?>-type file-coverage" data-percentage="<?php echo $percentage; ?>">
	<span class="strong"><?php echo $lang->strong_file; ?></span>
	<span class="name"><?php echo $name; ?></span>
	<span>::</span>
	<span class="result"><?php echo $types->get_value("type_{$type}"); ?></span>
	<span class="message">(<?php echo $percentage; ?>%)</span>
</div>

<div class="unit-tests <?php echo $type; ?>-type">
	<?php echo $result; ?>
</div>

<div class="unit-footer <?php echo $type; ?>-type"></div>