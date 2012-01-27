<?php

	// Modelo de testes
	class test_useful_user_model extends test_useful_project_model {
		// Prepara o modelo
		public function on_require() {
			$this->table('users');

			// Carrega um usuário por username
			$this->add_key('load_by_username', 'SELECT * FROM [this] '.
				'WHERE [this.access_username] = [@username]', 'username');
		}
	}
