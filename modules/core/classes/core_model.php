<?php

	// Classe para gerenciamento de modeos
	class core_model {
		// Constante que verifica se o método chamado é uma key
		const	METHOD_KEY_VALIDATE		= '/^(?<key>one|many|load|exists|count|multi)_/';

		/** OBJETO */
		// Armazena as instâncias do modelo
		static private $_class_instances = array();

		// Carrega um modelo e retorna uma representação
		static public function _load_model($model_path) {
			$model_path = core::get_modular_parts(explode('/', $model_path), array(
				'modular_path_auto' => true,
				'path_extension' => '/models',
				'make_fullpath' => true,
			));

			return core::get_joined_class($model_path, 'model');
		}

		// Carrega um objeto de um modelo
		static public function _create_row($conn, $model_path, $load_id) {
			$model_instance = self::_get_instance(self::_load_model($model_path));
			$model_row = new core_model_row($conn, $model_instance, $load_id);

			$model_instance->on_instance($model_row);
			return $model_row;
		}

		// Obtém a instância de um modelo
		static public function _get_instance($model_instance) {
			// Se já hover definiçao, retorna rapidamente
			if(isset(self::$_class_instances[$model_instance]))
				return self::$_class_instances[$model_instance];

			// Senão, cria
			$model = new $model_instance;
			$model->on_require();

			return self::$_class_instances[$model_instance] = $model;
		}

		// Obtém uma instância de um path linear (__exemplo)
		static public function _get_linear($model_path) {
			$model_path = core::get_modular_parts($model_path, array(
				'modular_path_auto' => true,
				'path_extension' => '/models',
				'make_fullpath' => true,
			));

			return self::_get_instance(core::get_joined_class($model_path, 'model'));
		}

		/** INSTÂNCIA */
		// Armazena o nome da tabela do modelo único e completo
		protected $_table;
		protected $_table_full;
		// Armazena o prefixo do modelo único e completo
		protected $_prefix = null;
		protected $_prefix_full = null;

		// Armazena as keys do modelo
		protected $_keys = array();

		// Obtém ou altera o prefixo da tabela
		public function prefix($model_prefix = null, $use_as_full = false) {
			// Se for uma string, é uma alteração de prefixo
			if(is_string($model_prefix)) {
				// Se precisar usar como prefixo completo, apenas altera
				if($use_as_full === true) {
					$this->_prefix =
					$this->_prefix_full = $model_prefix;
				}
				// Em outro caso, apenas altera o prefixo
				// O prefixo completo será puxado da raiz
				else {
					$this->_prefix = $model_prefix;
					$this->_prefix_full = $this->prefix() . $model_prefix;
				}
			}
			// Em outro  csao, é uma operação para obter o prefixo
			else {
				// Se for necessário o prefixo completo (prefix())
				if($model_prefix !== false) {
					// Se o prefixo completo não foi definido, define...
					if($this->_prefix_full === null) {
						$parent_class = get_parent_class($this);

						// Se o parent for um core_model, retorna null
						if($parent_class === 'core_model')
							return null;

						// Se não, será necessário carregar a classe
						$parent_class = self::_get_instance($parent_class);
						$this->_prefix_full = $parent_class->prefix();
					}

					return $this->_prefix_full;
				}

				// Senão, retorna somente o prefixo (prefix(false))
				return $this->_prefix;
			}
		}

		// Obtém ou altera a tabela
		public function table($model_table = null, $use_as_full = false) {
			// Se for uma string, é uma alteração de tabela
			if(is_string($model_table)) {
				// Se precisar usar como tabela completa, apenas altera
				if($use_as_full === true) {
					$this->_table =
					$this->_table_full = $model_table;
				}
				// Em outro caso, apenas altera a tabela
				// O prefixo completo será puxado da raiz
				else {
					$this->_table = $model_table;
					$this->_table_full = $this->prefix() . $model_table;
				}
			}
			// Em outro  csao, é uma operação para obter a tabela
			else {
				// Retorna a tabela prefixada (table()) ou não-prefixada (table(false))
				return $model_table !== false ? $this->_table_full : $this->_table;
			}
		}

		// Executa uma query no modelo
		public function query($conn, $query, $args = null) {
			return $conn->query(core_model_query::query($conn, $query, $this, $args));
		}

		// Cria uma nova key
		//TODO: se não for uma key válida, retorna um erro
		public function add_key($keyname) {
			$args = func_get_args();
			preg_match(self::METHOD_KEY_VALIDATE, $keyname, $key);

			// Objeto que será preenchido
			$object = new stdclass;
			$object->type = $key['key'];

			// A depender do tipo de chave, preenche alguns parâmetros
			switch($key['key']) {
				// Se for load, exists, count ou multi
				case 'load':
				case 'exists':
				case 'count':
				case 'multi':
					$object->sql = $args[1];
					$object->args_order = isset($args[2]) ? $args[2] : null;
					$object->args_default = isset($args[3]) ? $args[3] : array();
					break;
				// Se for one
				case 'one':
					$object->model = $args[1];
					$object->column = $args[2];
					break;
				// Se for many
				case 'many':
					$object->sql = $args[2];
					$object->model = $args[1];
					$object->args_order = isset($args[3]) ? $args[3] : null;
					$object->args_default = isset($args[4]) ? $args[4] : array();
					break;
			}

			// Preenche a key
			$this->_keys[$keyname] = $object;
		}

		// Obtém a configuração de uma chave
		public function _get_key($keyname) {
			return $this->_keys[$keyname];
		}

		/** EVENTOS */
		// Ocorre ao inserir ou atualizar um registro
		public function before_save($instance, $operation) {}
		public function on_save($instance, $operation) {}
		// Ocorre apenas ao inserir um registro
		public function before_insert($instance) {}
		public function on_insert($instance) {}
		// Ocorre apenas ao atualizar um registro
		public function before_update($instance) {}
		public function on_update($instance) {}
		// Ocorre ao deletar um registro
		public function before_delete($instance) {}
		public function on_delete($instance) {}
		// Ocorre ao criar uma instância de row
		public function on_instance($instance) {}
	}
