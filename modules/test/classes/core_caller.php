<?php

	// classe core
	class unit_core__caller_library extends test_class_library {
		public function test_call() {
			$this->set_prefix( 'caller' );
			$caller = call();
			$this->test( 1, $caller, 'Instance create' );
			$this->test( 2, call() === $caller, 'Instance retrieval' );

			$this->set_prefix('helpers');
			$this->test(1, function_exists('test_useful_test'));
			helper('__useful_test');
			$this->test(2, function_exists('test_useful_test'));
			$this->test(3, test_useful_test());
			$this->test(4, test_useful_test_again());
			$this->test(5, function_exists('test_useful_sub_test_advanced'));
			helper('__useful_sub_test');
			$this->test(6, test_useful_sub_test_advanced());

			$this->set_prefix('do_call');
			$this->test(1, call('__useful_test::get_caller_module_path'));
			$this->test(2, call('__useful_test'));
			$this->test(3, call('__useful_test_again'));
			$this->test(4, call('__useful_sub_test_advanced'));

			$this->set_prefix('direct');
			$this->test(1, call()->__useful_test());
			$this->test(2, call()->__useful_sub_test_advanced());
			$this->test(3, call()->__useful_test->get_caller_module_path());

			$this->set_prefix('exception');
			$this->exception_test(1, 'exception_invalid_caller');
		}

		public function exception_invalid_caller() {
			call( 'invalid caller' );
		}
	}
