<?php

	// Masterpage
	class site_master_controller extends core_controller {
		public function index() {
			$this->hello_world();
		}

		private function hello_world() {
			load( 'hello_world' );
		}
	}
