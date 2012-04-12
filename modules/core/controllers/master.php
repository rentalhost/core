<?php

	// Controlador master do core
	class core_master_controller extends core_controller {
		// Verifica pelas permissões necessárias
		private function _get_permissions() {
			if(CORE_DEBUG === false) {
				$err = new core_error('CxFFFF');
				$err->set_fatal()->set_log(false)->run();
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

			$error_object = isset($_SESSION['last-error']) ? $_SESSION['last-error'] : null;

			$lang = lang('/core/error');
			$error_code = $error_object !== null
				? $_SESSION['last-error']->error_code
				: $lang->unknow_error;

			$error_lang = null;
			if($error_object !== null) {
				$error_lang = 'err' . substr($error_code, 2);
				$error_lang = lang('/core/errors/' . $error_lang);
			}

			load('/core/error', array(
				'lang' => $lang,
				'error_code' => $error_code,
				'error' => $error_object,
				'error_lang' => $error_lang
			));
		}
	}
