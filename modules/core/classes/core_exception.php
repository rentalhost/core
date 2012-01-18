<?php

	// Classe para controle de exceções
	// Nota: Como os dados de Exception não são compatíveis com o padrão do Core, as funções possuem aliases.
	class core_exception extends Exception {
		// Aliases
		public function get_code()		{ return $this->code; }
		public function get_file()		{ return $this->file; }
		public function get_line()		{ return $this->line; }
		public function get_trace()		{ return $this->getTrace(); }

		// Constrói uma mensagem de erro
		public function __construct($message, $code = 0) {
			parent::__construct($message);
			$this->code = $code;
		}

		// Obtém a mensagem com base em code
		public function get_message() {
			// Se a mensagem foi informada, apenas retorna
			if(!empty($this->message)
			|| substr($this->code, 0, 2) !== 'Cx')
				return $this->message;

			// Caso contrário, retorna a informação do erro com base na tradução
			$lang = lang('/core/errors/err' . substr($this->code, 2));
			return $lang->get_real_value('error_message');
		}
	}
