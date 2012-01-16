<?php

	// Carrega o arquivo e quebra por linhas
	$file_content = preg_split('/\r?\n/', file_get_contents($file));
	$file_hex = str_pad(dechex(crc32($name)), 8, '0', STR_PAD_LEFT);

?>
<ul class="file-lines" data-file="<?php echo $file_hex; ?>">
	<?php

		$ignored_lines = core::get_current_path() . "/files/{$file_hex}.lines";
		$ignored_lines = is_file($ignored_lines)
					   ? json_decode(file_get_contents($ignored_lines), true)
					   : array();

		foreach($file_content as $line => $content):
			$line++;

			$class = null;
			$data = null;
			$ignorable = false;

			if(isset( $lines[$line] )) {
				$total_lines++;
				switch($lines[$line]) {
					case 1:
						$class = 'coverage-on';
						$accepted_lines++;
						break;
					case -1:
						$class = 'coverage-off';
						$ignorable = true;

						$data_md5 = md5(join("\n", array_slice($file_content, $line - 2, 5)));
						$data = ' data-line="' . $line . '" data-content="' . $data_md5 . '"';

						if(isset($ignored_lines[$data_md5])) {
							$class.= ' coverage-ignored';
							$accepted_lines++;
						}
						break;
					case -2:
						$class = 'coverage-dead';
						$accepted_lines++;
						break;
				}
			}

			if($class !== null)
				$class = " class=\"{$class}\"";

	?>
	<li<?php echo $class . $data; ?>>
		<span><?php echo htmlspecialchars($content); ?></span>
		<?php if($ignorable === true): ?>
		<span class="button ignore-button"><?php echo $lang->button_ignore; ?></span>
		<span class="button recovery-button" style="display: none;"><?php echo $lang->button_recovery; ?></span>
		<?php endif; ?>
	</li>
	<?php endforeach; ?>
</ul>