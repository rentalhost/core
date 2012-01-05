<?php

	$accepted_lines = 1;
	$total_lines = 1;
	$result = load('coverage/file', array(
		'name' => $name,
		'file' => $file,
		'lines' => $lines,
		'accepted_lines' => &$accepted_lines,
		'total_lines' => &$total_lines
	), true);

	$percentage = number_format(100 / $total_lines * $accepted_lines, 2, '.', '');
	$type = $percentage === '100.00' ? 'success' : 'failed';

?>

<div class="unit-class <?php echo $type; ?>-type" data-percentage="<?php echo $percentage; ?>">
	<span class="strong">arquivo</span>
	<span class="name"><?php echo $name; ?></span>
	<span>::</span>
	<span class="result"><?php echo $type; ?></span>
	<span class="message">(<?php echo $percentage; ?>%)</span>
</div>

<div class="unit-tests <?php echo $type; ?>-type">
	<?php echo $result; ?>
</div>

<div class="unit-footer <?php echo $type; ?>-type"></div>