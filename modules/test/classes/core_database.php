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
		}
	}
