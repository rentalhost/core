<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	<head>
		<title><?php echo $lang->head_title(CORE_TITLE); ?> :: <?php echo CORE_VERSION; ?></title>
		<meta http-equiv="Content-type" content="text/html; charset=<?php echo $lang->head_charset; ?>" />
		<base href="<?php echo baseurl(); ?>" />
		<link href="../core/publics/default.css" rel="stylesheet" type="text/css" />
		<link href="publics/default.css" rel="stylesheet" type="text/css" />
		<link href="../core/publics/default-extra.css" rel="stylesheet" type="text/css" />
		<link href="publics/images/labs-icon-small.png" rel="shortcut icon" type="image/png" />
		<script src="../core/publics/jquery.js"></script>
		<script src="../core/publics/jquery.css.js"></script>
		<script src="../core/publics/default.js"></script>
		<script src="publics/default.js"></script>
	</head>
	<body>
		<div id="header">
			<div class="content">
				<img src="publics/images/labs-icon.png" title="Icon by Oliver Scholtz" width="50" height="50" />
				<span class="labs-title"><?php echo $lang->head_title(CORE_TITLE); ?></span>
				<span class="labs-language">
					&#40;
					<?php

						$lang_order = $lang->get_language_order();
						$core_lang = lang('/core/languages', $lang_order);

						echo "{$lang_order[0]}: ";

					?>
					<div class="button lang-change" title="<?php echo htmlspecialchars($lang->language_tooltip); ?>">
						<?php echo $core_lang->get_value($lang_order[0]); ?>
					</div>
					&#41;
				</span>
				<span class="labs-platform"><?php echo $lang->running_on(CORE_TITLE, CORE_VERSION, PHP_VERSION); ?></span>
			</div>
		</div>

		<?php

			$xdebug_enabled = extension_loaded('xdebug')
						   && xdebug_is_enabled();

		?>

		<div id="content">
			<div class="content">
				<ul id="toolbar">
					<li data-href=""><?php echo $lang->button_run; ?></li>
					<?php
						if($xdebug_enabled):
							$extra_class = isset($_GET['class']) ? 'class=' . urlencode($_GET['class']) . '&' : null;
					?>
					<li data-href="?<?php echo $extra_class; ?>coverage"><?php echo $lang->button_coverage; ?></li>
					<li data-href="?<?php echo $extra_class; ?>coverage&hidden-success" class="no-margin">[H]</li>
					<?php else: ?>
					<li class="disabled" title="<?php echo $lang->require_xdebug; ?>"><?php echo $lang->button_coverage; ?></li>
					<li class="disabled no-margin">[H]</li>
					<?php endif; ?>
					<li class="float-right accept-all disabled"><?php echo $lang->button_accept_all; ?></li>
					<li data-href="../core" class="float-right"><?php echo $lang->button_manager; ?></li>
				</ul>

				<div id="classes-realm">
					<?php

						// Se necessário, inicia o sistema de depuração
						if($xdebug_enabled === true
						&& isset($_GET['coverage'])) {
							set_time_limit(15);
							xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
						}

						// Obtém e imprime as classes diretamente no modelo
						$result = '';
						$model_attrs = array(
							'lang' => lang('models'),
							'page_lang' => $lang
						);
						foreach(call('__class::get_all') as $value) {
							$model_attrs['class'] = (object) $value;
							$result.= load('models/class', $model_attrs, true);
						}

						if($xdebug_enabled === true
						&& isset($_GET['coverage'])) {
							$coverage = xdebug_get_code_coverage();
							ksort($coverage);

							$coverage_lang = lang('coverage');

							foreach($coverage as $file => $lines) {
								$filename = core::get_path_fixed(core::get_path_clipped($file));

								if(substr($filename, 0, 5) !== 'core/'
								|| substr($file, -4) !== '.php')
									continue;

								load('coverage/class', array(
									'lang' => $coverage_lang,
									'types' => $model_attrs['lang'],
									'name' => $filename,
									'file' => $file,
									'lines' => $lines
								));
							}
						}

						echo $result;

					?>
				</div>
				<br />
			</div>
		</div>

		<div id="black-background" class="black-background"></div>
		<div id="modal-content" class="modal-content">
			<h1><?php echo $lang->language_available; ?></h1>
			<ul class="lang-list">
				<?php

					$lang_list = core_language::get_available(null, true);
					asort($lang_list);

					foreach($lang_list as $lang_id => $lang_name):
						$current_class = $lang_order[0] === $lang_id ? ' lang-current' : null;

				?>
				<li class="change-language<?php echo $current_class; ?>" data-lang-id="<?php echo $lang_id; ?>">
					<span><?php echo htmlspecialchars($lang_name); ?></span>
					<em><?php echo "({$lang_id})"; ?></em>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</body>
</html>