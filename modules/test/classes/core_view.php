<?php

	// classe core_view
	class unit_core__view_library extends test_class_library {
		public function test_load() {
			$this->test(1, load('', null, true));
			$this->test(2, load('../try/to/hack', null, true), 'Trying to hack views, it\'s impossible!');
			$this->test(3, load('/test/useful/fake', null, true), 'File not exists');
			$this->test(4, load('/test/useful/sub_test', null, true), 'File not exists');

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

			$this->set_prefix('read_based');
			$view = load('useful/test', null, true);

			$this->test(1, $view->exists());
			$this->test(2, $view->has_failed());
			$this->test(3, $view->get_contents());
			$this->test(4, $view->get_return());

			$this->set_prefix('secure_test');
			$insegure_flag = core_view::STATUS_VIEW_IS_INSECURE;
			$this->test(2, (load('abc', null, true)->get_status() & $insegure_flag) !== $insegure_flag);
			$this->test(3, (load('abc/def/ghi', null, true)->get_status() & $insegure_flag) !== $insegure_flag);
			$this->test(4, (load('abc/[def]/ghi', null, true)->get_status() & $insegure_flag) !== $insegure_flag);
			$this->test(5, (load('abc/[def/ghi]', null, true)->get_status() & $insegure_flag) !== $insegure_flag);
			$this->test(6, (load('[abc]', null, true)->get_status() & $insegure_flag) !== $insegure_flag);
			$this->test(7, (load('[]', null, true)->get_status() & $insegure_flag) !== $insegure_flag);
			$this->test(8, (load('[/]', null, true)->get_status() & $insegure_flag) !== $insegure_flag);

			$this->set_prefix('exception');
			$this->exception_test(1, 'exception_not_found');
			$this->exception_test(2, 'exception_remains');
			$this->exception_test(3, 'exception_insecure');
			$this->exception_test(4, 'exception_empty');
		}

		public function exception_not_found() {
			load('useful/sub_test', null, true)->required();
		}

		public function exception_remains() {
			load('useful/fail', null, true)->required();
		}

		public function exception_insecure() {
			load('useful/[sub_test/test]', null, true)->required();
		}

		public function exception_empty() {
			load('', null, true)->required();
		}
	}
