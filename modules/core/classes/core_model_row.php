<?php

	// Classe que gerencia linhas de um modelo
	class core_model_row {
		// Armazena a instância do modelo
		private $_model_instance;

		// Constrói um row
		public function __construct($model_instance, $load_id) {
			$this->_model_instance = $model_instance;
		}

		// Obtém o modelo
		public function model() {
			return $this->_model_instance;
		}
	}
