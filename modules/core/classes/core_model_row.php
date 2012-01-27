<?php

	// Classe que gerencia linhas de um modelo
	class core_model_row {
		// Conexão usada
		private $_conn;
		// Armazena a instância do modelo
		private $_model_instance;

		// Constrói um row
		public function __construct($conn, $model_instance, $load_id) {
			$this->_conn = $conn;
			$this->_model_instance = $model_instance;
		}

		// Obtém o modelo
		public function model() {
			return $this->_model_instance;
		}

		// Executa uma query no modelo
		public function query($query, $args = null) {
			return $this->_model_instance->query($this->_conn, $query, $args);
		}
	}
