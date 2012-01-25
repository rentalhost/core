<?php

	// Define algumas constantes externas
	define('CORE_EX_QUERY_OBJECT', '/^(?<object>'.CORE_VALID_ID.')(?:\.(?<column>'.CORE_VALID_ID.'))?$/');

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
				if(preg_match(CORE_EX_QUERY_OBJECT, $item['content'][0], $item2)) {
					// Se não houver uma definição de coluna, é um objeto
					if(empty($item2['column'])) {
						// Se for uma definição de this, aplica um array especial
						if($item2['object'] === 'this')
						self::_query_push($query_data, array('type' => 'this.table'));
						// Senão, é necessário obter o nome da tabela do modelo informado
						else
						self::_query_push($query_data, '`' . core_model::_get_linear($item2['object'])->table() . '`');
					}
					// Se não, aplica o nome da coluna
					else {
						// Se for uma definição de this, aplica um array especial
						if($item2['object'] === 'this')
						self::_query_push($query_data, array(
							'type' => 'this.column',
							'column' => $item2['column']));
						// Senão, é necessário obter o nome da tabela do modelo informado
						else
						self::_query_push($query_data, '`' . core_model::_get_linear($item2['object'])->table()
							. '`.`' . $item2['column'] . '`');
					}

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
