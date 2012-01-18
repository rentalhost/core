<?php

	// Controlador master do core
	class core_master_controller extends core_controller {
		// Verifica pelas permissões necessárias
		private function _get_permissions() {
			if(CORE_DEBUG === false) {
				$err = new core_error('1000');
				$err->render();
				exit;
			}
		}

		// Exibe a página inicial
		public function index() {
			$this->_get_permissions();
			load('/core/page', array(
				'lang' => lang('/core/page')
			));
		}

		// Exibe um erro padrão
		public function error() {
			if(!isset($_SESSION))
				session_start();

			$lang = lang('/core/error');
			$error_code = isset($_SESSION['last-error-id'])
				? $_SESSION['last-error-id']
				: $lang->unknow_error;

			load('/core/error', array(
				'lang' => $lang,
				'error_code' => $error_code
			));
		}
	}
