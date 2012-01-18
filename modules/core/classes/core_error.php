<?php

	// Esta classe permite controlar os erros gerados pelo core, assim como lançar exceções
	class core_error {
		// Armazena o código do erro
		private	$_error_code;
		// Armazena o backtrace
		private $_backtrace;
		// Armazena os dados globais
		private $_globals;
		// Armazena parâmetros adicionais e de cache
		private $_args;
		// Armazena o ID único, baseado no cache_args
		private $_id;

		// Armazena se é um erro crítico
		// Se não for crítico, lança uma exceção apenas
		private $_is_fatal = false;
		// Armazena se é necessário um log individual
		private $_enable_individual_log = false;

		// Cria um novo erro
		public function __construct($error_code, $args = null, $cache_args = array()) {
			$this->_error_code = str_pad(strtolower($error_code), 4, '0', STR_PAD_LEFT);
			$this->_backtrace = debug_backtrace();
			$this->_globals = $GLOBALS;
			$this->_args = array_merge((array) $args, $cache_args);
			$this->_id = md5("{$error_code}:" . json_encode($cache_args));
		}

		// Define o erro como fatal
		public function set_fatal($is_fatal = true) {
			$this->_is_fatal = $is_fatal;

			if($is_fatal === true)
				$this->_enable_individual_log = true;

			return $this;
		}

		// Define se é necessário registrar ocorrência individual
		public function set_individual_log($mode = true) {
			$this->_enable_individual_log = $mode;
			return $this;
		}

		// Lança um erro
		//TODO: registrar a ocorrência
		public function run() {
			// Se não for crítico, apenas lança o erro como exception
			if($this->_is_fatal === false) {
				throw new core_exception(null, $this->_error_code);
				return;
			}
		}
	}
