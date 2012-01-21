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

			$this->set_prefix('has_module');
			$this->test(1, core::has_module('useful'));
			$this->test(2, core::has_module('/test/useful'));
			$this->test(3, core::has_module('fake'));
			$this->test(4, core::has_module('useful/fake'));
			$this->test(5, core::has_module('/fake'));

			$this->set_prefix('replace_domain');
			$this->test(1, core::_replace_domain('*.domain.com'), '*.domain.com');
			$this->test(2, core::_replace_domain('*?.domain.com'), '*?.domain.com');
			$this->test(3, core::_replace_domain('www?.domain.com'), 'www?.domain.com');
			$this->test(4, core::_replace_domain('abc.*.domain.com'), 'abc.*.domain.com');
			$this->test(5, core::_replace_domain('*.*.domain.com'), '*.*.domain.com');
			$this->test(6, core::_replace_domain('abc.*?.domain.com'), 'abc.*?.domain.com');
			$this->test(7, core::_replace_domain('www.domain.com.br?'), 'www.domain.com.br?');
			$this->test(8, core::_replace_domain('www.domain.com.*'), 'www.domain.com.*');
			$this->test(9, core::_replace_domain('www.domain.com.*?'), 'www.domain.com.*?');
			$this->test(10, core::_replace_domain('www.dom*ain.com'), 'www.dom*ain.com');
			$this->test(11, core::_replace_domain('*?.*.com.br?'), '*?.*.com.br?');
		}

		public function test_server() {
			$server = $_SERVER;

			$_SERVER['SERVER_NAME'] = 'domain.com';
			$_SERVER['SERVER_PORT'] = '80';
			$_SERVER['REQUEST_URI'] = '/core/test?fake';
			unset($_SERVER['REDIRECT_URL']);

			$this->set_prefix('is_domain');
			$this->test(1, is_domain('domain.com'));
			$this->test(2, is_domain('notdomain.com'));

			$_SERVER['HTTPS'] = 'on';
			$this->test(100, is_domain('domain.com'));
			$this->test(101, is_domain('https://domain.com'));
			unset($_SERVER['HTTPS']);

			$this->test(3, is_domain('domain.com:80'));
			$this->test(4, is_domain('domain.com:443'));
			$this->test(5, is_domain('domain.com/core/test'));
			$this->test(6, is_domain('domain.com/core/test/fake'));

			$_SERVER['SERVER_NAME'] = 'www.domain.com';
			$this->test(7, is_domain('*.domain.com'));
			$this->test(8, is_domain('www?.domain.com'));
			$this->test(9, is_domain('*?.domain.com'));

			$_SERVER['SERVER_NAME'] = 'abc.www.domain.com';
			$this->test(10, is_domain('*.domain.com'));
			$this->test(11, is_domain('www?.domain.com'));
			$this->test(12, is_domain('*?.domain.com'));
			$this->test(19, is_domain('abc.*.domain.com'));
			$this->test(20, is_domain('abc.www?.domain.com'));
			$this->test(21, is_domain('*.*.domain.com'));
			$this->test(22, is_domain('abc?.www?.domain.com'));

			$_SERVER['SERVER_NAME'] = 'sub.domain.com';
			$this->test(13, is_domain('*.domain.com'));
			$this->test(14, is_domain('www?.domain.com'));
			$this->test(15, is_domain('*?.domain.com'));

			$_SERVER['SERVER_NAME'] = 'domain.com';
			$this->test(16, is_domain('*.domain.com'));
			$this->test(17, is_domain('www?.domain.com'));
			$this->test(18, is_domain('*?.domain.com'));
			$this->test(29, is_domain('domain.com.br?'));

			$_SERVER['SERVER_NAME'] = 'domain.com.br';
			$this->test(23, is_domain('domain.com'));
			$this->test(24, is_domain('domain.com.br'));
			$this->test(25, is_domain('domain.com.*'));
			$this->test(26, is_domain('domain.com.*?'));
			$this->test(27, is_domain('domain.com.br?'));
			$this->test(28, is_domain('domain.com.us?'));

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
