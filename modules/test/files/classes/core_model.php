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
				20	=> 'SELECT [@telefone];',
				21	=> 'SELECT [@telefone(phone)];',
				22	=> 'SELECT [@1];',
				23	=> 'SELECT [@1(phone)];',
				24	=> 'SELECT [@telefone(phone)?];',
				25	=> 'SELECT [@telefone?];',
				26	=> 'SELECT [@telefone?null];',
			);

			foreach($tests as $key => $test)
				$this->test($key, core_model_query::parse_query($test), $test);

			$this->set_prefix('query');
			$conn = connection();
			$model = model('useful/user')->model();
			$model_args_1 = array(
				'key'		=> 'test',
				'int'		=> '1234',
				'float'		=> '12.34',
				'float2'	=> '12,34',
				'sql'		=> 'DATE()',
			);
			$this->test(1, core_model_query::query($conn, 'SELECT [this];', $model));
			$this->test(2, core_model_query::query($conn, 'SELECT [this.id];', $model));
			$this->test(3, core_model_query::query($conn, 'SELECT [this.id(int)];', $model));
			$this->test(4, core_model_query::query($conn, 'SELECT [this.id(int) as id_user];', $model));
			$this->test(5, core_model_query::query($conn, 'SELECT [this: id, name];', $model));
			$this->test(6, core_model_query::query($conn, 'SELECT [this.*];', $model));
			$this->test(7, core_model_query::query($conn, 'SELECT [@int];', $model, $model_args_1));
			$this->test(8, core_model_query::query($conn, 'SELECT [@int(int)];', $model, $model_args_1));
			$this->test(9, core_model_query::query($conn, 'SELECT [@float(float)];', $model, $model_args_1));
			$this->test(10, core_model_query::query($conn, 'SELECT [@float2(float)];', $model, $model_args_1));
			$this->test(11, core_model_query::query($conn, 'SELECT [@float(int)];', $model, $model_args_1));
			$this->test(12, core_model_query::query($conn, 'SELECT [@float2(int)];', $model, $model_args_1));
			$this->test(13, core_model_query::query($conn, 'SELECT [@sql(sql)];', $model, $model_args_1));
			$this->test(14, core_model_query::query($conn, 'SELECT [@key(key)];', $model, $model_args_1));
			$this->test(16, core_model_query::query($conn, 'SELECT [@fake(int)?];', $model, $model_args_1));
			$this->test(17, core_model_query::query($conn, 'SELECT [@fake(float)?];', $model, $model_args_1));
			$this->test(18, core_model_query::query($conn, 'SELECT [@fake(string)?];', $model, $model_args_1));
			$this->test(19, core_model_query::query($conn, 'SELECT [@fake?];', $model, $model_args_1));
			$this->test(20, core_model_query::query($conn, 'SELECT [@fake(int)?null];', $model, $model_args_1));
			$this->test(21, core_model_query::query($conn, 'SELECT [@fake?null];', $model, $model_args_1));

			$this->set_prefix('model');
			$row = model('useful/user');
			$this->test(1, $row->query('SELECT [this.id] FROM [this];')->fetch_object());
			$this->test(2, $row->query('SELECT [@test];', array('test' => 'okay'))->fetch_object());
		}

		public function test_row() {
			$user = model('useful/user', 1);
			$this->test(1, $user);
			$user->load_by_username('operator');
			$this->test(2, $user);
			$user->load_columns('operator');
			$this->test(3, $user);
			$user->load_default();
			$this->test(4, $user);
			$user->load_default('admin');
			$this->test(5, $user);
		}
	}
