<?php

	// Modelo de testes
	class test_useful_new__test_model extends test_project_model {
		// Prepara o modelo
		public function on_require() {
			$this->table('new_test');
		}
	}
