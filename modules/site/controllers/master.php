<?php

	// Masterpage
	class site_master_controller extends core_controller {
		public function index() {
			$this->hello_world();
		}

		//TODO: usar View!
		private function hello_world() {
			load( 'hello_world' );
		}
	}
