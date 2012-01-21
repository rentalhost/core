<?php

	// classe core
	class unit_core_library extends test_class_library {
		public function test_core() {
			$this->set_prefix('get_state');
			$this->test(1, core::get_state('on'));
			$this->test(2, core::get_state('off'));
			$this->test(3, core::get_state('true'));
			$this->test(4, core::get_state(true));
			$this->test(5, core::get_state(false));
			$this->test(6, core::get_state(''));
		}

		public function test_server() {
			$server = $_SERVER;

			$_SERVER['SERVER_NAME'] = 'domain.com';

			$this->set_prefix('is_domain');
			$this->test(1, is_domain('domain.com'));
			$this->test(2, is_domain('notdomain.com'));

			$_SERVER['HTTPS'] = 'on';
			$this->test(100, is_domain('domain.com'));
			$this->test(101, is_domain('https://domain.com'));
			unset($_SERVER['HTTPS']);

			$_SERVER = $server;
		}

		public function test_session() {
			session('/test')->test = true;
			$this->test(1, session('/test')->test);
			$this->test(2, isset(session()->test));
			$this->test(3, $_SESSION['/test']->test);
		}

		// Teste para o sistema de testes (non-core)
		public function test_export() {
			$this->set_prefix( 'scalars' );
			$this->test( 1, 1 );
			$this->test( 2, 1.23 );
			$this->test( 3, '123' );
			$this->test( 4, true );

			$this->set_prefix( 'objects' );
			$this->test( 1, array( 1, 2, 3 ) );
			$this->test( 2, (object) array( 1, 2, 3 ) );
			$this->test( 3, array( 1.23, (object) array( 1.23, '456', 3 ), true ) );
			$this->test( 4, new test_test_library(), 'Object' );
			$this->test( 5, array( 'size' => 1, 'key' => 2, 3 ), 'Different keys length' );

			$this->set_prefix( 'special' );
			$this->test( 1, null, 'Null' );
			$this->test( 2, opendir('.'), 'Resource' );

			$this->set_prefix( 'case_test' );
			$this->case_test( false, null, 'Unavailable, on moment' );
			$this->case_test( true, 'special_test' );
		}

		// Teste especial, deve ser usado com test_if
		public function special_test() {
			$this->test( 1, 'Fine!' );
		}

		public function test_get_baseurl() {
			$this->test( 1, substr( core::get_baseurl(), -11 ), 'Path are clipped' );
			$this->test( 2, substr( core::get_baseurl( false ), -6 ), 'Path are clipped' );
			$this->test( 3, substr( baseurl(), -6 ), 'Only for coverage' );
		}

		public function test_get_modular_parts() {
			$this->test( 1, core::get_modular_parts( 'test' ) );
			$this->test( 2, core::get_modular_parts( 'untest' ) );
			$this->test( 3, core::get_modular_parts( 'test_useful_folder__underlined' ) );
			$this->test( 4, core::get_modular_parts( 'test_useful_file__underlined' ) );
		}

		public function test_get_joined_class() {
			$modular = core::get_modular_parts( 'test_useful_fake' );
			$this->test( 1, core::get_joined_class( $modular, 'object' ) );
		}

		public function test_get_caller_module_path() {
			$this->test( 1, call( '__useful_test::get_caller_module_path' ) );
		}

		public function test_get_caller() {
			$this->test(1, core::get_path_clipped(core::get_caller_path()));
		}

		public function test_setlist() {
			$this->test(1, setlist(null));
			$this->test(2, setlist(''));
			$this->test(3, setlist('a'));
			$this->test(4, setlist('a, b, c, d'));
			$this->test(5, setlist('a, b, c\, d'));
			$this->test(6, setlist(' a,  b,c  '));
			$this->test(7, setlist(array('key' => 'value')));
			$this->test(8, setlist(array('a, b, c')));
		}

		public function test_exception() {
			$this->exception_test(1, 'exception_fake');
			$this->exception_test(2, 'exception_autoloaded');
			$this->exception_test(3, 'exception_unexistent');
			$this->exception_test(4, 'exception_unknow_key');
		}

		public function exception_fake() {
			new core_fake;
		}

		public function exception_autoloaded() {
			throw new test_useful_test_exception('It works!', 123);
		}

		public function exception_unexistent() {
			new test_unexistent_exception();
		}

		public function exception_unknow_key() {
			core::get_modular_parts(null, array('fake_config' => true));
		}
	}
