<?php

	// Modelo de testes
	class test_useful_user_profile_model extends test_useful_user_model {
		// Prepara o modelo
		public function on_require() {
			$this->table('profiles');
		}
	}
