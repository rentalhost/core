<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	<head>
		<title><?php echo $lang->head_title(CORE_TITLE); ?> :: <?php echo CORE_VERSION; ?></title>
		<meta http-equiv="Content-type" content="text/html; charset=<?php echo $lang->head_charset; ?>" />
		<base href="<?php echo baseurl(); ?>" />
		<link href="publics/default.css" rel="stylesheet" type="text/css" />
		<link href="publics/default-extra.css" rel="stylesheet" type="text/css" />
		<link href="publics/images/panel-icon-small.png" rel="shortcut icon" type="image/png" />
		<script src="publics/jquery.js"></script>
		<script src="publics/jquery.css.js"></script>
		<script src="publics/default.js"></script>
	</head>
	<body>
		<div id="header">
			<div class="content">
				<img src="publics/images/panel-icon.png" title="Icon by Gnome Project" width="50" height="50" />
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

		<div id="content">
			<div class="content">
				<ul id="toolbar">
					<li data-href="./list_errors"><?php echo $lang->button_errors; ?></li>
				</ul>

				<br />
			</div>
		</div>

		<div id="black-background" class="black-background"></div>
		<div id="modal-content" class="modal-content">
			<h1><?php echo $lang->language_available; ?></h1>
			<ul class="lang-list">
				<?php

					$lang_list = core_language::get_available('/core', true);
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