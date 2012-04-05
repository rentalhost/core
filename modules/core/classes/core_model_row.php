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

		// Obtém o valor tipado de uma chave
		private function _get_typed_value($key, $direction = 'get') {
			$data = $this->_data[$key];

			// Tipo de saída
			$type_data = isset($data['type']) ? $data['type'] : array('default', null, true);
			$type_data[0] = $type_data[0] ? $type_data[0] : 'default';

			// Retorna o valor tipado
			return core_types::type_return($this->_conn, $type_data[0], $data['internal'], $type_data[1], $type_data[2], $direction);
		}

		// Obtém o ID atual
		public function id() {
			return (int) @$this->_data['id']['internal'];
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
		public function values($internal_value = false) {
			$values = array();

			foreach($this->_data as $key => $data) {
				$values[$key] = $internal_value === true ? $data['internal'] : $this->_get_typed_value($key);
			}

			return $values;
		}

		/** MÉTODOS DE REGISTRO */
		// Carrega o objeto do modelo por ID
		public function load($id) {
			// Faz a busca e aplica uma informação recebida
			$result = $this->_apply_data($this->query('SELECT [*] FROM [this] WHERE `id` = [@id(int)];',
				array('id' => $id))->fetch_assoc());

			// Se um resultado não for encontrado, aplica ao menos o id informado
			if($result === false) {
				$this->_data['id'] = array(
					'internal' => $id,
					'outdated' => true
				);
			}

			return $result;
		}

		// Recarrega o item atual
		public function reload() {
			return $this->load($this->_data['id']['internal']);
		}

		// Salva o objeto
		public function save() {
			// Argumentos
			$save_args = array('data' => array());

			// Aplica o valor que será inserido/atualizado na data list
			//NOTA: somente os dados desatualizados serão aplicados
			foreach($this->_data as $column => $value) {
				if($this->_data[$column]['outdated'] === true) {
					$this->_data[$column]['outdated'] = false;
					$save_args['data'][] = "`{$column}` = " . $this->_get_typed_value($column, 'set');
				}
			}

			$save_args['data'] = join(', ', $save_args['data']);

			// Se o objeto já existir, faz um update
			if($this->_exists === true) {
				// Atualiza a informação no banco
				$this->query('UPDATE [this] SET [@data(sql)] WHERE `id` = [@this.id(int)];', $save_args);
			}
			// Caso contrário, é uma operação de inserção
			else {
				// Insere a informação no banco
				$this->query('INSERT INTO [this] SET [@data(sql)]', $save_args);

				// Se um ID não foi informado, aplica o ID recebido
				if(empty($this->_data['id']))
					$this->_data['id'] = array(
						'internal' => $this->_conn->insert_id,
						'outdated' => false
					);

				// Define que agora a informação existe
				$this->_exists = true;
			}

			return true;
		}

		// Remove um registro
		public function delete($id = null) {
			// Se um id não for informado, usa o ID atual
			if($id === null) {
				// Se o registro não existir, cancela, mas retorna true
				if($this->_exists === false)
					return true;

				$id = $this->_data['id']['internal'];
				$this->_exists = false;
			}

			return $this->query('DELETE FROM [this] WHERE `id` = [@id(int)]', array('id' => $id));
		}

		// Retorna se o registro existe na tabela
		public function exists() {
			return $this->_exists;
		}

		/** APLICA INFORMAÇÕES */
		// Aplica os dados recebidos
		public function _apply_data($result) {
			// Se não for informado um resultado...
			if($result == false) {
				$this->_exists = false;
				$this->_data = array();

				return false;
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
						$new_result[$key_data['name']] = array(
							'internal' => $value,
							'type' => array($key_data['type'], @$key_data['optional'], @$key_data['null']),
							'outdated' => false
						);
					}
					else
					$new_result[$key] = array(
						'internal' => $value,
						'outdated' => false
					);
				}

				$this->_exists = true;
				$this->_data = $new_result;

				return true;
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
						$this->_apply_data($query->fetch_assoc());
						return $this;
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
						$model = model($key->model, $this->_data[$key->column]['internal'], $this->_conn);
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

			// Em último caso, executa o método dentro do modelo
			array_unshift($args, $this);
			return call_user_func_array(array($this->_model, $func), $args);
		}

		// Obtém a informação tipada
		//TODO: tipar
		public function __get($key) {
			return $this->_get_typed_value($key, 'get');
		}

		// Altera a informação tipada
		public function __set($key, $value) {
			if(!isset($this->_data[$key]))
				$this->_data[$key] = array();

			$this->_data[$key]['internal'] = $value;
			$this->_data[$key]['outdated'] = true;
		}

		/** MODELO */
		// Trunca a tabela (remove todos os rows)
		public function truncate() {
			// Ao truncar, não existe mais nenhum row, então este também deixa de existir
			$this->_exists = false;

			return $this->query('TRUNCATE [this];');
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
