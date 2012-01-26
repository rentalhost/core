<?php

	// Define algumas constantes externas
	define('CORE_EX_QUERY_COLUMN',
		'(?<column>'.CORE_VALID_ID.')(?:\((?<type>_*'.CORE_VALID_ID.')\))?(?:\s+as\s+(?<name>'.CORE_VALID_ID.'))?');
	define('CORE_EX_QUERY_OBJECT', '/^(?<object>_*'.CORE_VALID_ID.')(?:\.'.CORE_EX_QUERY_COLUMN.')?$/');
	define('CORE_EX_QUERY_MULTI', '/^(?<object>_*'.CORE_VALID_ID.'):\s*(?!\,)(?<columns>(?:(?:\,\s*)?('.CORE_EX_QUERY_COLUMN.'))+\s*)$/');

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

				// Se for this, retorna um valor rápido
				if($item['content'][0] === 'this') {
					self::_query_push($query_data, array('this'));
					continue;
				}

				// Se for this.* ou somente *
				if($item['content'][0] === 'this.*'
				|| $item['content'][0] === '*') {
					self::_query_push($query_data, array('this'));
					self::_query_push($query_data, '.*');
					continue;
				}

				// Se for um modelo ou uma coluna de modelo
				if(preg_match(CORE_EX_QUERY_OBJECT, $item['content'][0], $object)) {
					$object_string = self::_id_string($query_data, $object);
					self::_query_push($query_data, self::_column_string($object, $object_string));
					continue;
				}

				// Analisa um sql multi-colunas
				if(preg_match(CORE_EX_QUERY_MULTI, $item['content'][0], $object)) {
					preg_match_all('/' . CORE_EX_QUERY_COLUMN . '/', $object['columns'], $object_columns, PREG_SET_ORDER);

					foreach($object_columns as $key => $column) {
						// A partir da key 1, adiciona ,
						if($key > 0)
							self::_query_push($query_data, ', ');

						// Adiciona o tipo principal da informação
						$object_string = self::_id_string($query_data, $object);
						self::_query_push($query_data, self::_column_string($column, $object_string));
					}

					continue;
				}

				//DEBUG: exibirá um erro, caso a informação passada não seja reconhecida
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

		// Adiciona um novo identificador
		static private function _id_string(&$query_data, $object) {
			// Se não for [this], considera um modelo
			if($object['object'] !== 'this')
				return '`' . core_model::_get_linear($object['object'])->table() . '`';

			// Senão, anexa this na tabela
			self::_query_push($query_data, array('this'));
		}

		// Adiciona uma nova coluna
		static private function _column_string($object, $result = null) {
			// Se a coluna for definida
			if(!empty($object['column'])) {
				$result.= '.`' . $object['column'] . '`';
			}

			// Se o nome for definido, mas o tipo não, basta um simples alias
			if(!empty($object['name'])
			&& empty($object['type']))
				$result.= ' AS `' . $object['name'] . '`';
			else
			if(!empty($object['column'])
			&& !empty($object['type'])) {
				// Sem outra situação, é necessário criar um objeto json
				$object_json = array(
					'name' => empty($object['name']) ? $object['column'] : $object['name'],
					'type' => $object['type']
				);

				// Armazena a informação
				//DEBUG: se o json passar de 256 caracteres, é um erro (limitação do mysql)
				$result.= ' AS `' . json_encode($object_json) . '`';
			}

			return $result;
		}
	}
