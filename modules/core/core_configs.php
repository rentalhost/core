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
	//NOTE: use "auto" para obter a configuração de accept-languages
	//NOTE: use "request" para obter a configuração da variável $_REQUEST (como $_GET e $_POST)
	//NOTE: use "session" para obter a configuração da sessão do usuário
	$config->language_default_order		= 'request, session, auto, en';	// setlist, array

	// Alterar a linguagem através de $_REQUEST
	//NOTE: altere a informação para false para desabilitar a função
	$config->language_request_key		= 'language-id'; // string, false

	// Chave de armazenamento da linguagem na sessão
	//NOTE: altere a informação para false para desabilitar a função
	$config->language_session_key		= 'language-id'; // string, false

	// Salva as configurações na raiz
	core_config::save_configs('', $config);

	// Carrega as configurações modular
	core::do_require(CORE_MODULES . '/configs.php');
