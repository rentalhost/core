<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	<head>
		<title>core laboratório :: <?php echo CORE_VERSION; ?></title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<base href="<?php echo get_baseurl(); ?>" />
		<link href="publics/default.css" rel="stylesheet" type="text/css" />
		<script src="publics/jquery-1.7.js"></script>
		<script src="publics/jquery.css-1.45.js"></script>
		<script src="publics/default.js"></script>
	</head>
	<body>
		<div id="header">
			<div class="content">
				<img src="publics/images/labs-icon.png" title="Icone por Oliver Scholtz" width="50" height="50" />
				<span class="labs-title">core laboratório</span>
				<span class="labs-platform">rodando o <strong>core <?php echo CORE_VERSION; ?></strong> no <strong>PHP <?php echo PHP_VERSION; ?></strong></span>
			</div>
		</div>

		<div id="content">
			<div class="content">
				<ul id="toolbar">
					<li data-href="?default">Executar</li>
					<?php if(extension_loaded('xdebug')): ?>
					<li data-href="?coverage">Code Coverage</li>
					<li data-href="?coverage&hidden-success" class="no-margin">[H]</li>
					<?php else: ?>
					<li class="disabled">Code Coverage</li>
					<li class="disabled no-margin">[H]</li>
					<?php endif; ?>
				</ul>

				<div id="classes-realm">
					<?php

						$xdebug_enabled = function_exists('xdebug_start_code_coverage');

						// Se necessário, inicia o sistema de depuração
						if($xdebug_enabled === true
						&& isset($_GET['coverage']))
							xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);

						// Obtém e imprime as classes diretamente no modelo
						$result = '';
						foreach(call('__class::get_all') as $value) {
							$result.= load('models/class', $value, true);
						}

						if($xdebug_enabled === true
						&& isset($_GET['coverage'])) {
							$coverage = xdebug_get_code_coverage();
							ksort($coverage);

							foreach($coverage as $file => $lines) {
								$filename = core::get_path_fixed(core::get_path_clipped($file));

								if(substr($filename, 0, 5) !== 'core/')
									continue;

								load('coverage/class', array(
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
	</body>
</html>