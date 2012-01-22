<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	<head>
		<title><?php echo $lang->head_title; ?></title>
		<meta http-equiv="Content-type" content="text/html; charset=<?php echo $lang->head_charset; ?>" />
		<base href="<?php echo baseurl(); ?>" />
		<link href="publics/default.css" rel="stylesheet" type="text/css" />
		<link href="publics/default-extra.css" rel="stylesheet" type="text/css" />
		<link href="publics/default-error.css" rel="stylesheet" type="text/css" />
		<link href="publics/images/error-icon-small.png" rel="shortcut icon" type="image/png" />
		<script src="publics/jquery.js"></script>
		<script src="publics/jquery.css.js"></script>
		<script src="publics/default.js"></script>
	</head>
	<body>
		<div id="header">
			<div class="content">
				<img src="publics/images/error-icon.png" title="Icon by Gnome Project" width="50" height="50" />
				<span class="labs-title"><?php echo $lang->head_title; ?></span>
			</div>
		</div>

		<div id="content">
			<div class="content">
				<h1><?php echo $lang->error_title; ?></h1>
				<p><?php echo $lang->error_message_1; ?></p>
				<p><?php echo $lang->error_message_2; ?></p>
				<br />

				<h2><?php echo $lang->error_what_now; ?></h2>
				<p><?php echo $lang->error_message_3; ?></p>
				<p><?php echo $lang->error_message_4; ?></p>
				<br />

				<h2><?php echo $lang->form_title; ?></h2>
				<p><?php echo $lang->form_message; ?></p>
				<form method="post" class="nice">
					<strong><?php echo $lang->form_email; ?></strong>
					<input type="text" name="form_email" size="40" /><br />

					<strong><?php echo $lang->form_textarea; ?></strong>
					<textarea name="form_message" rows="4" cols="80"></textarea><br />

					<strong><?php echo $lang->form_code; ?></strong>
					<input type="text" name="form_error" readonly="readonly" value="<?php echo $error_code; ?>" size="20" /><br />

					<strong>&nbsp;</strong>
					<input type="submit" value="<?php echo $lang->form_submit; ?>" />
				</form>
				<br />
			</div>
		</div>
	</body>
</html>