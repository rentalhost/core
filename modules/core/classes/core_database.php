<?php

	// Classe para gerenciamento de databases
	class core_database {
		// Armazena a informação se já está conectado
		private	$_connected = false;
		// Armazena o driver de conexão
		private	$_connection;

		// Armazena a string de conexão (para o lazy-mode)
		private	$_connection_string;
		// Armazena os dados da string aberto
		private $_connection_array = null;
		// Armazena o índex da conexão (geralmente default)
		private $_connection_index;

		// Cria uma nova conexão
		public function __construct($connection_string, $index_name) {
			$this->_connection_string = $connection_string;
			$this->_connection_index = $index_name;

			if(config('database_lazy_mode') === false)
				$this->connect();
		}

		// Abre a conexão com o banco
		public function connect() {
			// Somente se não houver conexão...
			if($this->_connected === false) {
				// Se o array de conexão ainda não foi definido, gera
				if($this->_connection_array === null) {
					// Decodifica a URL enviada
					$_c = parse_url($this->_connection_string);

					// Preenche o scheme/driver, a porta, a senha e o path/database
					$_c['scheme'] = isset($_c['scheme']) ? $_c['scheme'] : 'mysqli';
					$_c['host'] = isset($_c['host']) ? $_c['host'] : $_SERVER['HTTP_HOST'];
					$_c['port'] = isset($_c['port']) ? (int) $_c['port'] : 3306;
					$_c['user'] = isset($_c['user']) ? $_c['user'] : null;
					$_c['pass'] = isset($_c['pass']) ? $_c['pass'] : null;
					$_c['path'] = (isset($_c['path']) && $_c['path'] !== '/') ? substr($_c['path'], 1) : null;

					// Preenche a persistência de conexão e o charset que será usado
					$_q = isset($_c['query']) ? parse_str($_c['query']) : array();
					$_q['persistent'] = isset($_q['persistent']) ? core::get_state($_q['persistent']) : null;
					$_q['charset'] = isset($_q['charset']) ? $_q['charset'] : null;
					$_c['query'] = $_q;

					// Remove os dados da conexão
					$this->_connection_string = null;
					$this->_connection_array = $_c;

					// Remove os dados de análise
					unset($_c, $_q);
				}

				// Define o host
				$hostname = $this->_connection_array['host'];

				// Se for necessário conexão permanente
				if($this->_connection_array['query']['persistent'] === true
				|| ($this->_connection_array['query']['persistent'] === null
				 && config('database_persistent_mode') === true))
					$hostname = "p:{$hostname}";

				// Abre a conexão
				$this->_connection = new mysqli($hostname,
					$this->_connection_array['user'],
					$this->_connection_array['pass'],
					$this->_connection_array['path'],
					$this->_connection_array['port']);

				// Define o charset
					//config('database_default_charset')
				$charset = $this->_connection_array['query']['charset'] !== null
					? $this->_connection_array['query']['charset'] : config('database_default_charset');
				$this->_connection->set_charset($charset);

				// Define que a conexão foi feita
				$this->_connected = true;
			}

			return true;
		}

		// Fecha a conexão
		public function disconnect() {
			$this->_connection = null;
			$this->_connected = false;
		}

		// Executa uma query
		//TODO: lançar erros, se houver
		public function query($query_string) {
			// Inicia a conexão, se necessário
			if($this->_connected === false)
				$this->connect();

			// Armazena o resultado da query
			return $this->_connection->query($query_string);
		}

		/** PROPRIEDADES */
		// Obtém a string de conexão
		//TODO: executar o parser antes de exibir, caso não tenha executado antes (para padronizar)
		public function get_connection_string() {
			if($this->_connection_string !== null)
				return $this->_connection_string;

			// Define o scheme/driver
			$string = "{$this->_connection_array['scheme']}://";

			// Se o usuário ou a senha for definido
			if($this->_connection_array['user']
			|| $this->_connection_array['pass']) {
				// Se ao menos o usuário for definido
				if($this->_connection_array['user'])
					$string.= $this->_connection_array['user'];

				// Se ao menos a senha for definida
				if($this->_connection_array['pass'])
					$string.= ":{$this->_connection_array['pass']}";

				$string.= "@";
			}

			// Armazena a query
			$query = $this->_connection_array['query'];
			$query = array_filter($query, 'core::_not_empty');

			// Se houver persistent
			if(isset($query['persistent']))
				$query['persistent'] = $query['persistent'] === true ? 'true' : 'false';

			// Define o hostname, porta, database e a query
			$string.= "{$this->_connection_array['host']}:{$this->_connection_array['port']}" .
					  "/{$this->_connection_array['path']}";

			// Se houver query
			if(!empty($query))
				$string.= '?' . http_build_query($query);

			// Armazena e retorna o resultado gerado
			$this->_connection_string = $string;
	  		return $string;
		}

		/** OBJETO */
		// Cria uma nova conexão na prioridade atual (configurações)
		//TODO: se o alias já existir, gera um erro
		static public function _create_connection($connection_string, $index_name = null) {
			// Armazena a conexão no sistema de configurações
			$conn = new self($connection_string, $index_name);
			core_config::set_config(null, "database_connection[{$index_name}]", $conn);

			return $conn;
		}

		// Obtém uma conexão
		//TODO: lançar um erro, caso não seja possível encontrar a conexão
		static public function _get_connection($conn_path = null, $index_name = null) {
			return core_config::get_config($conn_path, "database_connection[{$index_name}]");
		}
	}
