<?php

	// Classe que gerencia tipos sql
	class core_types {
		/** ESTÁTICO */
		// Armazena os tipos existentes
		static private $_types = array();
		// Define a conexão atual
		static private $_conn;

		// Obtém ou altera a conexão atual
		static public function conn() {
			return self::$_conn;
		}

		// Obtém o valor de um tipo
		static public function type_return($conn, $type, $data, $optional = null, $nullable = null, $method = 'set') {
			self::$_conn = $conn;

			// Obtém os dados do tipo
			$type = isset(self::$_types[$type]) ? self::$_types[$type] : self::_load_type($type);

			// Se for opcional e o valor estiver vazio
			if($data === null) {
				if($method === 'get') {
					if($optional === null
					|| $type['optional'] === null)
						//TODO: se não for opcional para o valor ou para o tipo, informa um erro
						return null;
				}
				else
				// Em outro caso, retorna o valor opcional
				return $nullable !== true ? $type['optional'] : 'NULL';
			}

			// Se tudo acontecer normalmente, define o método que será usado
			$call_method = ($type['method'] === true ? 'both' : $method) . '_' . $type['alias'];
			$call_method = array($type['object'], $call_method);

			// Se a informação foi instancia de um core_model_row, obtém apenas o ID
			if($data instanceof core_model_row)
				$data = $data->id;

			// Se for chamável
			if(is_callable($call_method))
				return call_user_func($call_method, $data);

			// Em outro caso, retorna o próprio valor
			return $method === 'get' ? $data : 'NULL';
		}

		// Carrega um novo tipo
		static private function _load_type($type) {
			// Procura o arquivo de tipo
			$type_data = core::get_modular_parts($type, array(
				'path_clip' => true,
				'path_repeat' => true,
				'path_extension' => '/types',
				'make_fullpath' => true
			));

			// Obtém o nome e inclui a classe responsável
			$type_class = core::get_joined_class($type_data, 'types');
			core::do_require($type_data->fullpath);

			// Inclui e prepara a classe
			$type_object = new $type_class();
			$type_object->on_require();

			// Retorna o objeto com o tipo selecionado
			return self::$_types[$type];
		}

		/** OBJETO */
		// Inicia a preparação
		public function on_require() {}

		// Adiciona um novo tipo
		public function add_type($type_name, $type_alias = null, $optional_value = null, $include_module = true,
				$same_both = false) {
			// Define o aliases
			$type_alias = $type_alias ? $type_alias : $type_name;

			// Se for necessário incluir o módulo atual no nome
			if($include_module === true)
				$type_name = join('_', core::get_caller_module_path()) . '_' . $type_name;

			// Objeto de funcionamento
			$object = array(
				'object'	=> $this,
				'alias'		=> $type_alias,
				'optional'	=> $optional_value,
				'method'	=> $same_both
			);

			// Adiciona o objeto
			self::$_types[$type_name] = $object;
		}
	}

	// Anexa o padrão do core
	require_once CORE_ROOT . '/core_types.php';
