<?php

	// Testes para core_database
	class unit_core__database_library extends test_class_library {
		public function test_database() {
			$conn = connection();
			$this->test(1, $conn);
			$conn->connect();
			$this->test(2, $conn);
			$conn->disconnect();
			$this->test(3, $conn);
			$this->test(4, $conn->query('SELECT 1'));
			$this->test(5, $conn);
			$this->test(6, $conn->query('SELECT 1 AS `test`')->fetch_object());

			$this->set_prefix('fake');
			$conn = connection('fake');
			$this->test(1, $conn->get_connection_string());
			$this->test(2, $conn->get_connection_string());

			$this->test(3, $conn->get_property('driver'));
			$this->test(4, $conn->get_property('username'));
			$this->test(5, $conn->get_property('password'));
			$this->test(6, $conn->get_property('port'));
			$this->test(7, $conn->get_property('database'));
			$this->test(8, $conn->get_property('persistent'));
			$this->test(9, $conn->get_property('connect'));
			$this->test(10, $conn->get_property('charset'));

			$this->test(11, $conn->set_property('driver', 'pdo'), 'currently only for test');
			$this->test(12, $conn->set_property('username', 'newuser'));
			$this->test(13, $conn->set_property('password', 'oldpass'));
			$this->test(14, $conn->set_property('port', 2012));
			$this->test(15, $conn->set_property('database', 'samedatabase'));
			$this->test(16, $conn->set_property('persistent', 'on'));
			$this->test(17, $conn->set_property('connect', 'yes'));
			$this->test(18, $conn->set_property('charset', 'utf8'));
			$this->test(19, $conn->get_connection_string());

			$this->set_prefix('fake2');
			$conn = connection('fake2');
			$this->test(1, $conn->get_connection_string());

			$this->set_prefix('fake3');
			$conn = connection('fake3');
			$this->test(1, $conn->get_connection_string());
		}
	}
