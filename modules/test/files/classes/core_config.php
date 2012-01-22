<?php

	// classe core_config
	class unit_core__config_library extends test_class_library {
		public function test_config() {
			$this->test(1, core_config::set_config('test$only', 'test', 'only'));
			$this->test(2, core_config::get_config('test$only', 'test'));
			$this->test(3, core_config::get_config('test$only', 'none'));
			$this->test(5, core_config::has_config('test$only', 'test'));
			$this->test(4, core_config::get_config(null, 'test'));
			$this->test(6, core_config::get_config(null, 'none'));
			$this->test(7, core_config::get_config(null, 'none', 'default value'));

			$this->set_prefix('helper');
			$this->test(1, config('test'));
			$this->test(2, config('none'));
			$this->test(3, config('none', 'default value'));
		}
	}
