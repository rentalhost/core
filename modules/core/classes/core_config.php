<?php

    // Classe de configurações
    class core_config {
        // Armazena as configurações
        static private $_configs = array( );

        // Armazena os detalhes de uma configuração
        static public function save_configs( $modular_path, $config_array, $merge_similar = true ) {
            // Quando não existe a informação, faz um save rapidamente
            // Se não for necessário mesclar, faz um save rapidamente
            if( !isset( self::$_configs[$modular_path] )
            ||  $merge_similar === false ) {
                self::$_configs[$modular_path] = (array) $config_array;
                return true;
            }

            // Em último caso, faz um save merge
            self::$_configs[$modular_path] = array_merge( self::$_configs[$modular_path], (array) $config_array );
            return true;
        }

        // Re/define uma configuração
        //TODO: $overwrite_old = true -- sobrescrever valor anterior, se existir
        static public function set_config( $modular_path, $config_key, $new_value ) {
            // Se necessário, cria a configuração do modular
            if( !isset( self::$_configs[$modular_path] ) ) {
                self::$_configs[$modular_path] = array();
            }

            // Define a informação
            self::$_configs[$modular_path][$config_key] = $new_value;
            return true;
        }

        // Obtém uma configuração
        //TODO: $default_value = null -- usa um valor padrão, se a configuração não existir
        static public function get_config( $modular_path, $config_key ) {
            // Se a modular path for null, a busca será prioritária
            if( $modular_path === null ) {
                return self::_prioritary_get_config( $config_key );
            }
            else
            // Se a informação existir, ela será retornada
            if( isset( self::$_configs[$modular_path][$config_key] ) ) {
                return self::$_configs[$modular_path][$config_key];
            }

            // Caso contrário, undefined será retornado
            return null;
        }

        // Faz uma busca prioritária
        static private function _prioritary_get_config( $config_key ) {
            // Obtém o path atual de prioridade
            $priority_path = explode( '/', core::get_path_clipped( core::get_caller_path( ), CORE_INDEX ) );

            // Buscar enquanto puder
            while( true ) {
                // Une a prioridade e conta os elementos
                $priority_unify = join( '/', $priority_path );

                // Se os dados de configuração ainda não existe, carrega
                if( !isset( self::$_configs[$priority_unify] ) ) {
                    core::do_require( CORE_INDEX . "/{$priority_unify}/configs.php" );
                }

                // Verifica se a configuração existe
                if( isset( self::$_configs[$priority_unify][$config_key] ) ) {
                    return self::$_configs[$priority_unify][$config_key];
                }

                // Se a prioridade for diferente de "vazio" (ou seja, a raiz) reduz a prioridade
                if( !empty( $priority_unify ) ) {
                    array_pop( $priority_path );
                    continue;
                }

                break;
            }

            // Se não encontrar nenhuma informação, retorna null
            return null;
        }

        // Retorna se uma configuração foi registrada
        static public function has_config( $modular_path, $config_key ) {
            return key_exists( $config_key, self::$_configs[$modular_path] );
        }
    }
