<?php

	// classe core_exception
	class unit_core__exception_library extends test_class_library {
		public function test_exception() {
			try { new test_force_exception(); }
			catch(core_exception $exception) {}

			$this->test(1, $exception);
			$this->test(2, $exception->get_message());
			$this->test(3, $exception->get_code());
			$this->test(4, core::get_path_fixed($exception->get_file()));
			$this->test(5, $exception->get_line() > 0);
		}

		public function test_error() {
			$error = new core_error(0);
			$this->test(1, $error);
			$this->test(2, $error->set_individual_log());
			$this->test(3, $error->set_fatal());
		}
	}
