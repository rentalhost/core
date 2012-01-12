<?php

	// Núcleo geral
	class core {
		// Todas as chamadas de classes passarão por aqui
		//DEBUG: para user class, verificar se elas concordam com a pattern
		static public function __autoload( $classname ) {
			// Se for uma classe do core...
			if(substr($classname, 0, 5) === 'core_') {
				$classpath = CORE_ROOT . "/classes/{$classname}.php";

				// Após obter o caminho, inclui o arquivo
				if(!is_file($classpath)) {
					$classpath = self::get_path_fixed($classpath);
					eval("class {$classname} extends core_exception {}");
					throw new $classname("Core class \"{$classname}\" in {$classpath} not found.");
					return false;
				}

				require_once $classpath;
				return true;
			}

			// Localiza uma user class
			// Primeiro localiza a parte modular
			$classpath = core::get_modular_parts( $classname, array(
				'path_clip' => true,
				'search_paths' => false,
				'make_fullpath' => true
			) );

			// Feito isso, verifica qual será o complemento que será aplicado no fullpath
			switch( $classpath->clipped ) {
				case 'model': $classpath->fullpath .= '/models'; break;
				case 'library': $classpath->fullpath .= '/libraries'; break;
				case 'exception': $classpath->fullpath .= '/exceptions'; break;
			}

			// O próximo passo é localizar o arquivo que será incluido
			$classpath_subdata = core::get_modular_parts( $classpath->remains, array(
				'start_dir' => $classpath->fullpath,
				'search_modules' => false,
				'make_fullpath' => true
			) );

			// Após obter o caminho, inclui o arquivo
			if(!is_file($classpath_subdata->fullpath)) {
				eval("class {$classname} extends core_exception {}");
				throw new $classname("Class \"{$classname}\" in {$classpath->fullpath} not found.");
				return false;
			}

			// Inclui o arquivo
			core::do_require($classpath_subdata->fullpath);
		}

		// Retorna a URL base com sua modular (por padrão)
		static public function get_baseurl( $include_modular = true ){
			return ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' )
				. '://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['SCRIPT_NAME']) . '/'
				. ( $include_modular === false ? null
				  : join( '/', core_controller::_get_main_controller()->get_modular_data() ->modular ) . '/' );
		}

		// Obtém o path fixado
		static public function get_path_fixed( $path ) {
			return str_replace( '\\', '/', $path );
		}

		// Retorna o path limpo (útil para operações com as configurações)
		static public function get_path_clipped($path, $using_base = CORE_MODULES) {
			// Retorna o resultado gerado
			return core::get_path_fixed(substr($path, strlen($using_base) + 1));
		}

		// Separa um modular path em pedaços
		//DEBUG: verificar se uma parte colide com um arquivo ou pasta
		//DEBUG: retorna um erro se uma configuração passada não for suportada
		static public function get_modular_parts( $modular_path, $configs = null ) {
			$configs = (array) $configs;

			// Se for uma string, é necessário quebrar a informação
			if( is_string( $modular_path ) ) {
				!isset( $configs['split_by'] )			&& $configs['split_by']			= '_';
				!isset( $configs['group_by'] )			&& $configs['group_by']			= '__';
				!isset( $configs['neutral_by'] )		&& $configs['neutral_by']		= "\0";
				!isset( $configs['make_underlined'] )	&& $configs['make_underlined']	= true;

				$modular_path = str_replace( $configs['group_by'], $configs['neutral_by'], $modular_path );
				$modular_path = explode( $configs['split_by'], $modular_path );

				foreach( $modular_path as $key => $item )
					$modular_path[$key] = str_replace( $configs['neutral_by'], $configs['split_by'], $item );
			}

			// Após ter a array, é necessário fazer a busca pelos arquivos
			!isset( $configs['start_dir'] )			&& $configs['start_dir']		= CORE_MODULES;
			!isset( $configs['deep_modules'] )		&& $configs['deep_modules']		= false;
			!isset( $configs['search_modules'] )	&& $configs['search_modules']	= true;
			!isset( $configs['search_paths'] )		&& $configs['search_paths']		= true;
			!isset( $configs['path_clip'] )			&& $configs['path_clip']		= false;
			!isset( $configs['path_complement'] )	&& $configs['path_complement']	= null;
			!isset( $configs['file_extension'] )	&& $configs['file_extension']	= 'php';
			!isset( $configs['make_fullpath'] )		&& $configs['make_fullpath']	= false;
			!isset( $configs['make_underlined'] )	&& $configs['make_underlined']	= false;

			// Define o diretório de partida
			$current_path = $configs['start_dir'];

			// Prepara os resultados
			$result = new stdclass;

			// Se for necessário "clipar" o último elemento do path...
			if( $configs['path_clip'] === true ) {
				$result->clipped = array_pop( $modular_path );
			}

			// Se for necessário buscar por módulos...
			if( $configs['search_modules'] === true
			&&  empty( $modular_path ) === false ) {
				$result->modular = array();

				// Para cada parte do array, verifica se é um diretório
				$last_key = count( $modular_path ) - 1;
				foreach( $modular_path as $key => $value ) {
					// Propõe um diretório, a partir do segundo é usado um underline submodular
					$proposed_path = $current_path .
						( $key === 0 && $configs['deep_modules'] === false ? "/{$value}" : "/_{$value}" );

					// Se o diretório for aceito, adiciona aos módulos, aceita a proposta e continua a busca
					if( is_dir( $proposed_path ) ) {
						// Se for necessário dar duplo underline...
						if( $configs['make_underlined'] === true ) {
							$value = str_replace( '_', '__', $value );
						}

						// Registra a ocorrência
						$result->modular[] = $value;
						$current_path = $proposed_path;

						// Se for a última chave, limpa o modular
						if( $last_key === $key ) {
							$modular_path = array();
							break;
						}

						continue;
					}

					// Quando terminar os diretórios, remove os caminhos encontrados do modular path
					$modular_path = array_slice( $modular_path, $key );
					break;
				}

				// Se nenhum modular for cadastro, apaga a variável
				if( empty( $result->modular ) ) {
					unset( $result->modular );
				}
			}

			// Após buscar o módulo, se for necessário, anexa o complemento
			if( !empty( $configs['path_complement'] ) ) {
				$current_path .= $configs['path_complement'];
			}

			// Se for necessário buscar por paths...
			if( $configs['search_paths'] === true
			&&  empty( $modular_path ) === false ) {
				$result->path = array();

				// Para cada modular, busca pelo sub-diretório
				$last_key = count( $modular_path ) - 1;
				foreach( $modular_path as $key => $value ) {
					// Propõe um diretório
					$proposed_path = $current_path . "/{$value}";

					// Se o diretório for aceito, adiciona aos paths, aceita a proposta e continua a busca
					if( is_dir( $proposed_path ) ) {
						// Se for necessário dar duplo underline...
						if( $configs['make_underlined'] === true ) {
							$value = str_replace( '_', '__', $value );
						}

						// Registra a ocorrência
						$result->path[] = $value;
						$current_path = $proposed_path;

						// Se for a última chave, limpa o modular
						if( $last_key === $key ) {
							$modular_path = array();
							break;
						}

						continue;
					}

					// Se não for um diretório, talvez seja um arquivo
					// Propõe um arquivo
					$proposed_path .= ".{$configs['file_extension']}";

					// Se for um arquivo, adiciona aos paths
					// Ao achar um arquivo, fecha a busca e avança a chave, simulando uma nova proposta
					if( is_file( $proposed_path ) ) {
						// Se for necessário dar duplo underline...
						if( $configs['make_underlined'] === true ) {
							$value = str_replace( '_', '__', $value );
						}

						// Registra a ocorrência
						$result->path[] = $value;
						$current_path = $proposed_path;
						$key++;
					}

					// Quando terminar os diretórios, remove os caminhos encontrados do modular path
					$modular_path = array_slice( $modular_path, $key );
					break;
				}

				// Se nenhum path for cadastro, apaga a variável
				if( empty( $result->path ) ) {
					unset( $result->path );
				}
			}

			// Se sobrar alguma informação no path, armazena
			if( !empty( $modular_path ) ) {
				$result->remains = $modular_path;
			}

			// Se for necessário gerar o path completo...
			if( $configs['make_fullpath'] === true ) {
				$result->fullpath = core::get_path_fixed( realpath( $current_path ) );
			}

			// Retorna o resultado gerado
			return $result;
		}

		// Obtém o nome de uma classe de um modular parts
		static public function get_joined_class( $modular_parts, $class_sulfix = null ) {
			return join( '_', $modular_parts->modular ) . '_'
				 . join( '_', $modular_parts->path )
				 . ( $class_sulfix !== null ? "_{$class_sulfix}" : null );
		}

		// Obtém o nome do arquivo responsável pela chamada, mas que não seja do core
		static public function get_caller_path() {
			// Calcula a parte croppável da informação
			$core_root = CORE_ROOT . DIRECTORY_SEPARATOR;
			$crop_length = strlen($core_root);

			// Busca pela melhor primeira opção
			foreach(debug_backtrace() as $backtrace) {
				// Se a informação cropada for diferente do CORE_ROOT, é uma informação válida
				if(substr($backtrace['file'], 0, $crop_length) !== $core_root) {
					return core::get_path_fixed($backtrace['file']);
				}
			}
		} //DEBUG: deve acontecer um erro do tipo Exception, caso chegue até aqui

		// Obtém o caminho do módulo de onde a última chamada foi feita
		static public function get_caller_module_path() {
			// Quebra o caminho da última chamada, eliminando a parte não interessante
			$module_path = explode( '/', self::get_path_clipped( self::get_caller_path() ) );

			// Armazena a primeira informação, pois ela sempre definirá um módulo
			$result = array( array_shift( $module_path ) );

			// A partir do item 1, verifica se o valor começa por _, o que indica que é um módulo
			foreach( $module_path as $path_item ){
				// O item deve começar por _ para ser considerado um sub-módulo
				if( $path_item[0] !== '_' ) {
					break;
				}

				// Adiciona aos resultados
				$result[] = substr( $path_item, 1 );
			}

			// Retorna um array contendo o caminho da modulação
			return $result;
		}

		// Obtém o caminho escrito do módulo atual
		static public function get_current_path() {
			return core::get_path_fixed( CORE_MODULES . '/' . join( '/_', self::get_caller_module_path() ) );
		}

		// Cria uma setlist baseado na informação recebida (array)
		static public function parse_setlist($data){
			// Quando está em branco, retorna um array vazio
			if($data == null)
				return array();

			// Se for um array ou objeto, retorna apenas os valores existentes (sem chaves)
			if(is_array($data)
			|| is_object($data))
				return array_values((array) $data);

			// Se for uma string simples (sem virgula), retorna apenas um trim da informação
			if(strpos($data, ',') === false)
				return array(trim($data));

			// Em último caso, separa usando a expressão seguinte, que divide por virgula
			// Quando usado \, a informação é usada literalmente no objeto anterior
			// http://stackoverflow.com/a/6243797/755393 @NikiC
			return preg_split('~\\\\.(*SKIP)(*FAIL)|\,\s*~s', trim($data));
		}

		// Publica um arquivo, redirecinando próximos pedidos diretamente para o destino (HTTP 301)
		static public function do_publish( $publish_http ) {
			header('Cache-Control: max-age=290304000, public');
			header('Location: ' . $publish_http, true, 301);
			exit;
		}

		// Inclui um arquivo específico e passa os dados para um arquivo de configuração
		static public function do_require( $__required_file ) {
			// As configurações deverão ser salvas nesta variável
			$config = new stdclass;

			// Armazena a informação se o arquivo existe
			$__is_file = false;

			// Inclui o arquivo especificado
			$__required_file = realpath($__required_file);
			if(is_file($__required_file)) {
				$__is_file = true;
				require $__required_file;
			}

			// Salva as configurações, se necessário
			core_config::save_configs(self::get_path_clipped($__required_file), (array) $config);
			return $__is_file;
		}

		// Helper interno para filtrar um array, removendo seus itens vazios
		static public function _not_empty( $data ) {
			return !empty( $data );
		}
	}
