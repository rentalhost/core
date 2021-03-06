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
		// Armazena se é necessário registrar a ocorrência
		private $_enable_log = true;
		// Armazena se é necessário um log individual
		private $_enable_individual_log = false;

		// Armazena se é um exception de autoloader
		private $_special_exception = false;

		// Cria um novo erro
		public function __construct($error_code, $args = null, $cache_args = array()) {
			$this->_error_code = $error_code;
			$this->_backtrace = debug_backtrace();
			$this->_globals = $GLOBALS;
			$this->_args = array_merge((array) $args, $cache_args);
			$this->_id = md5("{$error_code}:" . json_encode($cache_args));
		}

		// Define o erro como fatal
		public function set_fatal($is_fatal = true) {
			$this->_is_fatal = $is_fatal;

			if($is_fatal === true)
				$this->set_individual_log();

			return $this;
		}

		// Define se é necessário registrar a ocorrência
		public function set_log($mode = true) {
			$this->_enable_log = $mode;
			return $this;
		}

		// Define se é necessário registrar ocorrência individual
		public function set_individual_log($mode = true) {
			$this->_enable_individual_log = $mode;

			if($mode === true)
				$this->set_log();

			return $this;
		}

		// Obtém os argumentos da mensagem
		public function get_message_args() {
			if(!isset($this->_args['args']))
				return array();

			return $this->_args['args'];
		}

		// Ativa a exceção especial (de autoloader)
		public function run_special_exception() {
			$this->_special_exception = true;
			$this->run();
		}

		// Lança um erro
		public function run() {
			// Se não for crítico, apenas lança o erro como exception
			if($this->_is_fatal === false) {
				// Se for uma exceção normal
				if($this->_special_exception === false)
					throw new core_exception(null, $this->_error_code, $this);

				// Senão, lança uma exceção de núcleo
				$classname = $this->_args['classname'];
				eval("class {$classname} extends core_exception {}");
				throw new $classname(null, $this->_error_code, $this);
			}

			// Armazena as informações do erro em uma sessão
			$_SESSION['last-error'] = (object) array(
				'error_code' => $this->_error_code,
				'backtrace' => $this->_backtrace,
				'globals' => $this->_globals,
				'args' => $this->_args,
				'id' => $this->_id
			);

			//TODO: registrar a ocorrência e alterara a página
			header('Location: ' . baseurl(false) . 'core/error');
		}
	}
