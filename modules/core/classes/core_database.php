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

		// Armazena a última query executada
		private $_last_query;

		// Configurações booleanas
		static private $_bool_props = array('persistent', 'connect');
		// Configurações gerais
		static private $_props = array('persistent', 'charset', 'connect');

		// Cria uma nova conexão
		public function __construct($connection_string, $index_name) {
			$this->_connection_string = $connection_string;
			$this->_connection_index = $index_name;

			parse_str(parse_url($connection_string, PHP_URL_QUERY), $autoconnect);
			$autoconnect = isset($autoconnect['connect']) ? core::get_state($autoconnect['connect']) : null;

			if($autoconnect === null
			&& config('database_lazy_mode') === false)
				$autoconnect = true;

			if($autoconnect === true)
				$this->connect();
		}

		// Transforma uma connection string em array
		private function _parse_connection_string() {
			// Decodifica a URL enviada
			$cs = parse_url($this->_connection_string);

			// Preenche a persistência de conexão e o charset que será usado
			if(isset($cs['query']))
				parse_str($cs['query'], $qs);
			else $qs = array();

			// Se um host não for informado, usa a informação do path
			if(!isset($cs['host'])) {
				$cs['host'] = $cs['path'];
				unset($cs['path']);
			}

			// Preenche o scheme/driver, a porta, a senha e o path/database
			$cs = array(
				'driver'	=> isset($cs['scheme']) ? $cs['scheme'] : 'mysqli',
				'host'		=> isset($cs['host']) ? $cs['host'] : $_SERVER['HTTP_HOST'],
				'port'		=> isset($cs['port']) ? (int) $cs['port'] : 3306,
				'username'	=> isset($cs['user']) ? $cs['user'] : null,
				'password'	=> isset($cs['pass']) ? $cs['pass'] : null,
				'database'	=> (isset($cs['path']) && $cs['path'] !== '/') ? substr($cs['path'], 1) : null
			);

			foreach(self::$_bool_props as $item)
				$cs[$item] = isset($qs[$item]) ? core::get_state($qs[$item]) : null;

			$cs['charset'] = isset($qs['charset']) ? $qs['charset'] : null;

			// Remove os dados da conexão
			$this->_connection_string = null;
			$this->_connection_array = $cs;
		}

		// Abre uma conexão
		private function _real_connect($hostname) {
			// Abre a conexão
			$this->_connection = new mysqli($hostname,
				$this->_connection_array['username'],
				$this->_connection_array['password'],
				$this->_connection_array['database'],
				$this->_connection_array['port']);
		}

		// Abre a conexão com o banco
		public function connect($reconnect = false) {
			// Se já houver conexão, desconecta antes (reconexão)
			if($reconnect === true
			&& $this->_connection_string === null)
				$this->disconnect();

			// Somente se não houver conexão...
			if($this->_connected === false) {
				// Se o array de conexão ainda não foi definido, gera
				if($this->_connection_array === null)
					$this->_parse_connection_string();

				// Define o host
				$hostname = $this->_connection_array['host'];

				// Se for necessário conexão permanente (apenas PHP 5.3)
				$persistent_mode = false;
				if(PHP_VERSION_ID >= 50300) {
					$persistent_mode = $this->_connection_array['persistent'] === true
									|| ($this->_connection_array['persistent'] === null
									 && config('database_persistent_mode') === true);
					if($persistent_mode === true)
						$hostname = "p:{$hostname}";
				}

				// Abre a conexão
			   @$this->_real_connect($hostname);

				// Faz um ping
				if($persistent_mode === true
				&& $this->_connection->ping() === false)
					$this->_real_connect($hostname);

				// Define o charset
					//config('database_default_charset')
				$charset = $this->_connection_array['charset'] !== null
					? $this->_connection_array['charset'] : config('database_default_charset');
				$this->_connection->set_charset($charset);

				// Define que a conexão foi feita
				$this->_connected = true;
			}

			return true;
		}

		// Reinicia a conexão
		public function reconnect() {
			return $this->connect(true);
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
			return $this->_connection->query($this->_last_query = $query_string);
		}

		// Protege uma informação
		public function escape($data) {
			// Inicia a conexão, se necessário
			if($this->_connected === false)
				$this->connect();

			return $this->_connection->real_escape_string($data);
		}

		/** PROPRIEDADES */
		// Obtém a string de conexão
		public function get_connection_string() {
			// Se o array ainda não foi construído, o constrói
			if($this->_connection_array === null)
				$this->_parse_connection_string();
			else
			// Se já foi definido, apenas retorna
			if($this->_connection_string !== null)
				return $this->_connection_string;

			// Define o scheme/driver
			$string = "{$this->_connection_array['driver']}://";

			// Se o usuário ou a senha for definido
			if($this->_connection_array['username']
			|| $this->_connection_array['password']) {
				// Se ao menos o usuário for definido
				if($this->_connection_array['username'])
					$string.= $this->_connection_array['username'];

				// Se ao menos a senha for definida
				if($this->_connection_array['password'])
					$string.= ":{$this->_connection_array['password']}";

				$string.= "@";
			}

			// Define o hostname, porta e database
			$string.= "{$this->_connection_array['host']}:{$this->_connection_array['port']}" .
					  "/{$this->_connection_array['database']}";

			// Armazena a query
			$query = array();
			foreach(self::$_props as $item) {
				if($this->_connection_array[$item] === null)
					continue;

				if(in_array($item, self::$_bool_props))
				{ $query[$item] = $this->_connection_array[$item] === true ? 'true' : 'false'; }
				else
				{ $query[$item] = $this->_connection_array[$item]; }
			}

			// Se houver query
			if(!empty($query))
				$string.= '?' . http_build_query($query);

			// Armazena e retorna o resultado gerado
			$this->_connection_string = $string;
	  		return $string;
		}

		// Obtém uma propriedade individual
		public function get_property($property) {
			// Se o array ainda não foi construído, o constrói
			if($this->_connection_array === null)
				$this->_parse_connection_string();

			return $this->_connection_array[$property];
		}

		// Define uma propriedade individual
		public function set_property($property, $value) {
			// Se o array ainda não foi construído, o constrói
			if($this->_connection_array === null)
				$this->_parse_connection_string();
			$this->_connection_string = null;

			if(in_array($property, self::$_bool_props))
			{ $this->_connection_array[$property] = core::get_state($value); }
			else
			{ $this->_connection_array[$property] = $value; }

			return $this->_connection_array[$property];
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
