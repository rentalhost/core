<?php

	// classe core_view
	class unit_core__view_library extends test_class_library {
		public function test_load() {
			$this->test(1, load(''));
			$this->test(2, load('../try/to/hack'), 'Trying to hack views, it\'s impossible!');
			$this->test(3, load('/test/useful/fake'), 'File not exists');
			$this->test(4, load('/test/useful/sub_test'), 'File not exists');

			$this->set_prefix('read');
			$view = load('/test/useful/test', null, true);

			$this->test(1, $view->required() === $view);
			$this->test(2, $view->exists());
			$this->test(3, $view->has_failed());
			$this->test(4, $view->get_status());
			$this->test(5, $view->get_contents());
			$this->test(6, $view->get_return());
			$this->test(7, $view->get_modular_data());

			ob_start();
			$this->test(8, $view->render() === $view);
			$view_contents = ob_get_contents();
			ob_end_clean();

			$this->test(9, $view_contents);
			$this->test(10, $view->has_printed());
		}
	}
