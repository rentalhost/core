<?php

	// Modelo de testes
	class test_useful_user_phone_model extends test_useful_user_model {
		// Prepara o modelo
		public function on_require() {
			$this->table('test_useful_user_phones', true);
		}
	}
