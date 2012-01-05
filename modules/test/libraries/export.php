<?php

	// Exporta um resultado
	class test_export_library extends core_library {
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
			return self::_prepare_result_walker($data);
		}

		// Prepara um resultado
		static private function _prepare_result_walker( $data ) {
			if( is_string( $data ) ) {
				return array( 'string', $data );
			}
			else
			if( is_bool( $data ) ) {
				return array( 'boolean', $data );
			}
			else
			if( is_int( $data ) ) {
				return array( 'number', $data );
			}
			else
			if( is_float( $data ) ) {
				return array( 'float', $data );
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
				$data_values = get_object_vars($data);
				$object_type = get_class($data);

				foreach($data_values as &$item)
					$item = self::prepare_result($item);

				return array('object', $data_values, $object_type);
			}
			else
			if( is_null( $data ) ) {
				return array( 'null', $data );
			}
			else
			if( is_resource( $data ) ) {
				return array('resource', get_resource_type($data));
			}
		}

		// Obtém o tipo básico da informação
		static private function _get_type( $value ) {
			if( is_bool( $value ) ) { return 'boolean'; }
			else if( is_string( $value ) ) { return 'string'; }
			else if( is_int( $value ) ) { return 'number'; }
			else if( is_float( $value ) ) { return 'float'; }
			else if( is_null( $value ) ) { return 'null'; }

			// Para array, object, resource, retorna a informação #0
			return $value[0];
		}

		// Tipa uma informação para impressão em HTML
		static private function _typefy_html( $type, $data, $enclosure, $additional_method, $exclude_header ) {
			$result = null;
			if( $exclude_header === false ) {
				$result = '<div class="type-' . $type . '">';
			}

			$result.= '<span class="code-type">' . $type . '</span>';

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
						. '<span class="code-type">object</span>'
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
