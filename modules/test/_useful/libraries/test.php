<?php

	class test_useful_test_library extends core_library {
		static public function get_caller_module_path() {
			return core::get_caller_module_path();
		}
	}
