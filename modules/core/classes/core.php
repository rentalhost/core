<?php

	// Núcleo geral
	class core {
		// Todas as chamadas de classes passarão por aqui
		static public function __autoload( $classname ) {
			// Se for uma classe do core...
			if(substr($classname, 0, 5) === 'core_') {
				$classpath = CORE_ROOT . "/classes/{$classname}.php";

				// Após obter o caminho, inclui o arquivo
				if(!is_file($classpath)) {
					$classpath = self::get_path_fixed($classpath);

					$error = new core_error('CxFFF0', null, array(
						'classname' => $classname,
						'classpath' => $classpath
					));
					$error->run_special_exception();
				}

				require_once $classpath;
				return true;
			}

			// Localiza uma user class
			// Primeiro localiza a parte modular
			$classpath = self::get_modular_parts( $classname, array(
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
			$classpath_subdata = self::get_modular_parts( $classpath->remains, array(
				'start_dir' => $classpath->fullpath,
				'search_modules' => false,
				'make_fullpath' => true
			) );

			// Após obter o caminho, inclui o arquivo
			if(!is_file($classpath_subdata->fullpath)) {
				$error = new core_error('CxFFF1', null, array(
					'classname' => $classname,
					'classpath' => $classpath->fullpath
				));
				$error->run_special_exception();
			}

			// Inclui o arquivo
			self::do_require($classpath_subdata->fullpath);

			// Se for um modelo, é necessário iniciá-lo
			if($classpath->clipped === 'model')
				core_model::_get_instance($classname);
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
			return self::get_path_fixed(substr($path, strlen($using_base) + 1));
		}

		// Obtém o estado booleano de uma informação
		static public function get_state($info, $additional_states = null) {
			//NOTE: esta é a verificação direta do PHP
			if(!is_string($info))
				return (bool) $info;

			$on_mode = array('true', 'on', 'yes') + setlist($additional_states);
			return in_array($info, $on_mode);
		}

		// Separa um modular path em pedaços
		static public function get_modular_parts($modular_path, $configs = null) {
			$configs = (array) $configs;

			// Aplica o modular path
			!isset( $configs['modular_path_auto'] )	&& $configs['modular_path_auto']	= false;

			// Se for uma string, é necessário quebrar a informação
			if( is_string( $modular_path ) ) {
				!isset( $configs['split_by'] )			&& $configs['split_by']				= '_';
				!isset( $configs['group_by'] )			&& $configs['group_by']				= '__';
				!isset( $configs['neutral_by'] )		&& $configs['neutral_by']			= "\0";
				!isset( $configs['make_underlined'] )	&& $configs['make_underlined']		= true;

				$modular_path = str_replace( $configs['group_by'], $configs['neutral_by'], $modular_path );
				$modular_path = explode( $configs['split_by'], $modular_path );

				foreach( $modular_path as $key => $item )
					$modular_path[$key] = str_replace( $configs['neutral_by'], $configs['split_by'], $item );

				if($configs['modular_path_auto'] === true
				&& $modular_path[0][0] === '_')
					$modular_path[0] = substr($modular_path[0], 1);
			}
			// Transforma o path em array
			else
			if($modular_path === null) {
				$modular_path = array();
			}

			// Após ter a array, é necessário fazer a busca pelos arquivos
			!isset( $configs['start_dir'] )			&& $configs['start_dir']			= CORE_MODULES;
			!isset( $configs['search_modules'] )	&& $configs['search_modules']		= true;
			!isset( $configs['search_paths'] )		&& $configs['search_paths']			= true;
			!isset( $configs['path_repeat'] )		&& $configs['path_repeat']			= true;
			!isset( $configs['path_clip'] )			&& $configs['path_clip']			= false;
			!isset( $configs['path_clip_empty'] )	&& $configs['path_clip_empty']		= false;
			!isset( $configs['path_extension'] )	&& $configs['path_extension']		= null;
			!isset( $configs['file_extension'] )	&& $configs['file_extension']		= 'php';
			!isset( $configs['make_fullpath'] )		&& $configs['make_fullpath']		= false;
			!isset( $configs['make_underlined'] )	&& $configs['make_underlined']		= false;

			// Em modo depuração, verifica se alguma configuração não suportada foi definida
			if(CORE_DEBUG === true) {
				static $config_keys = array('modular_path_auto', 'split_by', 'group_by', 'neutral_by',
					'make_underlined', 'start_dir', 'search_modules', 'search_paths', 'path_repeat',
					'path_clip', 'path_clip_empty', 'path_extension', 'file_extension', 'make_fullpath');

				$config_diff = array_diff(array_keys($configs), $config_keys);
				if(!empty($config_diff)) {
					$error = new core_error('Cx2000', null, array('unknow_keys' => $config_diff));
					$error->run();
				}
			}

			// Define o diretório de partida
			$current_path = $configs['start_dir'];

			// Prepara os resultados
			$result = new stdclass;

			// Se for necessário "clipar" o último elemento do path...
			if( $configs['path_clip'] === true ) {
				$result->clipped = array_pop( $modular_path );
			}

			// Se necessário, remove o último elemento do path se ele estiver vazio
			if($configs['path_clip_empty'] === true
			&& end($modular_path) === '')
				array_pop($modular_path);

			// Repete a última definição do path, se possível
			$last_try = $configs['path_repeat'];
			$using_last_try = false;

			// Se necessário, aplica a raiz do módulo
			if($configs['modular_path_auto'] === true) {
				if(empty($modular_path[0]))
					array_shift($modular_path);
				else
				$modular_path = array_merge(self::get_caller_module_path(), $modular_path);
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
						( $key === 0 ? "/{$value}" : "/_{$value}" );

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

				// Se for necessário repetir
				if($last_try === true
				&& empty($modular_path)
				&& !empty($result->modular)) {
					$last_try = false;
					$using_last_try = true;
					array_unshift($modular_path, end($result->modular));
				}
			}

			// Após buscar o módulo, se for necessário, anexa o complemento
			if( !empty( $configs['path_extension'] ) ) {
				$current_path .= $configs['path_extension'];
			}

			// Se for necessário buscar por paths...
			if( $configs['search_paths'] === true
			&&  empty( $modular_path ) === false ) {
				$result->path = array();

				// Para cada modular, busca pelo sub-diretório
				while(true) {
					$last_key = count( $modular_path ) - 1;

					foreach( $modular_path as $key => $item_value ) {
						$value = $item_value;

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
							if($last_key === $key) {
								// Em última chance, verifica se pode ser um arquivo
								if($last_try === true) {
									$proposed_file = "{$current_path}/{$item_value}.{$configs['file_extension']}";
									if(is_file($proposed_file)) {
										$last_try = false;
										$result->path[] = $value;
										$current_path = $proposed_file;

										// Adiciona um informativo
										$result->repeated = true;
									}
								}

								$modular_path = array();
								break 2;
							}

							continue;
						}

						// Propõe um arquivo
						$proposed_file = $proposed_path . ".{$configs['file_extension']}";

						// Se for um arquivo, adiciona aos paths
						// Ao achar um arquivo, fecha a busca e avança a chave, simulando uma nova proposta
						if( is_file( $proposed_file ) ) {
							// Se for necessário dar duplo underline...
							if( $configs['make_underlined'] === true ) {
								$value = str_replace( '_', '__', $value );
							}

							// Registra a ocorrência
							$result->path[] = $value;
							$current_path = $proposed_file;
							$key++;

							$last_try = false;
						}

						// Quando terminar os diretórios, remove os caminhos encontrados do modular path
						$modular_path = array_slice( $modular_path, $key );
						break 2;
					}
				}

				// Se nenhum path for cadastro, apaga a variável
				if( empty( $result->path ) ) {
					unset( $result->path );
				}
			}

			// Se sobrar alguma informação no path, armazena
			if($using_last_try === false) {
				if(!empty($modular_path))
					$result->remains = $modular_path;
			}
			else
			if(empty($modular_path))
				$result->repeated = true;

			// Se for necessário gerar o path completo...
			if( $configs['make_fullpath'] === true ) {
				$result->fullpath = self::get_path_fixed( realpath( $current_path ) );
			}

			// Retorna o resultado gerado
			return $result;
		}

		// Obtém o nome de uma classe de um modular parts
		static public function get_joined_class( $modular_parts, $class_sulfix = null ) {
			if(isset($modular_parts->repeated))
				array_pop($modular_parts->path);

			return join('_', $modular_parts->modular)
				 . (!empty($modular_parts->path) ? '_' . join('_', $modular_parts->path) : null)
				 . ($class_sulfix !== null ? "_{$class_sulfix}" : null);
		}

		// Obtém o nome do arquivo responsável pela chamada, mas que não seja do core
		static public function get_caller_path() {
			// Calcula a parte croppável da informação
			$core_root = CORE_ROOT . DIRECTORY_SEPARATOR;
			$crop_length = strlen($core_root);

			// Busca pela melhor primeira opção
			foreach(debug_backtrace() as $backtrace) {
				// Se a informação cropada for diferente do CORE_ROOT, é uma informação válida
				if(isset($backtrace['file'])
				&& substr($backtrace['file'], 0, $crop_length) !== $core_root) {
					return self::get_path_fixed($backtrace['file']);
				}
			}
		}

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
			return self::get_path_fixed( CORE_MODULES . '/' . join( '/_', self::get_caller_module_path() ) );
		}

		// Obtém um hash baseado no URL base
		static public function get_hash($include_modular = false) {
			return md5(baseurl($include_modular));
		}

		// Obtém uma sessão
		static public function &get_session($index_name = '', $set_value = -1) {
			// Se for necessário, aplica o prefixo na sessão
			$session_prefix = config('session_prefix');
			if($session_prefix !== false) {
				// Se for true, cria um prefixo automaticamente
				if($session_prefix === true) {
					$index_name = 'core_' . self::get_hash(config('session_prefix_modular')) . '::' . $index_name;
				}
				// Senão, apenas prefixa o índice
				else {
					$index_name = $session_prefix . '::' . $index_name;
				}
			}

			// Se o índice da sessão não existir, cria
			if(!isset($_SESSION[$index_name]))
				$_SESSION[$index_name] = new stdclass;
			else
			// Redefine a informação da sessão, como limpá-la
			if($set_value !== -1)
				$_SESSION[$index_name] = $set_value;

			// Por fim, retorna a sessão
			return $_SESSION[$index_name];
		}

		// Cria uma setlist baseado na informação recebida (array)
		static public function parse_setlist($data){
			// Quando está em branco, retorna um array vazio
			if($data == null)
				return array();

			// Se for um array ou objeto, retorna apenas os valores existentes (sem chaves)
			if(is_array($data))
				return array_values($data);

			// Se for uma string simples (sem virgula), retorna apenas um trim da informação
			if(strpos($data, ',') === false)
				return array(trim($data));

			// Em último caso, separa usando a expressão seguinte, que divide por virgula
			// Quando usado \, a informação é usada literalmente no objeto anterior
			// http://stackoverflow.com/a/6243797/755393 @NikiC
			return preg_split('~\\\\.(*SKIP)(*FAIL)|\,\s*~s', trim($data));
		}

		// Retorna true se o domínio for compatível
		static public function is_domain($domains) {
			$current_uri = null;

			// Para cada domínio na lista...
			foreach(setlist($domains) as $domain) {
				// Define o schema do dominio
				$domain = explode('://', $domain, 2);
				if(!isset($domain[1]))
					array_unshift($domain, 'http');

				// Verifica se o scheme é compatível
				$scheme_url = isset($_SERVER['HTTPS']) ? 'https' : 'http';
				if($domain[0] !== $scheme_url)
					continue;

				// Verifica a porta do domínio
				$domain = explode(':', $domain[1], 2);
				if(isset($domain[1])
				&& $domain[1] !== $_SERVER['SERVER_PORT'])
					continue;

				// Verifica o path
				$domain = explode('/', $domain[0], 2);
				if(isset($domain[1])) {
					// Armazena o URI de chamada
					if($current_uri === null)
					$current_uri = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL']
						: array_shift(explode('?', $_SERVER['REQUEST_URI'], 2));

					if($current_uri !== "/{$domain[1]}")
						continue;
				}

				// Verifica o hostname
				if($domain[0] !== $_SERVER['SERVER_NAME']) {
					// Verificação simplificada
					if(preg_match(CORE_HOSTNAME_VALID, $domain[0]))
						continue;

					// Verificação avançada
					if(!preg_match(self::_replace_domain($domain[0]), $_SERVER['SERVER_NAME']))
						continue;
				}

				// Se tudo foi validado corretamente, retorna true
				return true;
			}

			return false;
		}

		// Retorna se for localhost
		static public function is_localhost() {
			return self::is_domain(array('127.0.0.1', '[::1]', 'localhost'));
		}

		// Retorna se um módulo existe
		static public function has_module($module_path) {
			$module_path = self::get_modular_parts(explode('/', $module_path), array(
				'search_paths' => false,
				'modular_path_auto' => true
			));
			return !isset($module_path->remains);
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

		// Faz os replaces dos dominios
		static public function _replace_domain($domain) {
			$domain = preg_replace('/\*(?!\?)/', CORE_HOSTNAME_WORD, $domain);
			$domain = str_replace(array('.*?', '*?.', '.'),
				array('(.'.CORE_HOSTNAME_WORD.')?', '('.CORE_HOSTNAME_WORD.'.)?', '\\.'), $domain);
			$domain = preg_replace('/('.CORE_HOSTNAME_WORD.'\?(\\\.)|(\\\.)'.CORE_HOSTNAME_WORD.'\?)/', '($2$3$4$5)?', $domain);
			return "/^{$domain}$/";
		}
	}
