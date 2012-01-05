<?php

	// classe core
	class unit_core__caller_library extends test_class_library {
		public function test_call() {
			$this->test( 1, call( 'invalid caller' ) );
			$this->test( 2, call( '__valid_caller' ), 'Will be removed futurely' );

			$this->set_prefix( 'caller' );
			$this->test( 1, call(), 'Instance create' );
			$this->test( 2, call(), 'Instance retrieval' );
		}
	}
