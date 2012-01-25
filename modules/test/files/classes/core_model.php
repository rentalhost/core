<?php

	// Classe para testes de modelos
	class unit_core__model_library extends test_class_library {
		public function test_model() {
			$row = model('useful/user');
			$row_model = $row->model();

			$this->set_prefix('model');
			$this->test(1, $row_model);
			$this->test(2, $row_model->prefix());
			$this->test(3, $row_model->prefix(false));
			$this->test(4, $row_model->table());
			$this->test(5, $row_model->table(false));
		}
	}
