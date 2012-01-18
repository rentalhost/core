<?php

	// Executa métodos helper e library, além de carregar estas classes, além de model
	class core_caller {
		// Armazena as classes que já foram carregadas para evitar re-processamento
		static private $_loaded_classes = array();
		// Armazena uma instância do caller
		static private $_caller = null;

		// Inicia uma chamada estática de um helper ou library
		static public function do_call( $command, $arguments ) {
			// Separa o objeto do método
			if( preg_match( CORE_VALID_CALLER, $command, $command_details ) === 1 ) {
				// Se um objeto for definido, significa que é uma chamada a um método estático de uma library
				if( !empty( $command_details['object'] ) ) {
					// Carrega a biblioteca, se necessário
					$command_details['object'] = self::load_library( $command_details['object'] );

					// Executa a chamada e retorna a informação obtida
					return call_user_func_array( array( $command_details['object'], $command_details['method'] ), $arguments );
				}

				// Executa a chamada e retorna a informação obtida
				return call_user_func_array( self::load_helper( $command_details['method'] ), $arguments );
			}

			// Se não for possível, lança uma exceção
			$error = new core_error('Cx2001',
				array('arguments' => $arguments, 'command_format' => CORE_VALID_CALLER),
				array('command' => $command));
			$error->run();
		}

		// Carrega uma library e retorna seu nome completo
		//DEBUG: informa um erro se o método chamado na library não existir
		static public function load_library( $library ) {
			// Primeiramente é necessário identificar se é uma classe no próprio módulo
			// Para isto, o objeto deve iniciar com duplo underline
			// Exemplo: __exemplo passará a ser site_exemplo_library
			if( substr( $library, 0, 2 ) === '__' ) {
				// Se for, a informação é substituida pelo módulo
				$library = join( '_', core::get_caller_module_path() ) . '_' . substr( $library, 2 );
			}

			// Anexa o sulfixo das bibliotecas
			$library .= '_library';

			// Se a classe já tiver sido carregada, evita o processo
			if( isset( self::$_loaded_classes[$library] ) ) {
				return $library;
			}

			// Agora, é necessário carregar tal classe
			spl_autoload_call( $library );

			// Salva em loaded classes e retorna
			self::$_loaded_classes[$library] = true;
			return $library;
		}

		// Carrega um helper e retorna seu nome completo
		static public function load_helper($helper) {
			// Primeiramente é necessário identificar se é um helper do próprio módulo
			// Para isto, o helper deve iniciar com duplo underline
			// Exemplo: __exemplo passará a ser site_exemplo
			if( substr( $helper, 0, 2 ) === '__' ) {
				// Se for, a informação é substituida pelo módulo
				$helper = join( '_', core::get_caller_module_path() ) . '_' . substr( $helper, 2 );
			}

			// Agora é necessário localizar o helper e carregá-lo
			$modular_path = core::get_modular_parts($helper, array(
				'path_complement' => '/helpers',
				'make_fullpath' => true
			));

			// Se o helper já tiver sido carregado, evita o processo
			$helper_key = $modular_path->fullpath . ":helper";
			if( isset( self::$_loaded_classes[$helper_key] ) ) {
				return $helper;
			}

			// Inclui o arquivo
			core::do_require($modular_path->fullpath);

			// Salva em loaded classes e retorna
			self::$_loaded_classes[$helper_key] = true;
			return $helper;
		}

		// Obtém uma instância do caller
		static public function get_instance() {
			// Se o caller não for definido, carrega
			if( self::$_caller === null )
				return self::$_caller = new self();

			return self::$_caller;
		}

		/** CALLER */
		// Armazena o nome de uma biblioteca
		private $_library = null;

		// Cria um novo objeto
		public function __construct($library = null) {
			$this->_library = $library;
		}

		// Faz uma chamada direta em helper
		public function __call($func, $args) {
			return $this->_library === null
				? call_user_func_array(self::load_helper($func), $args)
				: call_user_func_array(array($this->_library, $func), $args);
		}

		// Faz uma chamada estática em uma library
		public function __get($lib) {
			return new self(self::load_library($lib));
		}
	}
