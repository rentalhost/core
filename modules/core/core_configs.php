<?php

	// Define as configurações padrões e salva
	$config = new stdclass;
	$config->route_default_modular		= 'site';	// string, array
	$config->route_default_controller	= 'master';	// string, array
	$config->route_default_method		= 'index';	// string

	// Salva as configurações na raiz
	core_config::save_configs('', $config);

	// Carrega as configurações modular
	core::do_require(CORE_MODULES . '/configs.php');
