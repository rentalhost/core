<?php

	// classe core
	class unit_core__caller_library extends test_class_library {
		public function test_call() {
			$this->set_prefix( 'caller' );
			$this->test( 1, call(), 'Instance create' );
			$this->test( 2, call(), 'Instance retrieval' );

			$this->set_prefix('exception');
			$this->exception_test(1, 'exception_invalid_caller');
		}

		public function exception_invalid_caller() {
			call( 'invalid caller' );
		}
	}
