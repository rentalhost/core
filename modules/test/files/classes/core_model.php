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
			$tests = array(
				1	=> 'SELECT 1;',
				3	=> 'SELECT [[test]];',
				4	=> 'SELECT [[[test]]];',
				5	=> 'SELECT [[[te\]st]]];',
				6	=> 'SELECT [this];',
				7	=> 'SELECT [__useful_user];',
				8	=> 'SELECT [this.id];',
				9	=> 'SELECT [__useful_user.id];',
				10	=> 'SELECT [__useful_user.date_created(date)];',
				11	=> 'SELECT [__useful_user.date_created as date];',
				12	=> 'SELECT [__useful_user.date_created(date) as date];',
				13	=> 'SELECT [*];',
				14	=> 'SELECT [this.*];',
				15	=> 'SELECT [__useful_user: id];',
				16	=> 'SELECT [__useful_user: id, name];',
				17	=> 'SELECT [this: id, name];',
				18	=> 'SELECT [__useful_user: id(type) as test];',
				19	=> 'SELECT [__useful_user: id(type) as test, date(subtype) as subtest];',
			);

			foreach($tests as $key => $test)
				$this->test($key, core_model_query::parse_query($test), $test);
		}
	}
