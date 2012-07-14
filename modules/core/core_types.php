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
			$this->add_type('date', 	null, null, 	false, false);
			$this->add_type('time',		null, null, 	false, false);
			$this->add_type('datetime',	null, null, 	false, false);
			$this->add_type('list',		null, array(),	false, false);
			$this->add_type('json', 	null, null, 	false, false);
			$this->add_type('bool',		null, false,	false, false);
			$this->add_type('password',	null, null,		false, false);
			$this->add_type('bytes', 	null, null, 	false, false);
			$this->add_type('ipv4', 	null, null, 	false, false);
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

		/** DATE */
		public function set_date($input) {
		}

		public function get_date($input) {
			return date(lang('/core/date')->date_format, strtotime($input));
		}

		/** TIME */
		public function set_time($input) {
		}

		public function get_time($input) {
			return date(lang('/core/date')->time_format, strtotime($input));
		}

		/** DATETIME */
		public function set_datetime($input) {
		}

		public function get_datetime($input) {
			return date(lang('/core/date')->datetime_format, strtotime($input));
		}

		/** LIST */
		public function set_list($input) {
			if(is_string($input)) return $input;
			return '"' . join(',', array_unique($input)) . '"';
		}

		public function get_list($input) {
			if(is_array($input)) return $input;
			if(empty($input)) return array();
			return explode(',', $input);
		}

		/** JSON */
		public function set_json($input) {
			return '"' . self::conn()->escape(json_encode($input)) . '"';
		}

		public function get_json($input) {
			return json_decode($input, true);
		}

		/** BOOL */
		public function set_bool($input) {
			return $input ? 'TRUE' : 'FALSE';
		}

		public function get_bool($input) {
			return core::get_state(strtolower($input), 'y');
		}

		/** PASSWORD */
		public function set_password($input) {
			return '"' . hash_hmac('sha256', $input, config('security_key'), false) . '"';
		}

		/** BOOL */
		public function set_bytes($input) {
			if(!preg_match('/^(?<size>\d+(?:[\.\,]\d+)?)\s?(?<level>B|KB|MB|GB|TB)?$/i', $input, $match))
				return null;

			$levels = array('B', 'KB', 'MB', 'GB', 'TB');
			$level = $match['level'] ? array_search($match['level'], $levels) : 0;

			return ((float) str_replace(',', '.', $match['size'])) * pow(1024, $level);
		}

		public function get_bytes($input) {
			$levels = array('B', 'KB', 'MB', 'GB', 'TB');
			$level = 0;

			while($input > 1024 && $level < 4) {
				$level++;
				$input/= 1024;
			}

			$input = number_format($input, $level === 0 ? 0 : 2, '.', '');
			return "{$input} {$levels[$level]}";
		}

		/** IPv4 */
		public function set_ipv4($input) {
			return ip2long($data);
		}

		public function get_ipv4($input) {
			return long2ip($data);
		}

		/** SQL */
		public function set_sql($input) {
			return $input;
		}
	}

	// Inicia o objeto
	$default_types = new core_default_types;
	$default_types->on_require();
