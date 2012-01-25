<?php

	// Modelo de testes
	class test_useful_project_model extends test_project_model {
		// Prepara o modelo
		public function on_require() {
			$this->prefix('useful_');
		}
	}
