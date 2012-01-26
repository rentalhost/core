<?php

	// Define algumas constantes externas
	define('CORE_EX_QUERY_OBJECT', '/^(?<object>'.CORE_VALID_ID.')(?:\.(?<column>'.CORE_VALID_ID.')'
		. '(?:\((?<type>'.CORE_VALID_ID.')\))?)?(?:\s+as\s+(?<name>'.CORE_VALID_ID.'))?$/');

	// Esta classe é apenas para ajudar com assuntos de query
	class core_model_query {
		// Permite obter a divisão por [...]
		const	QUERY_SPLITTER			= '/(?<open>\[?\[)(?<content>(?:\\\]|[^\[\]])*)(?<close>\]?\])/';

		// Analisa uma query e divide em partes especiais
		static public function parse_query($query) {
			// Obtém o SQL informado dentro de [...]
			preg_match_all(self::QUERY_SPLITTER, $query, $query_array, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

			// O resultado será armazenado aqui
			$query_data = array();

			// Analisa o resultado obtido
			$last_offset = 0;
			foreach($query_array as $item) {
				// Armazena o offset atual
				$current_offset = $item[0][1];

				// Se o offset atual for maior que o último determinado, armazena uma string
				if($current_offset > $last_offset) {
					self::_query_push($query_data, substr($query, $last_offset, $current_offset - $last_offset));
					$last_offset = $current_offset;
				}

				// Faz a alteração de offset
				$last_offset = $current_offset + strlen($item[0][0]);

				// Se a chave inicial e final for dupla, ignora
				if($item['open'][0] === '[['
				&& $item['close'][0] === ']]') {
					self::_query_push($query_data, "[{$item['content'][0]}]");
					continue;
				}

				// Se for um modelo ou uma coluna de modelo
				if(preg_match(CORE_EX_QUERY_OBJECT, $item['content'][0], $object)) {
					$object_data = array();

					// Armazena o dado que foi gerado
					$object_string = null;

					// Se for [this...] usa um array de dados, se não, obtém a informação do modelo
					if($object['object'] === 'this') {
						self::_query_push($query_data, '`');
						self::_query_push($query_data, array('object' => 'this'));
						self::_query_push($query_data, '`');
					}
					else $object_string.= '`' . core_model::_get_linear($object['object'])->table() . '`';

					// Se a coluna for definida
					if(!empty($object['column'])) {
						$object_string.= '.`' . $object['column'] . '`';
					}

					// Se o nome for definido, mas o tipo não, basta um simples alias
					if(!empty($object['name'])
					&& empty($object['type']))
						$object_string.= ' AS `' . $object['name'] . '`';
					else
					if(!empty($object['column'])
					&& !empty($object['type'])) {
						// Sem outra situação, é necessário criar um objeto json
						$object_json = array(
							'name' => empty($object['name']) ? $object['column'] : $object['name'],
							'type' => $object['type']
						);

						// Armazena a informação
						$object_string.= ' AS `' . json_encode($object_json) . '`';
					}

					// Se for necessário...
					if($object_string !== null)
						self::_query_push($query_data, $object_string);

					continue;
				}

				//DEBUG:
				$query_data[] = null;
			}

			// Se sobrar alguma informação, adiciona
			if(strlen($query) > $last_offset)
				self::_query_push($query_data, substr($query, $last_offset));

			return $query_data;
		}

		// Adiciona uma informação na query_data ou faz concatenação
		static private function _query_push(&$query_data, $data) {
			if(is_string($data)
			&& is_string(end($query_data)))
				return $query_data[key($query_data)].= $data;

			$query_data[] = $data;
		}
	}
