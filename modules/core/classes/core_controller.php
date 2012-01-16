<?php

	// Classe de um controller
	class core_controller {
		// Indica que tudo ocorreu perfeitamente
		const   STATUS_SUCCESS				= 0;
		// Indica que o controller solicitado não foi encontrado
		const   STATUS_CONTROLLER_INVALID	= 1;
		// Indica que o método solicitado não foi definido
		const   STATUS_METHOD_NOT_EXISTS	= 2;
		// Indica que a modular é necessária
		const	STATUS_MODULAR_REQUIRED		= 4;
		// Indica que o path é necessário
		const	STATUS_PATH_REQUIRED		= 8;
		// Indica que o método é necessário
		const	STATUS_METHOD_REQUIRED		= 16;
		// Indica que o arquivo não foi encontrado
		const	STATUS_CONTROLLER_NOT_FOUND	= 32;

		// Indica que o tipo de retorno é padrão
		const   RETURN_TYPE_DEFAULT			= 'default';
		// Indica que o tipo de retorno é JSON
		const   RETURN_TYPE_JSON			= 'json';

		/** OBJETO */
		// Armazena a modular do controller
		private $_modular_data;
		// Armazena o status da operação
		private $_status;
		// Armazena o tipo de resultado esperado [default/json]
		private $_result_type;
		// Armazena o resultado retornado pela execução
		private $_result_data;
		// Armazena o conteúdo gerado pela função
		private $_result_contents	= "";
		// Armazena a informação se o conteúdo foi impresso
		private $_result_printed	= false;

		// Cria uma nova instância
		//DEBUG: verificar se o método chamado entra em conflito com os métodos do core_controller
		private function __construct( $modular_data, $controller_args, $cancel_print,
				$default_status, $default_result_type, $auto_execute ) {
			// Armazena o path modular para futuras referências
			$this->_modular_data = $modular_data;
			$this->_modular_data->args = (array) $controller_args;
			// Armazena o status padrão
			$this->_status = $default_status;
			// Armazena o tipo de retorno padrão
			$this->_result_type = $default_result_type;

			// Verifica se um dado método pode ser chamado
			if(isset($modular_data->method) === true
			&& method_exists($this, $modular_data->method) === false) {
				$this->_status|= self::STATUS_METHOD_NOT_EXISTS;
			}

			// Auto-executa, se necessário
			if($auto_execute === true) {
				$this->_execute();

				// Se a impressão não for cancelada, renderiza sua informação
				if($cancel_print === false)
					$this->render();
			}
		}

		// Lança uma exceção baseada no status
		private function _throw_exception() {
			if(($this->_status & self::STATUS_CONTROLLER_NOT_FOUND) === self::STATUS_CONTROLLER_NOT_FOUND) {
				throw new core_exception("Controller file is not found at \"{$this->_modular_data->fullpath}\".");
			}
			else
			if(($this->_status & self::STATUS_METHOD_REQUIRED) === self::STATUS_METHOD_REQUIRED) {
				throw new core_exception("Controller method is required in \"{$this->_modular_data->url}\".");
			}
			else
			if(($this->_status & self::STATUS_PATH_REQUIRED) === self::STATUS_PATH_REQUIRED) {
				throw new core_exception("Controller path not found in \"{$this->_modular_data->url}\".");
			}
			else
			if(($this->_status & self::STATUS_MODULAR_REQUIRED) === self::STATUS_MODULAR_REQUIRED) {
				throw new core_exception("Controller modular not found in \"{$this->_modular_data->url}\".");
			}
			else
			if(($this->_status & self::STATUS_METHOD_NOT_EXISTS) === self::STATUS_METHOD_NOT_EXISTS) {
				throw new core_exception("Controller method \"{$this->_modular_data->class}::{$this->_modular_data->method}\" not found.");
			}
		}

		// Executa o controller
		private function _execute() {
			// Se houve algum erro, evita que seja executado
			if( $this->_status !== self::STATUS_SUCCESS ) {
				$this->_throw_exception();
			}

			// Nesta etapa, é necessário receber os dados retornado pela função e gerado
			ob_start();
			$this->_result_data = call_user_func_array( array( $this, $this->_modular_data->method ), $this->_modular_data->args );
			$this->_result_contents = ob_get_contents();
			ob_end_clean();

			return $this;
		}

		// Imprime o conteúdo gerado
		public function render() {
			$this->_result_printed = true;

			// Se o tipo de resultado não for 'json', usa o método padrão de impressão
			if( $this->_result_type === self::RETURN_TYPE_DEFAULT ) {
				echo $this->_result_contents;
			}
			// Senão, imprime a informação em JSON
			else
			if( $this->_result_type === self::RETURN_TYPE_JSON ) {
				echo json_encode( $this->_result_data );
			}

			return $this;
		}

		// Se o arquivo for requerido, ele não poderá ter nenhum erro
		public function required() {
			if( $this->_status !== self::STATUS_SUCCESS ) {
				$this->_throw_exception();
			}

			return $this;
		}

		// Retorna true se o controller existir
		public function exists() {
			return ($this->_status & self::STATUS_CONTROLLER_INVALID) !== self::STATUS_CONTROLLER_INVALID;
		}

		// Retorna true se um erro ocorreu
		public function has_failed() {
			return $this->_status !== self::STATUS_SUCCESS;
		}

		// Retorna true se o conteúdo já foi impresso
		public function has_printed() {
			return $this->_result_printed;
		}

		// Retorna o status da operação
		public function get_status() {
			return $this->_status;
		}

		// Retorna true se o status for compatível
		public function has_status($status) {
			return ($this->_status & $status) === $status;
		}

		// Obtém a informação gerada pela função
		public function get_contents() {
			return $this->_result_contents;
		}

		public function __toString() {
			return $this->_result_contents;
		}

		// Obtém a informação retornada pela função
		public function get_return() {
			return $this->_result_data;
		}

		// Obtém o tipo de retorno
		public function get_return_type() {
			return $this->_result_type;
		}

		// Altera o tipo de retorno
		public function set_return_type( $return_type ) {
			$this->_result_type = $return_type;
			return $this;
		}

		// Obtém a informação modular
		public function get_modular_data() {
			return $this->_modular_data;
		}

		/** ESTÁTICO */
		// Armazena o controlador primário
		private static $_main_controller;

		// Inicia o controlador da URL
		static public function _init_url() {
			// Gera o modular path a partir dos dados recebidos do cliente
			$request_url = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['REQUEST_URI'];
			$modular_path = substr($request_url, strlen(dirname($_SERVER['PHP_SELF'])) + 1);

			// Gera o controler, ele é obrigatório pois é quem inicia a chamada
			self::$_main_controller = self::_create_controller( $modular_path, false, false );
			self::$_main_controller->_execute()->required()->render();
		}

		// Carrega uma URL
		//TODO: suporte a route exchange. Ex: [any]
		//TODO: quando o método chamado recebe .json no final, altera automaticamente o tipo de saída
		//DEBUG: verificar por ambiguidade no conteúdo
		//DEBUG: só deve haver uma classe de controller definida no arquivo incluído
		//DEBUG: se o método não existir, exibe um erro
		static public function _create_controller( $modular_path_data, $cancel_print = false, $auto_execute = true,
				$can_detect_caller_path = false ) {
			// Armazena o método padrão, ele será usado em vários momentos
			$default_method = core_config::get_config( null, 'route_default_method' );

			// Define algumas informações principais
			$modular_path = new stdclass;
			$modular_path->url = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' )
				. "://{$_SERVER['SERVER_NAME']}" . dirname( $_SERVER['SCRIPT_NAME'] ) . '/';

			// Se o path modular for false, então usa totalmente o padrão
			// Ex: http://127.0.0.1/
			if($modular_path_data === null
			|| $modular_path_data === false) {
				$modular_path->modular = (array) core_config::get_config( null, 'route_default_modular' );
				$modular_path->path = (array) core_config::get_config( null, 'route_default_controller' );
				$modular_path->method = $default_method;
				$modular_path->class = core::get_joined_class( $modular_path, 'controller' );
			}
			else {
				// Se puder detectar o path do caller (somente execute())
				if($can_detect_caller_path === true
				&& isset($modular_path_data[0]) === true) {
					// Se o primeiro caractere for um $, indica rota estrita inline
					if($modular_path_data[0] === '$') {
						$strict_route = true;
						$modular_path_data = substr($modular_path_data, 1);
					}

					// Se o modular começar por / apenas remove a informação
					// Caso contrário, deverá preencher com o path do módulo de chamada
					if($modular_path_data[0] === '/') {
						$modular_path_data = substr($modular_path_data, 1);
					}
					else {
						$modular_path_data = join('/', core::get_caller_module_path()) . '/' . $modular_path_data;
					}
				}

				// Se não definir o modo estrito de rota, obtém a informação
				if(!isset($strict_route)) {
					$strict_route = config('route_strict_mode');
				}

				// Extende a informação da URL
				$modular_path->url .= $modular_path_data;

				// Se houver uma definição de path, é necessário fazer uma busca por arquivos
				// Primeiramente, é necessário separar a chamada
				$modular_path_data = explode( '/', $modular_path_data );

				// Depois é necessário buscar pelo modular do endereço solicitado
				// Ex: http://127.0.0.1/site
				$modular_path_data = core::get_modular_parts( $modular_path_data, array(
					'search_paths' => false,
					'make_fullpath' => true
				) );

				// Se a modular não for definida, retorna um controller neutro
				if( isset( $modular_path_data->modular ) === false ) {
					return new self( $modular_path, null, $cancel_print,
						self::STATUS_CONTROLLER_INVALID | self::STATUS_MODULAR_REQUIRED, self::RETURN_TYPE_DEFAULT, false );
				}

				// Senão, armazena a informação
				$modular_path->modular = $modular_path_data->modular;

				// Se o remains[0] for igual a publics faz um redirecionamento de informação (HTTP 301)
				if( isset( $modular_path_data->remains )
				&&  $modular_path_data->remains[0] === 'publics' ) {
					core::do_publish( core::get_baseurl( false ) . 'modules/'
						. join( '/_', $modular_path->modular ) . '/'
						. join( '/', $modular_path_data->remains ) );
					return;
				}

				// Depois é necessário buscar pelo controller do endereço solicitado
				// Ex: http://127.0.0.1/site/master ou simplesmente http://127.0.0.1/master
				$modular_path_data = core::get_modular_parts( @$modular_path_data->remains, array(
					'start_dir' => $modular_path_data->fullpath . '/controllers',
					'search_modular' => false
				) );

				// Se o controller não for definido, define com o valor padrão
				if(isset($modular_path_data->path)) {
					$modular_path->path = $modular_path_data->path;
				}
				// Se a rota estrita estiver desativada, preenche com a configuração padrão
				else if($strict_route === false) {
					$modular_path->path = (array) core_config::get_config(null, 'route_default_controller');
				}
				// Em último caso, cria um erro
				else {
					return new self( $modular_path, null, $cancel_print,
						self::STATUS_CONTROLLER_INVALID | self::STATUS_PATH_REQUIRED, self::RETURN_TYPE_DEFAULT, false );
				}

				// Gera o nome completo da chamada
				$modular_path->class = core::get_joined_class( $modular_path, 'controller' );

				// Se não existir mais informações para o method, usa o valor padrão
				if(empty($modular_path_data->remains) === false) {
					$modular_path->method = array_shift($modular_path_data->remains);
				}
				else
				if($strict_route === false) {
					$modular_path->method = $default_method;
				}
				else {
					return new self( $modular_path, null, $cancel_print,
						self::STATUS_CONTROLLER_INVALID | self::STATUS_METHOD_REQUIRED, self::RETURN_TYPE_DEFAULT, false );
				}
			}

			// Gera o caminho completo do arquivo
			$modular_path->fullpath = core::get_path_fixed( CORE_MODULES . '/'
				. join( '/_', $modular_path->modular ) . '/controllers/'
				. join( '/', $modular_path->path ) . '.php' );

			// Se o arquivo de controller não existir, usará o controler neutro
			if( is_file( $modular_path->fullpath ) === false ) {
				return new self( $modular_path, null, $cancel_print,
					self::STATUS_CONTROLLER_INVALID | self::STATUS_CONTROLLER_NOT_FOUND, self::RETURN_TYPE_DEFAULT, false );
			}

			// Senão, faz um require da classe solicitada
			//DEBUG: o arquivo precisa existir
			if(class_exists($modular_path->class, false) === false)
				core::do_require($modular_path->fullpath);

			// Se for chamado um método diferente do padrão, mas este não existir, usa o método padrão
			try {
				if( $modular_path->method !== $default_method
				&&  method_exists($modular_path->class, $modular_path->method) === false ) {
					if($strict_route === true) {
						return new self( $modular_path, null, $cancel_print,
							self::STATUS_CONTROLLER_INVALID | self::STATUS_METHOD_REQUIRED, self::RETURN_TYPE_DEFAULT, false );
					}

					array_unshift( $modular_path_data->remains, $modular_path->method );
					$modular_path->method = $default_method;
				}
 			}
			catch(core_exception $e) {
				$modular_path->method = $default_method;
			}

			// Por fim, cria o controller com as definições passadas
			return new $modular_path->class( $modular_path, @$modular_path_data->remains, $cancel_print,
				self::STATUS_SUCCESS, self::RETURN_TYPE_DEFAULT, $auto_execute );
		}

		// Retorna o controlador principal
		static public function _get_main_controller() {
			return self::$_main_controller;
		}
	}
