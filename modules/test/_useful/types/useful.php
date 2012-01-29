<?php

	// Tipos de teste
	class test_useful_types extends core_types {
		public function on_require() {
			$this->add_type('phone', null, null, true, true);
		}

		public function both_phone($data) {
			if(!preg_match('/^\s*(?:\(?(?<ddd>[0-9]{2})\)?)\s*(?<num1>[0-9]{4,5})\s*\-?\s*(?<num2>[0-9]{4})\s*?$/', $data, $data))
				return $data;

			if(empty($data['ddd']))
				$data['ddd'] = '00';

			return "({$data['ddd']}) {$data['num1']}-{$data['num2']}";
		}
	}
