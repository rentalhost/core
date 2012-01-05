<?php

    // classe core
    class unit_test__dir_library extends test_class_library {
        public function test_get_files() {
            library('test_dir');
            $this->test( 1, test_dir_library::get_files("\0"), 'Invalid file' );
            $this->test( 2, test_dir_library::get_files('./modules/site', true), 'Deep search' );
        }
    }
