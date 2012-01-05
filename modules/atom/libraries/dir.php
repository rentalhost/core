<?php

    // Gerencia diretórios
    class atom_dir_library extends core_library {
        // Obtém os arquivos de um diretório
        static public function get_files( $dir, $deep_search = true, $regex_filter = null ) {
            // Se o diretório não existir
            if( is_dir( $dir ) === false ) {
                return false;
            }

            // Armazena os diretórios que serão analisados
            $dir_list = array( $dir );

            // Armazena os arquivos encontrados aqui
            $file_list = array();

            // Começa a busca
            while( true ) {
                // Se não houver mais diretórios para buscar, finaliza
                if( empty( $dir_list ) ) {
                    break;
                }

                // Obtém um diretório da stack
                $dir = array_shift( $dir_list );
                $dir_handler = opendir( $dir );

                // Analisa cada resultado
                while( $dir_content = readdir( $dir_handler ) ) {
                    // . ou .. são ignorados
                    if( $dir_content === '.'
                    ||  $dir_content === '..' ) {
                        continue;
                    }

                    // Se for necessário, filtra o resultado
                    if( $regex_filter !== null
                    &&  preg_match($regex_filter, $dir_content) === 0 ) {
                        continue;
                    }

                    // Prepara o full path
                    $dir_content = "{$dir}/{$dir_content}";

                    // Diretórios podem ser deep searched
                    if(is_dir( $dir_content ) ) {
                        if( $deep_search === true ) {
                            $dir_list[] = $dir_content;
                        }

                        continue;
                    }

                    // Adiciona um arquivo
                    $file_list[] = $dir_content;
                }
            }

            // Retorna os arquivos encontrados
            sort($file_list);
            return $file_list;
        }
    }
