<?php

	// Controla as informações das classes
	class test_class_library extends core_library {
		// Armazena os arquivos de resultado
		static private $_files = array();

		// Tipo numerico / prioridade
		static private $_priority = array(
			'unavailable' => 0,
			'success'	=> 1,
			'new'		=> 2,
			'removed'	=> 3,
			'failed'	=> 4,
			'exception'	=> 5
		);

		// Obtém informações sobre as classes existentes
		static public function get_all() {
			library( '__dir' );

			$results = array();
			$loaded_classes = get_declared_classes();

			$current_path = core::get_current_path();

			$files = call('__dir::get_files', $current_path . '/results', false, '/\.valid$/');
			foreach( $files as $file ) {
				$file_id = substr(array_pop(array_slice(explode('/', $file), -1)), 0, -6);
				$file_data = explode('.', $file_id);
				self::$_files[$file_data[0]][] = array($file_id, $file_data, $file);
			}

			$old_classes = array_flip(array_keys(self::$_files));

			foreach( call('__dir::get_files', $current_path . '/classes') as $file )
				require_once $file;

			$loaded_classes = array_diff( get_declared_classes(), $loaded_classes );
			foreach( $loaded_classes as $item ) {
				$classname = substr($item, 5, -8);
				$results[$classname] = self::_get_class_data($item);
				unset($old_classes[$classname]);
			}

			foreach($old_classes as $old_class => $NIL) {
				$old_cases = array();
				foreach(self::$_files[$old_class] as $old_case)
					$old_cases[$old_case[0]] = array(
						'id' => $old_case[0],
						'method' => $old_case[1][1],
						'prefix' => $old_case[1][2],
						'index' => (int) $old_case[1][3],
						'type' => 'removed',
						'result' => json_decode(file_get_contents($old_case[2]))
					);

				$results[$old_class] = array(
					'classname' => str_replace('__', '_', $old_class),
					'type' => 'removed',
					'methods' => $old_cases
				);
			}

			return $results;
		}

		// Obtém informações sobre uma classe
		static private function _get_class_data( $item ) {
			$obj = new ReflectionClass($item);
			$obj_methods = $obj->getMethods(ReflectionMethod::IS_PUBLIC);

			$data = array();
			foreach( $obj_methods as $key => $value ) {
				if( substr( $value->getName(), 0, 5 ) === 'test_' ) {
					self::_run_tests($data, new $item, $value->getName());
				}
			}

			$classname = substr($item, 5, -8);
			if(isset(self::$_files[$classname]))
			foreach(self::$_files[$classname] as $key => $value) {
				if(array_key_exists($value[0], $data)) {
					unset(self::$_files[$classname][$key]);
					continue;
				}

				$data[$value[0]] = array(
					'id' => $value[0],
					'method' => $value[1][1],
					'prefix' => $value[1][2],
					'index' => $value[1][3],
					'type' => 'removed',
					'result' => json_decode(file_get_contents($value[2]), true)
				);
			}

			$max_priority = 0;
			foreach($data as $key => $value) {
				$data_priority[$key] = self::$_priority[$value['type']];
				$max_priority = max($max_priority, $data_priority[$key]);

				$data_method[$key] = $value['method'];
				$data_prefix[$key] = $value['prefix'];
				$data_index[$key] = $value['index'];
			}

			array_multisort($data_priority, SORT_DESC,
							$data_method, SORT_ASC,
							$data_prefix, SORT_STRING,
							$data_index, SORT_NUMERIC,
							$data);

			return array(
				'classname' => str_replace('__', '_', $classname),
				'type' => array_search($max_priority, self::$_priority),
				'methods' => $data
			);
		}

		// Executa os métodos de um unit
		static private function _run_tests( &$results, $class, $method ) {
			$class->_id_class = substr( get_class( $class ), 5, -8 );
			$class->_id_method = substr( $method, 5 );

			call_user_func( array( $class, $method ) );

			$results = array_merge( $results, $class->_results );
		}

		// Aceita um resultado
		static public function accept_result( $id ) {
			// Primeiro testa se o ID é válido
			if( preg_match( '/^(?:' . CORE_VALID_ID . '\.?)*$/' ) === 0 )
				return false;

			// Se for, remove o resultado atual, se existir, e move o novo resultado sobre este
			$file = core::get_current_path() . "/results/{$id}";
		   @unlink("{$file}.valid");
			rename("{$file}.last", "{$file}.valid");

			return true;
		}

		// Rejeita um resultado
		static public function reject_result( $id ) {
			// Primeiro testa se o ID é válido
			if( preg_match( '/^(?:' . CORE_VALID_ID . '\.?)*$/' ) === 0 )
				return false;

			// A rejeição é apenas a remoção do arquivo .valid do ID referente
		   @unlink(core::get_current_path() . "/results/{$id}.valid");

			return true;
		}

		// Identificadores de um unit
		private $_id_class,
				$_id_method,
				$_id_prefix = 'default',
				$_results = array();

		// Altera o prefixo atual
		public function set_prefix( $prefix ) {
			$this->_id_prefix = $prefix;
		}

		// Executa um teste e armazena o seu resultado
		public function test( $index, $result, $comment = null ) {
			$id = "{$this->_id_class}.{$this->_id_method}.{$this->_id_prefix}.{$index}";

			if( isset($this->_results[$id]) ) {
				$this->_results[$id] = array(
					'id' => $id,
					'method' => $this->_id_method,
					'prefix' => $this->_id_prefix,
					'index' => $index,
					'type' => 'exception',
					'message' => 'O index #' . $index . ' já está sendo utilizado.'
				);
				return;
			}

			$this->_results[$id] = array(
				'id' => $id,
				'method' => $this->_id_method,
				'prefix' => $this->_id_prefix,
				'index' => $index
			);

			$result = call( '__export::prepare_result', $result );
			$result_type = 'new';

			$file = core::get_current_path() . "/results/{$id}";
			file_put_contents("{$file}.last", json_encode($result));

			if( is_file("{$file}.valid") ) {
				$old_result = json_decode( file_get_contents("{$file}.valid"), true );

				if( $old_result === $result )
					$result_type = 'success';
				else {
					$result_type = 'failed';
					$result = array(
						'old' => $old_result,
						'new' => $result
					);
				}
			}

			$this->_results[$id]+= array(
				'type' => $result_type,
				'result' => $result,
				'message' => $comment
			);
		}

		// Executa um grupo de testes somente se houver suporte
		public function case_test( $expression, $callback, $message = null ) {
			$id = "{$this->_id_class}.{$this->_id_method}.{$this->_id_prefix}.{$callback}";

			if( $expression === false ) {
				$this->_results[$id] = array(
					'id' => $id,
					'method' => $this->_id_method,
					'prefix' => $this->_id_prefix,
					'index' => 0,
					'type' => 'unavailable',
					'message' => $message
				);
				return;
			}

			call_user_func( array( $this, $callback ) );
		}
	}
