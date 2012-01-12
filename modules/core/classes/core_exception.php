<?php

	// Classe para controle de exceções
	// Nota: Como os dados de Exception não são compatíveis com o padrão do Core, as funções possuem aliases.
	class core_exception extends Exception {
		// Aliases
		public function get_message()	{ return $this->message; }
		public function get_code()		{ return $this->code; }
		public function get_file()		{ return $this->file; }
		public function get_line()		{ return $this->line; }
		public function get_trace()		{ return $this->getTrace(); }
	}
