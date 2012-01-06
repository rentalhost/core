<?php

	// classe core_controller
	class unit_core__controller_library extends test_class_library {
		public function test_controller() {
			$this->test(1, execute('useful', true));
			$this->test(2, execute('/test/useful', true));
			$this->test(3, execute(null, true));

			$this->set_prefix('args');
			$this->test(1, execute('useful/one_arg/123', true)->get_return());
			$this->test(2, execute('useful/one_arg/123/456', true)->get_return(), 'drop info');
			$this->test(3, execute('useful/two_args/123/456', true)->get_return());
			$this->test(4, execute('useful/default_method', true)->get_return());

			$this->set_prefix('methods');
			$execute = execute('useful/the_array', true);
			$this->test(1, $execute->required() === $execute);
			$this->test(2, $execute->exists());
			$this->test(3, $execute->has_failed());
			$this->test(15, $execute->has_printed());
			$this->test(4, $execute->get_status());
			$this->test(5, $execute->get_contents());
			$this->test(6, $execute->get_return());
			$this->test(7, $execute->get_return_type());
			$this->test(8, $execute->set_return_type(core_controller::RETURN_TYPE_JSON) === $execute);
			$this->test(9, $execute->get_return_type());

			ob_start();
			$this->test(10, $execute->render() === $execute);
			$this->test(11, ob_get_contents());
			$this->test(16, $execute->has_printed());
			ob_end_clean();

			ob_start();
			$this->test(12, $execute->set_return_type(core_controller::RETURN_TYPE_DEFAULT) === $execute);
			$this->test(13, $execute->render() === $execute);
			$this->test(14, ob_get_contents());
			ob_end_clean();
		}
	}
