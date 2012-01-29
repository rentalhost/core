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
			$type = self::$_types[$type];

			// Se for opcional e o valor estiver vazio
			if($data === null) {
				if($optional === null
				|| $type['optional'] === null)
					return null; //TODO: se não for opcional para o valor ou para o tipo, informa um erro

				// Em outro caso, retorna o valor opcional
				return $nullable !== true ? $type['optional'] : 'NULL';
			}

			// Se tudo acontecer normalmente, define o método que será usado
			$call_method = ($type['method'] === true ? 'both' : $method) . '_' . $type['alias'];
			$call_method = array($type['object'], $call_method);

			// Se for chamável
			if(is_callable($call_method))
				return call_user_func($call_method, $data);

			// Em outro caso, retorna um valor padrão
			return $method === 'get' ? null : 'NULL';
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
