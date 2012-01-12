<?php

	// Classe para Views
	class core_view {
		// Indica que tudo ocorreu perfeitamente
		const   STATUS_SUCCESS				= 0;
		// Indica que a view solicitada não foi encontrada
		const   STATUS_VIEW_NOT_FOUND		= 1;
		// Indica que a view solicitada é inválida
		const   STATUS_VIEW_IS_INVALID		= 2;
		// Indica que a view está vazia
		const   STATUS_VIEW_IS_EMPTY		= 4;
		// Indica que a view é insegura ou inválida
		const   STATUS_VIEW_IS_INSECURE		= 8;
		// Indica que a view solicitada deixou "restos"
		const   STATUS_VIEW_HAS_REMAINS		= 16;

		/** OBJETO */
		// Armazena o caminho e a view proposta
		private $_path;
		private $_proposed_view;
		// Armazena a informação modular, se disponível
		private $_modular_data;
		// Armazena o status da operação
		private $_status 			= self::STATUS_SUCCESS;
		// Armazena o resultado retornado pela execução
		private $_result_data;
		// Armazena o conteúdo gerado pela função
		private $_result_contents	= "";
		// Armazena a informação se o conteúdo foi impresso
		private $_result_printed	= false;

		// Cria uma nova view
		//TODO: penser em uma forma de proteger views estrangeiras (site//hello_world)
		public function __construct($view_path, $view_args, $cancel_print) {
			// Corrige o path solicitado
			$this->_proposed_view = core::get_path_fixed( $view_path );

			// Se o path estiver vazio é invalidado
			if( empty( $this->_proposed_view ) ) {
				$this->_status = self::STATUS_VIEW_IS_INVALID | self::STATUS_VIEW_IS_EMPTY | self::STATUS_VIEW_NOT_FOUND;
				return;
			}

			// Se o path for inseguro ou mesmo inválido
			if( preg_match( CORE_VALID_PATH, $this->_proposed_view ) === 0 ) {
				$this->_status = self::STATUS_VIEW_IS_INVALID | self::STATUS_VIEW_IS_INSECURE | self::STATUS_VIEW_NOT_FOUND;
				return;
			}

			// Se o primeiro caractere for uma /, a view será buscada desde o princípio
			$deep_search = $this->_proposed_view[0] === '/';

			// Quebra a view por barras e remove os resultados vazios
			// É necessário fazer um key reset para que o deep search funcione corretamente
			$view_path_data = array_values( array_filter( explode( '/', $this->_proposed_view ), 'core::_not_empty' ) );

			// Busca pelo caminho da view
			$view_path_data = core::get_modular_parts( $view_path_data, array(
				'start_dir' => $deep_search === true
					? CORE_MODULES
					: CORE_MODULES . '/' . join( '/_', core::get_caller_module_path() ),
				'path_complement' => '/views',
				'deep_modules' => $deep_search === false,
				'make_fullpath' => true
			) );

			// Armazena a informação modular encontrada
			$this->_modular_data = $view_path_data;

			// Se a busca retornar restos invalida
			if( isset( $view_path_data->remains ) ) {
				$this->_status = self::STATUS_VIEW_IS_INVALID | self::STATUS_VIEW_HAS_REMAINS | self::STATUS_VIEW_NOT_FOUND;
				return;
			}

			// Define o fullpath proposto
			$this->_path = $view_path_data->fullpath;

			// Se o arquivo proposto não existir é inválido
			if( is_file( $this->_path ) === false ) {
				$this->_status = self::STATUS_VIEW_IS_INVALID | self::STATUS_VIEW_NOT_FOUND;
				return;
			}

			// Se nenhuma invalidação ocorreu, o arquivo é finalmente chamado
			$this->_load_view($view_args);

			// Por fim, imprime o conteúdo, se for necessário
			$this->_result_printed = !$cancel_print;
			if($cancel_print === false) {
				echo $this->_result_contents;
			}
		}

		// Carrega a view proposta
		private function _load_view( $__args ) {
			// Se args for um array, exporta seus dados dentro da view
			if( is_array( $__args ) ) {
				extract( $__args, EXTR_REFS );
			}

			// Nesta etapa, todos os dados importantes deverão estar disponíveis
			ob_start();
			$this->_result_data = include( $this->_path );
			$this->_result_contents = ob_get_contents();
			ob_end_clean();
		}

		// Imprime o conteúdo gerado
		public function render() {
			$this->_result_printed = true;

			echo $this->_result_contents;
			return $this;
		}

		// Se o arquivo for requerido, ele não poderá ter nenhum erro
		public function required() {
			if( $this->_status !== self::STATUS_SUCCESS ) {
				if(($this->_status & self::STATUS_VIEW_IS_INSECURE) === self::STATUS_VIEW_IS_INSECURE) {
					throw new core_exception("View is not valid because it is insecure: \"{$this->_proposed_view}\".");
				}
				else
				if(($this->_status & self::STATUS_VIEW_HAS_REMAINS) === self::STATUS_VIEW_HAS_REMAINS) {
					$remains = join('/', $this->_modular_data->remains);
					throw new core_exception("View is not valid because it have remains on path definition: \"{$remains}\".");
				}
				else
				if(($this->_status & self::STATUS_VIEW_IS_EMPTY) === self::STATUS_VIEW_IS_EMPTY) {
					throw new core_exception("View is not valid because it is empty.");
				}
				else
				if(($this->_status & self::STATUS_VIEW_NOT_FOUND) === self::STATUS_VIEW_NOT_FOUND) {
					throw new core_exception("View is not valid because it is a dir: \"{$this->_path}\".");
				}
			}

			return $this;
		}

		// Retorna true se a view existir
		public function exists() {
			return ($this->_status & self::STATUS_VIEW_NOT_FOUND) !== self::STATUS_VIEW_NOT_FOUND;
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

		// Obtém a informação modular
		public function get_modular_data() {
			return $this->_modular_data;
		}
	}
