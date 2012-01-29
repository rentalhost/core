<?php

	// Esta classe adiciona os tipos mais comuns de sql para o core
	class core_default_types extends core_types {
		// Adiciona os tipos padrões
		//TODO: tipo key não pode ser usado para output
		public function on_require() {
			$this->add_type('key',		null, null,	false, false);
			$this->add_type('int',		null, 0,	false, true);
			$this->add_type('string',	null, '""',	false, false);
			$this->add_type('float',	null, 0,	false, true);
			$this->add_type('sql',		null, null,	false, false);
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

		/** SQL */
		public function set_sql($input) {
			return $input;
		}
	}

	// Inicia o objeto
	$default_types = new core_default_types;
	$default_types->on_require();
