<?php

	// Classe que gerencia linhas de um modelo
	class core_model_row {
		// Conexão usada
		private $_conn;

		// Armazena a instância do modelo
		private $_model_instance;
		// Armazena a instãncia do row de onde veio
		private $_from;

		// Armazena a informação da linha
		private $_data = null;
		// A linha existe?
		private $_exists = false;

		// Constrói um row
		public function __construct($conn, $model_instance, $load_id) {
			$this->_conn = $conn;
			$this->_model_instance = $model_instance;

			// Carrega o ID inicial
			if($load_id !== null)
				$this->load($load_id);

		}

		// Carrega o objeto do modelo por ID
		public function load($id) {
			// Faz a busca e aplica uma informação recebida
			return $this->_apply_data($this->query('SELECT [*] FROM [this] WHERE `id` = [@id(int)];',
				array('id' => $id))->fetch_object());
		}

		// Calcula a quantidade de registros de um modelo
		public function count() {
			// Faz a busca e aplica uma informação recebida
			return (int) array_pop($this->query('SELECT COUNT(*) FROM [this];')->fetch_array());
		}

		// Obtém de onde esta row foi criada (dentro da chave one)
		public function from() {
			return $this->_from;
		}

		// Aplica os dados recebidos
		private function _apply_data($result) {
			// Se não for informado um resultado...
			if($result === false) {
				$this->_exists = false;
			}
			// Senão, aplica as informações
			else {
				$this->_exists = true;
				$this->_data = $result;
			}
		}

		/** MÁGICO */
		// Faz uma chamada a um key
		public function __call($func, $args) {
			// Se for um key válido
			if(preg_match(core_model::METHOD_KEY_VALIDATE, $func)) {
				// Obtém as configurações da chave
				$key = $this->_model_instance->_get_key($func);

				// A depender do tipo de chave...
				switch($key->type) {
					// Chave load carrega uma informação para os dados internos
					case 'load':
						$query = $this->query($key->sql, core_model_query::merge_args($args, $key));
						return $this->_apply_data($query->fetch_object());
						break;
					// Chave exists apenas retorna true se a informação existir (ao menos um registro)
					// Chave count retorna a quantidade de registros compatíveis
					case 'exists':
					case 'count':
						$query = $this->query($key->sql, core_model_query::merge_args($args, $key));
						return $key->type === 'exists' ? $query->num_rows > 0 : $query->num_rows;
						break;
					// Chave one retorna um objeto de outro modelo (ou o mesmo) baseado em uma coluna local
					case 'one':
						$model = model($key->model, $this->_data->{$key->column}, $this->_conn);
						$model->_from = $this;
						return $model;
				}
			}
		}

		// Obtém a informação tipada
		//TODO: tipar
		public function __get($key) {
			return $this->_data->{$key};
		}

		// Altera a informação tipada
		//TODO: tipar
		public function __set($key, $value) {
			$this->_data->{$key} = $value;
		}

		/** EXTRA */
		// Obtém o modelo
		public function model() {
			return $this->_model_instance;
		}

		// Executa uma query no modelo
		public function query($query, $args = null) {
			return $this->_model_instance->query($this->_conn, $query, $args, $this);
		}
	}
