<?php

	// Define as configurações padrões e salva
	$config = new stdclass;

	/** CONFIGURAÇÕES DA ROTA */
	// Configuração de rota estrita (boolean)
	// Quanto ativado, a rota deverá ser definida por completo, com classe, path e método
	$config->route_strict_mode			= false;

	// Configuração da rota padrão, quando não definida
	// Exemplo: .../site/master/index
	$config->route_default_modular		= 'site';	// string, array
	$config->route_default_controller	= 'master';	// string, array
	$config->route_default_method		= 'index';	// string

	// Configuração do idioma padrão
	// Por padrão, irá utilizar como base o idioma do accept-language
	$config->language_default_order		= 'auto, en';	// setlist, array

	// Salva as configurações na raiz
	core_config::save_configs('', $config);

	// Carrega as configurações modular
	core::do_require(CORE_MODULES . '/configs.php');
