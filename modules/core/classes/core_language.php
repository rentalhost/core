<?php

	// Classe de idioma
	class core_language {
		const	MATCH_LANGUAGE	= '[a-z]{2}(?:\-[a-zA-Z]{2})?';

		// Armazena a ordem padrão de idioma
		private $_lang_order;
		// Armazena o diretório de busca
		private $_lang_path;
		private $_lang_dir;

		// Armazena os dados de linguagem
		static private $_languages = array();

		// Cria um novo objeto de linguagem
		public function __construct($path, $lang_order) {
			// Obtém a ordem de idioma padrão
			if($lang_order === null)
				$lang_order = config('language_default_order');
			$this->_lang_order = self::_get_order(setlist($lang_order));

			// Armazena o caminho onde será feito a busca
			// Se o primeiro caractere for uma /, o language será buscado desde o princípio
			$deep_search = $path[0] === '/';

			// Quebra o language por barras e remove os resultados vazios
			// É necessário fazer um key reset para que o deep search funcione corretamente
			$lang_path_data = array_values(array_filter(explode('/', $path), 'core::_not_empty'));

			// Busca pelo caminho do language
			$lang_path_data = core::get_modular_parts($lang_path_data, array(
				'start_dir' => $deep_search === true
					? CORE_MODULES
					: CORE_MODULES . '/' . join('/_', core::get_caller_module_path()),
				'path_complement' => '/languages',
				'deep_modules' => $deep_search === false,
				'make_fullpath' => true
			));

			// Armazena a informação modular encontrada
			$this->_lang_path = array($lang_path_data->fullpath, join('/', $lang_path_data->remains));

			// Cria, se necessário, a variável de armazenamento de linguagem
			$lang_format = $this->get_dir_format();
			if(!isset(self::$_languages[$lang_format])) {
				self::$_languages[$lang_format] = array();
			}

			// Armazena o diretório de busca de linguagem
			$this->_lang_dir = &self::$_languages[$lang_format];
		}

		// Obtém o formato de diretório de busca
		public function get_dir_format() {
			return "{$this->_lang_path[0]}/%s/{$this->_lang_path[1]}.ini";
		}

		// Obtém o valor real de uma key
		public function get_real_value($key) {
			$lang_format = $this->get_dir_format();

			foreach($this->_lang_order as $lang_key) {
				// Se não for definido o idioma, cria a variável
				if(!isset($this->_lang_dir[$lang_key])) {
					$this->_lang_dir[$lang_key] = null;
				}

				// Se for false, ignora rapidamente
				if($this->_lang_dir[$lang_key] === false) {
					continue;
				}

				// Gera o diretório de busca
				$lang_path = sprintf($lang_format, $lang_key);

				// Se o diretório não existir, avança
				if(!is_file($lang_path)) {
					$this->_lang_dir[$lang_key] = false;
					continue;
				}

				// Se o arquivo não estiver sido carregado, o faz
				if($this->_lang_dir[$lang_key] === null) {
					$this->_lang_dir[$lang_key] = parse_ini_file($lang_path, false);
				}

				// Se a chave não existir, avança
				if(!isset($this->_lang_dir[$lang_key][$key])) {
					continue;
				}

				// Por fim, retorna a informação desejada
				return $this->_lang_dir[$lang_key][$key];
			}

			// Em último caso, retorna null
			return null;
		}

		// Obtém o valor final
		public function __get($key) {
			return $this->get_value($key);
		}

		public function __call($key, $args) {
			return $this->get_value($key, $args);
		}

		public function get_value($key, $args = array()) {
			$value = $this->get_real_value($key);

			if($value === null)
				return null;

			self::$_replace_object = $this;
			$value = preg_replace_callback('/\%' . CORE_VALID_ID . '/', 'core_language::_replace_value', $value);
			self::$_replace_object = null;

			if($args !== false) {
				array_unshift($args, $value);
				return call_user_func_array('sprintf', $args);
			}

			return $value;
		}

		// Obtém ou reconfigura a ordem de linguagem
		public function get_language_order() {
			return $this->_lang_order;
		}

		public function set_language_order($lang_order) {
			$this->_lang_order = self::_get_order(setlist($lang_order));
			return $this;
		}

		/** OBJETO */
		// Obtém e organiza a lista de idiomas
		static private function _get_order($order) {
			$reorder = array();

			foreach($order as $item) {
				// Obtém a definição em $_REQUEST, se disponível
				if($item === 'request') {
					$request_key = config('language_request_key');

					if($request_key !== false
					&& isset($_REQUEST[$request_key])
					&& preg_match('/^' . self::MATCH_LANGUAGE . '$/', $_REQUEST[$request_key])) {
						// Se o autosync estiver ligado, armazena a informação em uma sessão
						$session_key = config('language_session_key');
						if($session_key !== false
						&& config('language_session_autosync') === true) {
							if(!isset($_SESSION))
								session_start();

							$_SESSION[$session_key] = $_REQUEST[$request_key];
						}

						$reorder[] = $_REQUEST[$request_key];
					}

					continue;
				}
				else
				// Obtém a definição em $_SESSION, se disponível
				if($item === 'session') {
					$session_key = config('language_session_key');

					if($session_key === false)
						continue;

					if(!isset($_SESSION))
						session_start();

					if(isset($_SESSION[$session_key])
					&& preg_match('/^' . self::MATCH_LANGUAGE . '$/', $_SESSION[$session_key])) {
						$reorder[] = $_SESSION[$session_key];
					}

					continue;
				}
				else
				// Obtém a definição do navegador
				if($item === 'auto') {
					if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
						preg_match_all('/' . self::MATCH_LANGUAGE . '/', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_matches);
						foreach($lang_matches[0] as $lang)
							$reorder[] = strtolower($lang);
					}

					continue;
				}

				$reorder[] = $item;
			}

			$reorder = array_unique($reorder);
			return $reorder;
		}

		// Obtém os idiomas disponíveis em um path
		static public function get_available($path = null, $lang_order = null) {
			// Se o path não for definido, usa o path atual
			if($path === null) {
				$path = core::get_path_fixed(CORE_MODULES) . '/' .
					join('/_', core::get_caller_module_path()) . '/languages';
			}
			else {
				// Armazena o caminho onde será feito a busca
				// Se o primeiro caractere for uma /, o language será buscado desde o princípio
				$deep_search = $path[0] === '/';

				// Quebra o language por barras e remove os resultados vazios
				// É necessário fazer um key reset para que o deep search funcione corretamente
				$lang_path_data = array_values(array_filter(explode('/', $path), 'core::_not_empty'));

				// Busca pelo caminho do language
				$lang_path_data = core::get_modular_parts($lang_path_data, array(
					'start_dir' => $deep_search === true
						? CORE_MODULES
						: CORE_MODULES . '/' . join('/_', core::get_caller_module_path()),
					'path_complement' => '/languages',
					'deep_modules' => $deep_search === false,
					'make_fullpath' => true
				));

				// Reconfigura o path
				$path = $lang_path_data->fullpath;
			}

			// Se a pasta não existir, retorna null
			//DEBUG: lançar uma exceção
			if(!is_dir($path))
				return null;

			// Define a ordem de linguagem
			$lang_order_data = $lang_order === true ? null : $lang_order;

			// Obtém a tradução dos idiomas
			$lang = lang('/core/languages', $lang_order_data);

			// Obtém as pastas
			$dir_res = opendir($path);
			$dir_list = array();
			while($dir = readdir($dir_res)) {
				if($dir !== '.'
				&& $dir !== '..'
				&& is_dir("{$path}/{$dir}")) {
					// Se a ordem de linguagem for true, usa a tradução local
					if($lang_order === true)
						$lang->set_language_order(array($dir, 'en'));

					$dir_list[$dir] = $lang->get_value($dir);
				}
			}

			ksort($dir_list);
			return $dir_list;
		}

		// Localiza e substitui strings especiais
		//NOTE: deve funcionar assim devido a limitações do PHP 5.2
		static private $_replace_object = array();
		static private $_replace_args = array();
		static private function _replace_value($matches) {
			$value = self::$_replace_object->get_value(substr($matches[0], 1), false);
			return $value === null ? $matches[0] : $value;
		}
	}
