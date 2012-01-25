<?php

	// Modelo de testes
	class test_project_model extends core_model {
		// Prepara o modelo
		public function on_require() {
			$this->prefix('test_');
		}
	}
