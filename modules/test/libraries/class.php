<?php

    // Controla as informações das classes
    class test_class_library extends core_library {
        // Obtém informações sobre as classes existentes
        static public function get_all() {
            library( 'atom_dir' );

            $results = array();
            $loaded_classes = get_declared_classes();

            foreach( call('atom_dir::get_files', core::get_current_path() . '/classes') as $file )
                require_once $file;

            $loaded_classes = array_diff( get_declared_classes(), $loaded_classes );
            foreach( $loaded_classes as $item ) {
                $results[] = self::_get_class_data($item);
            }

            return $results;
        }

        // Obtém informações sobre uma classe
        static private function _get_class_data( $item ) {
            $obj = new ReflectionClass($item);
            $obj_methods = $obj->getMethods(ReflectionMethod::IS_PUBLIC);

            $data = array();
            foreach( $obj_methods as $key => $value ) {
                if( substr( $value->getName(), 0, 5 ) === 'test_' ) {
                    self::_run_tests($data, new $item, $value->getName());
                }
            }

            return array(
                'classname' => substr($item, 5, -8),
                'methods' => $data
            );
        }

        // Executa os métodos de um unit
        static private function _run_tests( &$results, $class, $method ) {
            $class->_id_class = substr( get_class( $class ), 5, -8 );
            $class->_id_method = substr( $method, 5 );

            call_user_func( array( $class, $method ) );

            $results = array_merge( $results, $class->_results );
        }

        // Aceita um resultado
        static public function accept_result( $id ) {
            // Primeiro testa se o ID é válido
            if( preg_match( '/^(?:' . CORE_VALID_ID . '\.?)*$/' ) === 0 )
                return false;

            // Se for, remove o resultado atual, se existir, e move o novo resultado sobre este
            $file = core::get_current_path() . "/results/{$id}";
           @unlink("{$file}.valid");
            rename("{$file}.last", "{$file}.valid");

            return true;
        }

        // Rejeita um resultado
        static public function reject_result( $id ) {
            // Primeiro testa se o ID é válido
            if( preg_match( '/^(?:' . CORE_VALID_ID . '\.?)*$/' ) === 0 )
                return false;

            // A rejeição é apenas a remoção do arquivo .valid do ID referente
           @unlink(core::get_current_path() . "/results/{$id}.valid");

            return true;
        }

        // Identificadores de um unit
        private $_id_class,
                $_id_method,
                $_id_prefix = 'default',
                $_results = array();

        // Altera o prefixo atual
        public function set_prefix( $prefix ) {
            $this->_id_prefix = $prefix;
        }

        // Executa um teste e armazena o seu resultado
        public function test( $index, $result, $comment = null ) {
            $id = "{$this->_id_class}.{$this->_id_method}.{$this->_id_prefix}.{$index}";

            $this->_results["{$this->_id_prefix}-{$index}"] = array(
                'id' => $id,
                'method' => $this->_id_method,
                'prefix' => $this->_id_prefix,
                'index' => $index
            );

            if( isset($this->_results["{$this->_id_prefix}-{$index}"]['type']) ) {
                $this->_results["{$this->_id_prefix}-{$index}"]+= array(
                    'type' => 'exception',
                    'message' => 'O index #' . $index . ' já está sendo utilizado.'
                );
                return;
            }

            $result = call( '__export::prepare_result', $result );
            $result_type = 'new';

            $file = core::get_current_path() . "/results/{$id}";
            file_put_contents("{$file}.last", json_encode($result));

            if( is_file("{$file}.valid") ) {
                $old_result = json_decode( file_get_contents("{$file}.valid"), true );
                $result_type = $old_result === $result ? 'success' : 'failed';
            }

            $this->_results["{$this->_id_prefix}-{$index}"]+= array(
                'type' => $result_type,
                'result' => $result,
                'message' => $comment
            );
        }
    }
