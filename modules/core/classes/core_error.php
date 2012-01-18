<?php

	// Esta classe permite controlar os erros gerados pelo core, assim como lançar exceções
	class core_error {
		// Armazena o código do erro
		private	$_error_code;
		// Armazena o backtrace
		private $_backtrace;
		// Armazena os dados globais
		private $_globals;
		// Armazena parâmetros adicionais
		private $_args;
		// Armazena o ID único, baseado no cache_args
		private $_id;

		// Cria um novo erro
		public function __construct($error_code, $args = array(), $cache_args = null) {
			$this->_error_code = str_pad(strtolower($error_code), 4, '0', STR_PAD_LEFT);
			$this->_backtrace = debug_backtrace();
			$this->_globals = $GLOBALS;
			$this->_args = $args;

			$cache_args = $cache_args === null ? $args : $cache_args;
			$this->_id = "errx{$this->_error_code}_" . crc32(json_encode($cache_args));
		}

		// Renderiza um erro
		public function render() {

		}
	}
