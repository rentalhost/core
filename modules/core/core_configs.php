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

	/** CONFIGURAÇÕES DE LINGUAGEM */
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

	// Alterar sessão automaticamente quando usar a request key
	//NOTE: altere a informação para false para desabilitar a função
	$config->language_session_autosync	= true;

	/** CONFIGURAÇÕES DE BANCO DE DADOS */
	// Permitir conexão preguiçosa: conectar somente quando for necessário.
	$config->database_lazy_mode			= true;		// boolean
	// Usar conexões persistentes por padrão
	$config->database_persistent_mode	= true;		// boolean
	// Charset padrão para ser utilizado
	$config->database_default_charset	= 'utf8';	// string

	/** CONFIGURAÇÕES DE SESSÃO */
	// Prefixar sessões (isto evita conflito de sessões)
	//NOTE: altere a informação para true para prefixar automaticamente
	//NOTE: altere a informação para false para desabilitar a função
	$config->session_prefix = true;
	// Ao prefixar automaticamente, também incluir a modular
	$config->session_prefix_modular = false;

	// Salva as configurações na raiz
	core_config::save_configs('', $config);

	// Carrega as configurações modular
	core::do_require(CORE_MODULES . '/configs.php');
