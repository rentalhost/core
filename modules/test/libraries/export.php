<?php

	// Exporta um resultado
	class test_export_library extends core_library {
		// Carrega o idioma da classe
		static public $_lang;

		// Exporta um resultado em HTML
		static public function export_html( $data, $exclude_header = false ) {
			switch(self::_get_type($data)) {
				case 'boolean':
					return self::_typefy_html($data[0], $data[1] ? 'true' : 'false', null, null, $exclude_header);
					break;
				case 'string':
					return self::_typefy_html($data[0], $data[1], '"', 'htmlspecialchars', $exclude_header);
					break;
				case 'object':
					return self::_typefy_html_object($data[1], $data[2]);
					break;
				default:
					return self::_typefy_html($data[0], $data[1], null, null, $exclude_header);
			}
		}

		// Prepara um resultado para o JSON
		static public function prepare_result( $data ) {
			if( is_string( $data ) ) {
				// Remover informações sobre o URL e o path local
				// Isto permite validar melhor o servidor e o localhost
				$replace_data = array(
					core::get_baseurl(false)			=> 'http://.../core/',
					core::get_path_fixed(CORE_INDEX)	=> './core'
				);

				$data = str_replace(array_keys($replace_data), array_values($replace_data), $data);
				return array('string', $data);
			}
			else
			if( is_bool( $data ) ) {
				return array('boolean', $data);
			}
			else
			if( is_int( $data ) ) {
				return array('number', $data);
			}
			else
			if( is_float( $data ) ) {
				return array('float', $data);
			}
			else
			if( is_array( $data )
			||  is_a( $data, 'stdClass' ) ) {
				$data_values = (array) $data;
				$object_type = is_array($data) ? 'array' : 'stdClass';

				foreach($data_values as &$item)
					$item = self::prepare_result($item);

				return array('object', $data_values, $object_type);
			}
			else
			if( is_object( $data ) ) {
				$data_values = array();

				if(method_exists($data, '__toString')
				&& !$data instanceof exception)
					$data_values['__toString()'] = array('string', $data->__toString());

				$object_data = (array) $data;

				if($data instanceof exception) {
					$object_data["\0*\0file"] = core::get_path_fixed($object_data["\0*\0file"]);
					unset($object_data['xdebug_message']);
					unset($object_data["\0Exception\0string"]);
					unset($object_data["\0*\0line"]);
					unset($object_data["\0Exception\0trace"]);
					unset($object_data["\0Exception\0previous"]); // PHP 5.3

					if(substr($object_data["\0*\0code"], 0, 2) === 'Cx') {
						$message_lang = lang('/core/errors/err' . substr($object_data["\0*\0code"], 2), array('en', 'pt-br'));
						$object_data["\0*\0message"] = $message_lang->get_value('error_message',
							$object_data["\0core_exception\0_error"]->get_message_args());
					}

					unset($object_data["\0core_exception\0_error"]);
				}
				else
				if($data instanceof core_language) {
					unset($object_data["\0core_language\0_lang_dir"]);
					unset($object_data["\0core_language\0_lang_order"]);
				}
				else
				if($data instanceof core_error) {
					unset($object_data["\0core_error\0_globals"]);
					unset($object_data["\0core_error\0_backtrace"]);
				}
				else
				if($data instanceof mysqli) {
					static $mysqli_keys = array('affected_rows', 'connect_errno', 'connect_error', 'errno',
						'error', 'field_count', 'info', 'insert_id', 'sqlstate', 'warning_count');

					$object_data = array();
					foreach($mysqli_keys as $key)
						$object_data[$key] = $data->{$key};
				}
				else
				if($data instanceof mysqli_result) {
					static $mysqli_result_keys = array('current_field', 'field_count', 'lengths', 'num_rows', 'type');

					$object_data = array();
					foreach($mysqli_result_keys as $key)
						$object_data[$key] = $data->{$key};
				}
				else
				if($data instanceof core_database) {
					unset($object_data["\0core_database\0_connection_string"],
						$object_data["\0core_database\0_connection_array"]);
				}
				else
				if($data instanceof core_model_row) {
					unset($object_data["\0core_model_row\0_model_instance"]);
					unset($object_data["\0core_model_row\0_from"]);
				}

				foreach($object_data as $key => $value) {
					$key = explode("\0", $key);
					$key = isset($key[2]) ? $key[2] : $key[0];
					$data_values[$key] = $data === $value
						? array('recursive', '$this')
						: self::prepare_result($value);
				}

				ksort($data_values);
				return array('object', $data_values, get_class($data));
			}
			else
			if( is_null( $data ) ) {
				return array('null', $data);
			}
			else
			if( is_resource( $data ) ) {
				return array('resource', get_resource_type($data));
			}
		}

		// Obtém o tipo básico da informação
		static private function _get_type( $value ) {
			if( is_bool( $value ) )			{ return 'boolean'; }
			else if( is_string( $value ) )	{ return 'string'; }
			else if( is_int( $value ) )		{ return 'number'; }
			else if( is_float( $value ) )	{ return 'float'; }
			else if( is_null( $value ) )	{ return 'null'; }

			// Para array, object, resource, retorna a informação #0
			return $value[0];
		}

		// Tipa uma informação para impressão em HTML
		static private function _typefy_html( $type, $data, $enclosure, $additional_method, $exclude_header ) {
			$result = null;
			if( $exclude_header === false ) {
				$result = '<div class="type-' . $type . '">';
			}

			$result.= '<span class="code-type">' . self::$_lang->get_value("type_{$type}") . '</span>';

			if( $additional_method !== null ) {
				$data = call_user_func( $additional_method, $data );
			}

			if( $enclosure !== null ) {
				$data = "{$enclosure}{$data}{$enclosure}";
			}

			$result.= '<span class="code-value">' . $data . '</span>';

			if( $exclude_header === false ) {
				$result.= '</div>';
			}

			return $result;
		}

		// Tipa uma informação de objeto (array, stdClass ou object genérico)
		static private function _typefy_html_object( $data, $object_type ) {
			$max_key_length = 1;
			foreach( $data as $key => $value ) {
				$max_key_length = max( $max_key_length, strlen($key) );
			}

			$result = '<div class="code-header special">'
						. '<span class="code-type">' . self::$_lang->type_object . '</span>'
						. '<span class="code-value object-type">' . $object_type . '</span>'
					. '</div>'
					. '<div class="code-body">';

			foreach( $data as $key => $value ) {
				$result.= '<div class="code-header type-' . self::_get_type($value) . '">'
							. '<span class="code-key">' . str_pad($key, $max_key_length, ' ', STR_PAD_RIGHT) . '</span><span>: </span>'
							. self::export_html($value, true)
						. '</div>';
			}

			$result.= '</div>';
			return $result;
		}
	}

	test_export_library::$_lang = lang('export');
