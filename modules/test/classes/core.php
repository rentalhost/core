<?php

    // classe core
    class unit_core_library extends test_class_library {
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
            $this->test( 4, new test_test_library(), 'CLASS' );
            $this->test( 5, array( 'size' => 1, 'key' => 2, 3 ), 'Different keys length' );

            $this->set_prefix( 'special' );
            $this->test( 1, null, 'NULL' );
            $this->test( 2, opendir('.'), 'RESOURCE' );
        }

        // Testes para o método core::get_baseurl / get_baseurl();
        public function test_get_baseurl() {
            $this->test( 1, substr( core::get_baseurl(), -11 ), 'Path are clipped' );
            $this->test( 2, substr( core::get_baseurl( false ), -6 ), 'Path are clipped' );
        }
    }
