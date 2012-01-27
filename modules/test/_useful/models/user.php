<?php

	// Modelo de testes
	class test_useful_user_model extends test_useful_project_model {
		// Prepara o modelo
		public function on_require() {
			$this->table('users');

			// Chaves para load
			$this->add_key('load_by_username', 'SELECT * FROM [this] WHERE [this.access_username] = [@username]', 'username');
			$this->add_key('load_columns', 'SELECT [this: id, access_username] FROM [this] WHERE [this.access_username] = [@1]');
			$this->add_key('load_default', 'SELECT [*] FROM [this] WHERE [this.access_username] = [@1]', null, array('operator'));

			// Chaves para exists
			$this->add_key('exists_username', 'SELECT [this.id] FROM [this] WHERE [this.access_username] = [@1]');
		}
	}
