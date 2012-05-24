<?php

	// Esta classe itera os resultados de um modelo
	class core_model_results implements iterator, countable {
		// Armazena a conexão
		private $_conn;
		// Armazena a query executada
		private $_query;

		// Armazena a instância do modelo
		private $_model;
		// Armazena a origem da chamada
		private $_from;

		// Armazena o último resultado
		private $_last_row;

		// Contrói um iterador
		public function __construct($conn, $query, $model_instance, $from_instance){
			$this->_conn = $conn;
			$this->_query = $query;
			$this->_model = $model_instance;
			$this->_from = $from_instance;
		}

		// Obtém o modelo
		public function model() {
			return $this->_model;
		}

		// Obtém a origem da chamada
		public function from() {
			return $this->_from;
		}

		// Obtém todos os resultados
		public function fetch_all() {
			$results = array();
			foreach($this as $key => $value)
				$results[$key] = $value;

			return $results;
		}

		/** ITERATOR */
		// Retorna o objeto reconstruído atual
		public function current(){
			return $this->_last_row;
		}

		// Retorna a chave do objeto atual (o ID)
		public function key(){
			return $this->_last_row->id();
		}

		// Avança ao próximo objeto da lista
		public function next(){
			$next_row = $this->_query->fetch_assoc();
			if($next_row === null)
				return $this->_last_row = null;

			$this->_last_row = new core_model_row($this->_conn, $this->_model, null);
			$this->_last_row->_apply_data($next_row);
		}

		// Inicia a posição de um objeto
		public function rewind(){
			$this->next();
		}

		// Verifica se o objeto é válido
		public function valid(){
			return $this->_last_row !== null;
		}

		/** COUNTABLE */
		// Conta quantos objetos foram encontrados
		public function count(){
			return $this->_query->num_rows;
		}

		// Conta a quantidade total (sem limitação) de objetos encontrados
		public function count_unlimited() {
			return (int) array_pop($this->_conn->query('SELECT FOUND_ROWS();')->fetch_row());
		}
	}