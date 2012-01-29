<?php

	// Classe que gerencia linhas de um modelo
	class core_model_row {
		// Conexão usada
		private $_conn;

		// Armazena a instância do modelo
		private $_model;
		// Armazena a instãncia do row de onde veio
		private $_from;

		// Armazena a informação da linha
		private $_data = null;
		// A linha existe?
		private $_exists = false;

		// Constrói um row
		public function __construct($conn, $model_instance, $load_id) {
			$this->_conn = $conn;
			$this->_model = $model_instance;

			// Carrega o ID inicial
			if($load_id !== null)
				$this->load($load_id);

		}

		// Carrega o objeto do modelo por ID
		public function load($id) {
			// Faz a busca e aplica uma informação recebida
			return $this->_apply_data($this->query('SELECT [*] FROM [this] WHERE `id` = [@id(int)];',
				array('id' => $id))->fetch_assoc());
		}

		// Obtém o ID atual
		public function id() {
			return (int) @$this->_data['id'];
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

		// Obtém os valores armazenados internamente
		public function values() {
			return (array) $this->_data;
		}

		// Aplica os dados recebidos
		public function _apply_data($result) {
			// Se não for informado um resultado...
			if($result == false) {
				$this->_exists = false;
				$this->_data = null;
			}
			// Senão, aplica as informações
			else {
				// Armazena o novo resultado
				$new_result = array();

				// Reconfigura o resultado, onde for necessário
				foreach($result as $key => $value) {
					// Se for configurado (json), reconfigura
					if($key[0] === '{') {
						$key_data = json_decode($key, true);
						$new_result[$key_data['name']] = core_types::type_return($this->_conn, $key_data['type'],
							$value, @$key_data['optional'], @$key_data['null'], 'get');
					}
					else
					$new_result[$key] = $value;
				}

				$this->_exists = true;
				$this->_data = $new_result;
			}
		}

		/** MÁGICO */
		// Faz uma chamada a um key
		public function __call($func, $args) {
			// Se for um key válido
			if(preg_match(core_model::METHOD_KEY_VALIDATE, $func)) {
				// Obtém as configurações da chave
				$key = $this->_model->_get_key($func);

				// A depender do tipo de chave...
				switch($key->type) {
					// Chave load carrega uma informação para os dados internos
					case 'load':
						$query = $this->query($key->sql, core_model_query::merge_args($args, $key));
						return $this->_apply_data($query->fetch_assoc());
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
						$model = model($key->model, $this->_data[$key->column], $this->_conn);
						$model->_from = $this;
						return $model;
					// Chave multi retorna múltiplos resultados do mesmo tipo deste modelo
					case 'multi':
						$query = $this->query($key->sql, core_model_query::merge_args($args, $key));
						return new core_model_results($this->_conn, $query, $this->_model, $this);
					// Chave many retorna múltiplos resultados de um diferente modelo
					case 'many':
						$model = model($key->model);
						$query = $model->query($key->sql, core_model_query::merge_args($args, $key), $this);
						return new core_model_results($this->_conn, $query, $model->model(), $this);
				}
			}
		}

		// Obtém a informação tipada
		//TODO: tipar
		public function __get($key) {
			return $this->_data[$key];
		}

		// Altera a informação tipada
		//TODO: tipar
		public function __set($key, $value) {
			$this->_data[$key] = $value;
		}

		/** EXTRA */
		// Obtém o modelo
		public function model() {
			return $this->_model;
		}

		// Executa uma query no modelo
		public function query($query, $args = null, $from = null) {
			return $this->_model->query($this->_conn, $query, $args, $from ? $from : $this);
		}
	}
