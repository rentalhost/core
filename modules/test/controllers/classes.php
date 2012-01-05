<?php

	// Controller geralmente usado para class
	class test_classes_controller extends core_controller {
		// Aceita um resultado
		public function accept_result() {
			$this->set_return_type( 'json' );

			// Retorna os units encontrados
			return call( '__class::accept_result', $_POST['id'] );
		}

		// Rejeita um resultado
		public function reject_result() {
			$this->set_return_type( 'json' );

			// Retorna os units encontrados
			return call( '__class::reject_result', $_POST['id'] );
		}
	}
