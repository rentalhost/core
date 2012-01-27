<?php

	// Classe que gerencia linhas de um modelo
	class core_model_row {
		// Conexão usada
		private $_conn;
		// Armazena a instância do modelo
		private $_model_instance;

		// Armazena a informação da linha
		private $_data = null;
		// A linha existe?
		private $_exists = false;

		// Constrói um row
		public function __construct($conn, $model_instance, $load_id) {
			$this->_conn = $conn;
			$this->_model_instance = $model_instance;

			// Carrega o ID inicial
			if($load_id !== null)
				$this->load($load_id);

		}

		// Carrega o objeto do modelo por ID
		public function load($id) {
			// Faz a busca e aplica uma informação recebida
			return $this->_apply_data($this->query('SELECT [*] FROM [this] WHERE `id` = [@id(int)];',
				array('id' => $id))->fetch_object());
		}

		// Aplica os dados recebidos
		private function _apply_data($result) {
			// Se não for informado um resultado...
			if($result === false) {
				$this->_exists = false;
			}
			// Senão, aplica as informações
			else {
				$this->_exists = true;
				$this->_data = $result;
			}
		}

		/** EXTRA */
		// Obtém o modelo
		public function model() {
			return $this->_model_instance;
		}

		// Executa uma query no modelo
		public function query($query, $args = null) {
			return $this->_model_instance->query($this->_conn, $query, $args);
		}
	}
