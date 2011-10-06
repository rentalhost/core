<?php

    // Prepara uma variável para exportação
    class test_export_library extends core_library {
        // Exporta uma variável para ser lida pelo sistema de testes
        static public function export( $data ) {
            // Tipos floats são tipados
            if( is_float( $data ) ) {
                return array(
                    "type" => 'float',
                    "value" => $data
                );
            }
            else
            // Tipos string são encapsuladas
            if( is_string( $data ) ) {
                return '"' . $data . '"';
            }
            else
            // Tipos numéricos e booleans são impressos normalmente
            if( is_scalar( $data ) ) {
                return $data;
            }
            else
            // Valor nulo
            if( is_null( $data ) ) {
                return array( 'type' => 'null' );
            }
            else
            // Arrays e stdClass devem ser informadas
            if( is_array( $data )
            ||  is_a( $data, 'stdclass' ) ) {
                $result = array();
                $result['type'] = gettype( $data );

                $data = (array) $data;
                foreach( $data as $key => $item )
                    $data[$key] = self::export( $item );

                $result['value'] = $data;
                return $result;
            }
            else
            // Se for uma classe
            if( is_object( $data ) ) {
                $result = array();
                $result['type'] = get_class( $data );

                $data = (array) $data;
                foreach( $data as $key => $item ) {
                    $key = array_slice( explode( "\0", $key ), 1, -1 );
                    $data[$key] = self::export( $item );
                }

                $result['value'] = $data;
                return $result;
            }
            else
            // Resources devem ser tipado
            if( is_resource( $data ) ) {
                return array(
                    "type" => gettype( $data ),
                    "value" => get_resource_type( $data )
                );
            }
        }
    }
