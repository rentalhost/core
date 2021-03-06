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

		// Armazena dados privados
		private $_private_data = array();

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
			return (int) @$this->_data[$this->_model->primary_key]['internal'];
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

		/** DADOS PRIVADOS */
		// Obtém ou altera os dados
		public function data($key, $value = null) {
			if($value === null)
				return isset($this->_private_data[$key]) ? $this->_private_data[$key] : null;

			$this->_private_data[$key] = $value;
		}

		/** MÉTODOS DE REGISTRO */
		// Carrega o objeto do modelo por ID
		public function load($id) {
			// Define o método de carregamento
			$this->_loader_method = array(array($this, 'load'), array($id));

			// Faz a busca e aplica uma informação recebida
			$result = $this->_apply_data($this->query('SELECT [*] FROM [this] WHERE [@primaryColumn(key)] = [@id(int)];',
				array('primaryColumn' => $this->_model->primary_key, 'id' => $id))->fetch_assoc());

			// Se um resultado não for encontrado, aplica ao menos o id informado
			if($result === false) {
				$this->_data[$this->_model->primary_key] = array(
					'internal' => $id,
					'outdated' => true
				);
			}

			return $this;
		}

		// Recarrega o item atual
		public function reload() {
			if($this->_loader_method !== null)
				return call_user_func_array($this->_loader_method[0], $this->_loader_method[1]);
		}

		// Salva o objeto
		public function save($ignore_events = false) {
			// Argumentos
			$save_args = array('data' => array());

			// Cria uma nova mensagem
			$this->_event_messages = new core_message;

			// Armazena o status de existência atual e o tipo de evento
			$old_exists = $this->_exists;
			$event_type = $old_exists === true ? core_model::ON_UPDATE : core_model::ON_INSERT;

			// Executa o evento antes de salvar
			if($ignore_events === false
			&& !$this->_run_event('before_save', $event_type))
				return false;

			// Executa o evento antes de inserir ou atualizar
			if($ignore_events === false
			&& !$this->_run_event($old_exists ? 'before_update' : 'before_insert'))
				return false;

			// Aplica o valor que será inserido/atualizado na data list
			//NOTA: somente os dados desatualizados serão aplicados
			foreach($this->_data as $column => $value) {
				if($this->_data[$column]['outdated'] === true) {
					$this->_data[$column]['outdated'] = false;
					$save_args['data'][] = "`{$column}` = " . $this->_get_typed_value($column, 'set');
				}
			}

			// Se não houve modificações, retorna true
			if(empty($save_args['data']))
				return true;

			$save_args['data'] = join(', ', $save_args['data']);

			// Se o objeto já existir, faz um update
			if($this->_exists === true) {
				// Atualiza a informação no banco
				$save_args['primaryColumn'] = $this->_model->primary_key;
				$this->query('UPDATE [this] SET [@data(sql)] WHERE [@primaryColumn(key)] = [@this.id(int)];', $save_args);
			}
			// Caso contrário, é uma operação de inserção
			else {
				// Insere a informação no banco
				$this->query('INSERT INTO [this] SET [@data(sql)]', $save_args);

				// Se um ID não foi informado, aplica o ID recebido
				if(empty($this->_data[$this->_model->primary_key]))
					$this->_data[$this->_model->primary_key] = array(
						'internal' => $this->_conn->get_connection()->insert_id,
						'outdated' => false
					);

				// Define que agora a informação existe
				$this->_exists = true;
			}

			// Recarrega o elemento
			$this->reload();

			// Se não puder ignorar os eventos...
			if($ignore_events === false) {
				// Executa o evento após salvar, inserir ou atualizar
				$this->_run_event('on_save', $event_type);

				// Executa o evento antes de inserir ou atualizar
				$this->_run_event($old_exists === true ? 'on_update' : 'on_insert');
			}

			return true;
		}

		// Remove um registro
		public function delete($id = null) {
			// Cria uma nova mensagem
			$this->_event_messages = new core_message;

			// Se um id não for informado, usa o ID atual
			if($id === null) {
				// Se o registro não existir, cancela, mas retorna true
				if($this->_exists === false)
					return true;

				// Executa o evento antes de inserir ou atualizar
				if(!$this->_run_event('before_delete'))
					return false;

				$id = $this->_data[$this->_model->primary_key]['internal'];
				$this->_exists = false;
			}

			// Executa o evento antes de inserir ou atualizar
			$this->_run_event('on_delete');

			return $this->query('DELETE FROM [this] WHERE [@primaryColumn(key)] = [@id(int)]',
				array('primaryColumn' => $this->_model->primary_key, 'id' => $id));
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

				// Define o tipo do resultado, se necessário
				foreach($new_result as $key => $value) {
					$key_type = $this->_model->get_column_type($key);
					if($key_type !== null) {
						if(!isset($new_result[$key]['type'])) {
							$new_result[$key]['type'] = $key_type;
							continue;
						}

						for($i=0; $i<2; $i++) {
							if(isset($new_result[$key]['type'][$i])
							&& $new_result[$key]['type'][$i] === null) {
								$new_result[$key]['type'][$i] = $key_type[$i];
							}
						}
					}
				}

				$this->_exists = true;
				$this->_data = $new_result;

				return true;
			}
		}

		// Informa se uma determinada coluna foi modificada
		public function is_outdated($key) {
			return isset($this->_data[$key]['outdated']) ? $this->_data[$key]['outdated'] : false;
		}

		// Altera o tipo de uma coluna
		public function set_type($key, $type, $optional = true, $nullable = true) {
			if(!isset($this->_data[$key]))
				$this->_data[$key] = array(
					'internal' => null,
					'outdated' => false
				);

			$this->_data[$key]['type'] = array($type, $optional, $nullable);
		}

		/** MÁGICO */
		// Armazena o método de carregamento
		private $_loader_method;

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
						// Armazena o método de carregamento
						$this->_loader_method = array(array($this, '__call'), array($func, $args));

						$query = $this->query($key->sql, core_model_query::merge_args($args, $key));
						$this->_apply_data($query->fetch_assoc());
						return $this;
						break;
					// Chave exists apenas retorna true se a informação existir (ao menos um registro)
					// Chave count retorna a quantidade de registros compatíveis
					case 'exists':
					case 'count':
						$query = $this->query($key->sql, core_model_query::merge_args($args, $key));

						// Se for exists, retorna se existe algum registro
						if($key->type === 'exists')
							return $query->num_rows > 0;

						// Em outro caso (count) verifica se há somente uma coluna e se ela se chama COUNT(...)
						$fields = $query->fetch_fields();

						// Se houver apenas um campo e este for COUNT(*) retorna o seu valor
						if(count($fields) === 1
						&& preg_match('/^COUNT(.+)$/i', $fields[0]->name)) {
							$fetch = $query->fetch_row();
							return intval($fetch[0]);
						}

						// Em último caso, retorna o número de resultados encontrados
						return $query->num_rows;
						break;
					// Chave one retorna um objeto de outro modelo (ou o mesmo) baseado em uma coluna local
					case 'one':
						$model = model($key->model, $this->_get_typed_value($key->column), $this->_conn);
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

			// Se o método existir no modelo, executa
			if(method_exists($this->_model, $func)) {
				array_unshift($args, $this);
				return call_user_func_array(array($this->_model, $func), $args);
			}

			// Em último caso, retorna o valor armazenado normalmente
			return $this->__get($func);
		}

		// Obtém a informação tipada
		public function __get($key) {
			return $this->_get_typed_value($key, 'get');
		}

		// Obtém o valor original de uma chave
		public function get_original($key) {
			return isset($this->_data[$key]['original']) ? $this->_data[$key]['original'] : $this->_data[$key]['internal'];
		}

		// Altera a informação tipada
		public function __set($key, $value) {
			if(!isset($this->_data[$key]))
				$this->_data[$key] = array();

			$old_internal = isset($this->_data[$key]['internal']) ? $this->_data[$key]['internal'] : null;
			$old_outdated = isset($this->_data[$key]['outdated']) ? $this->_data[$key]['outdated'] : false;

			$this->_data[$key]['internal'] = $value;
			$this->_data[$key]['outdated'] = $old_internal !== $this->_get_typed_value($key);

			if($old_outdated !== $this->_data[$key]['outdated'])
				$this->_data[$key]['original'] = $old_internal;
		}

		// Verifica se o conteúdo está vazio
		public function __isset($key) {
			return isset($this->_data[$key]['internal']);
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

		// Retorna a conexão
		public function conn() {
			return $this->_conn;
		}

		/** EVENTOS */
		// Resposta do último evento ocorrido
		private $_event_result = true;

		// Executa um evento e valida o seu resultado
		private function _run_event($event_method, $event_argument = null) {
			$result = call_user_func(array($this->model(), $event_method), $this, $event_argument);

			// Se o evento retornar false, cancela
			if($result === false) {
				return $this->_event_result = false;
			}

			// Chega se uma mensagem de erro foi gerada
			if($result instanceof core_message) {
				// Anexa as mensagens recebidas
				$this->_event_messages->append($result);

				// Se conter erros, retorna false
				if($result->has('error')) {
					$this->_event_result = $result;
					return false;
				}
			}

			return $this->_event_result = true;
		}

		/** MENSAGENS */
		// Armazena as mensagens de um determinado evento
		private $_event_messages;

		// Obtém as mensagens do último evento
		public function get_last_event_messages() {
			return $this->_event_messages;
		}
	}
