<?php

	// Classe para controle de exceções
	// Nota: Como os dados de Exception não são compatíveis com o padrão do Core, as funções possuem aliases.
	class core_exception extends Exception {
		// Aliases
		public function get_code()		{ return $this->code; }
		public function get_file()		{ return $this->file; }
		public function get_line()		{ return $this->line; }
		public function get_trace()		{ return $this->getTrace(); }

		// Obtém a mensagem com base em code
		public function get_message() {
			// Se a mensagem foi informada, apenas retorna
			if(!empty($this->message))
				return $this->message;

			// Caso contrário, retorna a informação do erro com base na tradução
			$lang = lang('/core/errors/err' . str_pad(strtoupper(dechex($this->code)), 4, '0', STR_PAD_LEFT));
			return $lang->get_real_value('error_message');
		}
	}
