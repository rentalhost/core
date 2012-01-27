<?php

	// Modelo de testes
	class test_useful_user_model extends test_useful_project_model {
		// Prepara o modelo
		public function on_require() {
			$this->table('users');
			$this->prefix('user_');

			// Chaves para load
			$this->add_key('load_by_username', 'SELECT * FROM [this] WHERE [this.access_username] = [@username]', 'username');
			$this->add_key('load_columns', 'SELECT [this: id, access_username] FROM [this] WHERE [this.access_username] = [@1]');
			$this->add_key('load_default', 'SELECT [*] FROM [this] WHERE [this.access_username] = [@1]', null, array('operator'));

			// Chaves para exists
			$this->add_key('exists_username', 'SELECT NULL FROM [this] WHERE [this.access_username] = [@1]');

			// Chaves para exists
			$this->add_key('count_like', 'SELECT NULL FROM [this] WHERE [this.access_username] LIKE [@1]');

			// Chaves para one
			$this->add_key('one_profile', 'useful/user/profile', 'id_profile');

			// Chaves para multi
			$this->add_key('multi_users', 'SELECT [this.*] FROM [this] WHERE [this.access_username] LIKE "%a%"');

			// Chaves para many
			$this->add_key('many_phones', 'useful/user/phone', 'SELECT [this.*] FROM [this] WHERE [this.id_user] = [@this.id(int)]');
		}
	}
