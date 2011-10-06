<?php

    // Controller geralmente usado para class
    class test_classes_controller extends core_controller {
        // Obtém as units de uma class
        public function get_units() {
            $this->set_return_type( 'json' );

            // Retorna os units encontrados
            return call( '__class::get_units', $_POST['from_class'] );
        }

        // Executa uma unit
        public function run_unit() {
            $this->set_return_type( 'json' );

            // Retorna os units encontrados
            return call( '__class::run_unit', $_POST['from_class'], $_POST['unit_method'] );
        }

        // Aceita um resultado
        public function accept_result() {
            $this->set_return_type( 'json' );

            // Retorna os units encontrados
            return call( '__class::accept_result', $_POST['full_id'] );
        }

        // Rejeita um resultado
        public function reject_result() {
            $this->set_return_type( 'json' );

            // Retorna os units encontrados
            return call( '__class::reject_result', $_POST['full_id'] );
        }
    }
