<?php

	// Esta classe adiciona os tipos mais comuns de sql para o core
	class core_default_types extends core_types {
		// Adiciona os tipos padrões
		//TODO: tipo key não pode ser usado para output
		public function on_require() {
			$this->add_type('default',	null, 'NULL', 	false, false);
			$this->add_type('key',		null, null,		false, false);
			$this->add_type('int',		null, 0,		false, true);
			$this->add_type('string',	null, '""',		false, false);
			$this->add_type('float',	null, 0,		false, true);
			$this->add_type('list',		null, null,		false, false);
			$this->add_type('sql',		null, null,		false, false);
		}

		/** DEFAULT */
		// Usado quando um tipo não é determinado
		public function set_default($input) {
			if(is_string($input))
				return $this->set_string($input);
			else
			if(is_int($input)
			|| ctype_digit($input))
				return (int) $input;
			else
			if(is_float($input))
				return (float) $input;
			else
			if($input instanceof core_model_row)
				return $input->id();
		}

		/** KEY */
		public function set_key($input) {
			return '`' . self::conn()->escape($input) . '`';
		}

		/** INT */
		public function both_int($input) {
			return (int) $input;
		}

		/** STRING */
		public function set_string($input) {
			return '"' . self::conn()->escape($input) . '"';
		}

		/** FLOAT */
		public function both_float($input) {
			return (float) str_replace(',', '.', $input);
		}


		/** LIST */
		public function set_list($input) {
			return join(',', array_unique($input));
		}

		public function get_list($input) {
			return split(',', $input);
		}

		/** SQL */
		public function set_sql($input) {
			return $input;
		}
	}

	// Inicia o objeto
	$default_types = new core_default_types;
	$default_types->on_require();
