<?php

	// Classe para controle de exceções
	// Nota: Como os dados de Exception não são compatíveis com o padrão do Core, as funções possuem aliases.
	class core_exception extends Exception {
		// Armazena o erro que gerou a exceção
		private $_error = null;

		// Aliases
		public function get_code()		{ return $this->code; }
		public function get_file()		{ return $this->file; }
		public function get_line()		{ return $this->line; }
		public function get_trace()		{ return $this->getTrace(); }

		// Constrói uma mensagem de erro
		public function __construct($message, $code = 0, $error = null) {
			parent::__construct($message);
			$this->code = $code;
			$this->_error = $error;
		}

		// Obtém a mensagem com base em code
		public function get_message($args = array()) {
			// Se a mensagem foi informada, apenas retorna
			if(!empty($this->message)
			|| substr($this->code, 0, 2) !== 'Cx')
				return $this->message;

			// Caso contrário, retorna a informação do erro com base na tradução
			$lang = lang('/core/errors/err' . substr($this->code, 2));
			return $lang->get_value('error_message', $this->_error->get_message_args());
		}
	}
