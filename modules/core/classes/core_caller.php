<?php

    // Executa métodos helper e library, além de carregar estas classes, além de model
    class core_caller {
        // Armazena as classes que já foram carregadas para evitar re-processamento
        static private $_loaded_classes = array();

        // Inicia uma chamada estática de um helper ou library
        //DEBUG: informa um erro se a chamada for inválida
        //DEBUG: informa um erro se o método chamado na library não existir
        static public function do_call( $command, $arguments ) {
            // Separa o objeto do método
            if( preg_match( CORE_VALID_CALLER, $command, $command_details ) === 1 ) {
                // Se um objeto for definido, significa que é uma chamada a um método estático de uma library
                if( !empty( $command_details['object'] ) ) {
                    // Carrega a biblioteca, se necessário
                    $command_details['object'] = self::load_library( $command_details['object'] );

                    // Executa a chamada e retorna a informação obtida
                    return call_user_func_array( array( $command_details['object'], $command_details['method'] ), $arguments );
                }
            }

            return false;
        }

        // Carrega uma library e retorna seu nome completo
        static public function load_library( $library ) {
            // Primeiramente é necessário identificar se é uma classe no próprio módulo
            // Para isto, o objeto deve iniciar com duplo underline
            // Exemplo: __exemplo passará a ser site_exemplo_library
            if( substr( $library, 0, 2 ) === '__' ) {
                // Se for, a informação é substituida pelo módulo
                $library = join( '_', core::get_caller_module_path() ) . '_' . substr( $library, 2 );
            }

            // Anexa o sulfixo das bibliotecas
            $library .= '_library';

            // Se a classe já tiver sido carregada, evita o processo
            if( isset( self::$_loaded_classes[$library] ) ) {
                return self::$_loaded_classes[$library];
            }

            // Agora, é necessário carregar tal classe
            spl_autoload_call( $library );

            // Salva em loaded classes e retorna
            self::$_loaded_classes[$library] = $library;
            return $library;
        }
    }
