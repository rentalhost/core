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

			$this->set_prefix('model_path');
			$row = model('useful/new_test');
			$this->test(1, $row);
		}

		public function test_query() {
			$tests = array(
				1	=> 'SELECT 1;',
				3	=> 'SELECT [[test]];',
				4	=> 'SELECT [[[test]]];',
				5	=> 'SELECT [[[te\]st]]];',
				6	=> 'SELECT [this];',
				7	=> 'SELECT [useful/user];',
				8	=> 'SELECT [this.id];',
				9	=> 'SELECT [useful/user.id];',
				10	=> 'SELECT [useful/user.date_created(date)];',
				11	=> 'SELECT [useful/user.date_created as date];',
				12	=> 'SELECT [useful/user.date_created(date) as date];',
				13	=> 'SELECT [*];',
				14	=> 'SELECT [this.*];',
				15	=> 'SELECT [useful/user: id];',
				16	=> 'SELECT [useful/user: id, name];',
				17	=> 'SELECT [this: id, name];',
				18	=> 'SELECT [useful/user: id(type) as test];',
				19	=> 'SELECT [useful/user: id(type) as test, date(subtype) as subtest];',
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
			$this->test(1, $row->query('SELECT [this.id] FROM [this] ORDER BY [this.id];')->fetch_object());
			$this->test(2, $row->query('SELECT [@test];', array('test' => 'okay'))->fetch_object());
			$row = model('useful/user', 1);
			$this->test(3, $row->query('SELECT [@this.id(int)] AS `test`;')->fetch_object());
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
			$this->test(6, $user->exists_username('operator'));
			$this->test(7, $user->exists_username('fake'));
			$this->test(8, $user->count_like('%a%'));
			$this->test(9, $user->count_like('%z%'));
			$this->test(10, $user->count());
			$profile = $user->one_profile();
			$this->test(11, $profile);
			$this->test(12, $profile->from() === $user);
			$user->load_default('fake');
			$this->test(13, $user);

			$this->set_prefix('multi');
			$user = model('useful/user', 1);
			$users = $user->multi_users();
			foreach($users->fetch_all() as $key => $value)
				$this->test($key, $value->values());

			$this->set_prefix('multi_results');
			$this->test(1, $users->model());
			$this->test(2, $users->from());
			$this->test(3, count($users));

			$this->set_prefix('many');
			$phones = $user->many_phones();
			foreach($phones->fetch_all() as $key => $value)
				$this->test($key, $value->values());

			$this->set_prefix('many_typed');
			$phones = $user->many_phones_typed();
			foreach($phones->fetch_all() as $key => $value)
				$this->test($key, $value->values());

			$this->set_prefix('many_results');
			$this->test(1, $phones->model());
			$this->test(2, $phones->from());
			$this->test(3, count($phones));
		}

		public function test_basic() {
			$log = model('useful/user/log');
			$log->truncate();

			$this->test(1, $log);
			$log->load(1);
			$this->test(2, $log->exists());

			$log->log_text = "Saving a log...";
			$log->log_date = 1328821108;

			$this->test(3, $log);
			$this->test(4, $log->save());
			$this->test(5, $log->exists());

			$this->test(6, $log->reload());
			$this->test(7, $log);

			$this->set_prefix('insert');
			$log = model('useful/user/log', 1);

			$this->test(1, $log);

			$log->log_date = '2012-02-09 11:22:33';
			$this->test(2, $log);
			$this->test(3, $log->exists());
			$this->test(4, $log->save());
			$this->test(5, $log);
			$this->test(6, $log->exists());

			$this->set_prefix('foreign');
			$user = model('useful/user', 1);
			$log = model('useful/user/log', 1);
			$log->id_user = $user;

			$this->test(1, $log);
			$this->test(2, $log->save());
			$this->test(3, $log->reload());
			$this->test(4, $log);

			$this->set_prefix('delete');
			$log = model('useful/user/log', 1);
			$this->test(1, $log->exists());
			$this->test(2, $log->delete());
			$this->test(3, $log->exists());
		}
	}
