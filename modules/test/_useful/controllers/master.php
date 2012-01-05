<?php

	// controlador mestre para testes
	class test_useful_master_controller extends core_controller {
		public function index() {
			echo 'Okay, it works.';
			return 'And it too!';
		}

		public function one_arg($id) {
			return intval($id);
		}

		public function two_args($a, $b) {
			return $a + $b;
		}

		public function the_array() {
			echo 'Default Content';
			return array('Hello World!');
		}
	}
