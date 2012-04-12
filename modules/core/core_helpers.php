<?php

	/** HELPERS DE SERVIDOR */
	// Obtém o URL base
	function baseurl($include_modular = true) {
		return core::get_baseurl($include_modular);
	}

	// Verifica se é o domínio informado
	function is_domain($domains) {
		return core::is_domain($domains);
	}

	// Verifica se é um domínio local
	function is_localhost() {
		return core::is_localhost();
	}

	/** HELPERS DE CONTEÚDO */
	// Carrega um core_view
	function load($view_path, $view_args = null, $cancel_print = false) {
		return new core_view($view_path, $view_args, $cancel_print);
	}

	// Carrega um core_controller
	function execute($controller_path, $cancel_print = false, $auto_execute = true) {
		return core_controller::_create_controller($controller_path, $cancel_print, $auto_execute, true);
	}

	/** HELPERS DE CHAMADA */
	// Executa a chamada de um helper ou library
	function call($command = null) {
		// Se nenhum comando for definido, retorna o objeto de chamada
		if($command === null)
			return core_caller::get_instance();

		// Caso contrário, faz a chamada, passando seus parãmetros
		$args = func_get_args();
		return core_caller::do_call($command, array_slice($args, 1));
	}

	/** HELPERS DE LIBRARY */
	// Carrega uma biblioteca
	function library($libraries) {
		foreach(setlist($libraries) as $library)
			core_caller::load_library($library);
		return true;
	}

	// Carrega um helper
	function helper($helpers) {
		foreach(setlist($helpers) as $helper)
			core_caller::load_helper($helper);
		return true;
	}

	/** HELPERS DE DADOS */
	// Converte uma string separada por vírgula em um array
	function setlist($data) {
		return core::parse_setlist($data);
	}

	// Cria uma mensagem rápida, retornando um objeto core_message
	function message($message = null, $type = null, $code = null) {
		$obj = new core_message($type);

		if($message !== null) {
			$obj->push($message, null, $code);
		}

		return $obj;
	}

	/** HELPERS DE BANCO DE DADOS */
	// Cria uma nova configuração de banco de dados
	function create_connection($connection_string, $index_name = null) {
		return core_database::_create_connection($connection_string, $index_name);
	}

	// Obtém a conexão atual
	function connection($index_name = null, $conn_path = null) {
		return core_database::_get_connection($conn_path, $index_name);
	}

	// Obtém um modelo
	function model($model_path, $load_id = null, $conn = null) {
		return core_model::_create_row($conn ? $conn : connection(), $model_path, $load_id);
	}

	/** HELPERS DE CONFIGURAÇÃO */
	// Obtém uma configuração rapidamente, usando a modular atual
	function config($key, $default_value = null) {
		return core_config::get_config(null, $key, $default_value);
	}

	/** HELPERS DE IDIOMA */
	// Obtém um controlador de idiomas
	function lang($path, $lang_order = null) {
		return new core_language($path, $lang_order);
	}

	/** HELPERS DE SESSÃO */
	// Obtém um objeto de sessão
	function &session($index_name = '', $set_value = -1) {
		return core::get_session($index_name, $set_value);
	}
