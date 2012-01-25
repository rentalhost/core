<?php

	// Classe para testes de modelos
	class unit_core__model_library extends test_class_library {
		public function test_model() {
			$row = model('useful/user');
			$row_model = $row->model();

			$this->test(1, $row_model);
			$this->test(2, $row_model->prefix());
			$this->test(3, $row_model->prefix(false));
			$this->test(4, $row_model->table());
			$this->test(5, $row_model->table(false));
		}

		public function test_query() {
			$this->test(1, core_model_query::parse_query('SELECT 1;'));
			$this->test(3, core_model_query::parse_query('SELECT [[test]];'));
			$this->test(4, core_model_query::parse_query('SELECT [[[test]]];'));
			$this->test(5, core_model_query::parse_query('SELECT [[[te\]st]]];'));
			$this->test(6, core_model_query::parse_query('SELECT [this];'));
			$this->test(7, core_model_query::parse_query('SELECT [__useful_user];'));
			$this->test(8, core_model_query::parse_query('SELECT [this.id];'));
			$this->test(9, core_model_query::parse_query('SELECT [__useful_user.id];'));
		}
	}
