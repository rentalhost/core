<div class="unit-class <?php echo $class->type; ?>-type">
	<span class="strong"><?php echo $lang->strong_class; ?></span>
	<span class="name"><?php echo htmlspecialchars($class->classname); ?></span>
	<span>::</span>
	<span class="result"><?php echo $lang->get_value("type_{$class->type}"); ?></span>
	<span class="message"></span>
</div>

<div class="unit-tests <?php echo $class->type; ?>-type">
	<?php

		// Imprime informações sobre cada método da classe
		$model_attrs = array('lang' => $lang);
		foreach($class->methods as $value) {
			$model_attrs['unit'] = (object) $value;
			load('models/unit', $model_attrs);
		}

	?>
</div>

<div class="unit-footer <?php echo $class->type; ?>-type"></div>