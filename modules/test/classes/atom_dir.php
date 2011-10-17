<?php

    // classe core
    class unit_atom__dir_library extends test_class_library {
        public function test_get_files() {
            library('atom_dir');
            $this->test( 1, atom_dir_library::get_files("\0"), 'Invalid file' );
            $this->test( 2, atom_dir_library::get_files('./modules/site', true), 'Deep search' );
        }
    }
