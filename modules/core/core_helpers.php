<?php

	/** HELPERS DE URL */

	// Obtém o URL base
	function get_baseurl( $include_modular = true ) {
		return core::get_baseurl( $include_modular );
	}

	/** HELPERS DE CONTEÚDO */

	// Carrega um core_view
	function load( $view_path, $view_args = null, $cancel_print = false ) {
		return new core_view( $view_path, $view_args, $cancel_print );
	}

	/** HELPERS DE CHAMADA */

	// Executa a chamada de um helper ou library
	function call( $command = null ) {
		// Se nenhum comando for definido, retorna o objeto de chamada
		if( $command === null ) {
			return core_caller::get_instance();
		}

		// Caso contrário, faz a chamada, passando seus parãmetros
		$args = func_get_args();
		return core_caller::do_call( $command, array_slice( $args, 1 ) );
	}

	/** HELPERS DE LIBRARY */

	// Carrega uma biblioteca
	//TODO: usar setlist para carregar mais de uma library
	function library( $libraries ) {
		return core_caller::load_library( $libraries );
	}
