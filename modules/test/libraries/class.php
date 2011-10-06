<?php

    // Controla as informações das classes
    class test_class_library extends core_library {
        /** ESTÁTICO */
        // Obtém todas informações das classes existentes
        static public function get_all() {
            // Inicia a library de diretórios, pois ela será usada em breve
            library( 'atom_dir' );

            // Agora é necessário armazenar as classes já iniciadas
            $loaded_classes = get_declared_classes();

            // Agora precisamos carregar todos os arquivos de classes que temos no nosso módulo de testes
            foreach( call( 'atom_dir::get_files', core::get_current_path() . '/classes' ) as $file ) {
                require_once $file;
            }

            // Agora precisamos saber quais novas classes foram definidas
            $loaded_classes = array_diff( get_declared_classes(), $loaded_classes );

            // Para cada classe carregada, obtém os dados de checagem
            foreach( $loaded_classes as &$item ) {
                $item = self::get_data( new $item );
            }

            // Retorna a informação recebida
            return $loaded_classes;
        }

        // Obtém as informações sobre uma classe
        static public function get_data( $class ) {
            // Primeiro é feito uma análise na classe...
            $class->do_checkup();

            // Em seguida, suas informações são colhidas
            $result = array();
            $result['name'] = substr( get_class( $class ), 5, -8 );
            $result['type'] = $class->_class_type;
            $result['message'] = $class->get_message();

            // Remove as informações vazias e retorna
            return array_filter( $result, 'core::_not_empty' );
        }

        // Inicia uma classe pelo nome simplificado
        static public function require_class( $class ) {
            // Para isso, é preciso filtrar a variável recebida
            if( preg_match( '/' . CORE_VALID_ID . '/', $class ) === 0 ){
                return false;
            }

            // Prepara e inclui o arquivo
            $classfile = core::get_current_path() . "/classes/{$class}.php";
            if( !is_file( $classfile ) ) {
                return false;
            }

            // Por fim, inclui o arquivo
            require_once $classfile;
            return true;
        }

        // Carrega uma classe
        static private function _load_class( $class ){
            // Inicia a biblioteca responsável pelas classes
            library( '__class' );

            // Tenta incluir a classe pedida, se não, termina por aqui
            if( call( '__class::require_class', $class ) === false ) {
                return false;
            }
        }

        // Obtém os métodos de units de uma class
        static public function get_units( $class ) {
            // Tenta incluir a classe pedida, se não, termina por aqui
            if( self::_load_class( $class ) === false ) {
                return false;
            }

            // Faz uma reflexão para obter os métodos da classe
            $methods = new ReflectionClass( "unit_{$class}_library" );
            $methods = $methods->getMethods( ReflectionMethod::IS_PUBLIC );

            foreach( $methods as $key => $method ) {
                // O método para ser um unit, deve começar por "test_"
                if( substr( $method->getName(), 0, 5 ) !== 'test_' ) {
                    unset( $methods[$key] );
                    continue;
                }

                // Se começar, então carrega as suas informações
                $methods[$key] = array(
                    'name' => substr( $method->getName(), 5 )
                );
            }

            sort($methods);
            return $methods;
        }

        // Executa uma unit
        static public function run_unit( $class, $method ) {
            // Tenta incluir a classe pedida, se não, termina por aqui
            if( self::_load_class( $class ) === false ) {
                return false;
            }

            // Inicializa a classe
            $class = "unit_{$class}_library";
            $classinst = new $class();

            // Verifica se o método é válido ou se existe
            $method = "test_{$method}";
            if( method_exists( $classinst, $method ) === false ) {
                return false;
            }

            // Agora criamos uma instância da classe, ela será usada para carregar o método
            $classinst->_class_method = substr( $method, 5 );
            call_user_func( array( $classinst, $method ) );

            // Depois, é necessário descobrir se algum método foi removido
            $old_results = call( 'atom_dir::get_files', core::get_current_path() . '/results', false,
                "/^{$class}\.{$method}\..*\.valid$/" );

            // Após executar o método, colhe os resultados
            return $classinst->_class_results;
        }

        // Aceita um resultado
        static public function accept_result( $full_id ) {
            // Para isso, é preciso filtrar a variável recebida
            if( preg_match( '~^(' . CORE_VALID_PATH_ID . '\.?)+$~', $full_id ) === 0 ){
                return false;
            }

            // É necessário apenas checar se o arquivo-resultado existe
            $path = core::get_current_path() . "/results/{$full_id}.last";

            // Se o resultado não existir...
            if( !is_file( $path ) ) {
                return false;
            }

            // Em outro caso, aceita o resultado
            $path_real = core::get_current_path() . "/results/{$full_id}.valid";
           @unlink($path_real);
            rename($path, $path_real);

            return true;
        }

        // Aceita um resultado
        static public function reject_result( $full_id ) {
            // Para isso, é preciso filtrar a variável recebida
            if( preg_match( '~^(' . CORE_VALID_PATH_ID . '\.?)+$~', $full_id ) === 0 ){
                return false;
            }

            // É necessário apenas checar se o arquivo-resultado existe
            $path = core::get_current_path() . "/results/{$full_id}.valid";

            // Se o resultado não existir...
            if( !is_file( $path ) ) {
                return false;
            }

            // Em outro caso, apaga o resultado
            unlink($path);
            return true;
        }

        /** OBJETO */
        // Armazena o tipo inicial da classe
        protected $_class_type = 'waiting';
        // Armazena o método executado
        protected $_class_method;
        // Armazena o prefixo que está sendo usado
        protected $_class_prefix = 'default';
        // Armazena os resultados da classe
        protected $_class_results = array();

        // Faz uma análise no objeto, e opcionalmente retorna uma mensagem
        // Este método é útil para mostrar que dada classe não é suportada
        private function do_checkup() {
        }

        // Obtém uma mensagem da classe
        private function get_message() {
        }

        // Altera o prefixo
        protected function set_prefix( $prefix ) {
            $this->_class_prefix = $prefix;
        }

        // Executa um teste na classe
        protected function test( $index, $result ) {
            // Define o prefixo, se ele não existir
            if( isset($this->_class_results[$this->_class_prefix]) === false ){
                $this->_class_results[$this->_class_prefix] = array();
            }

            // Se a definição do teste já existir, retorna um erro
            if( isset($this->_class_results[$this->_class_prefix][$index]) ) {
                $this->_class_results[$this->_class_prefix][$index] = array(
                    'type' => 'exception',
                    'message' => 'O index #' . (int) $index . ' já está sendo usado por outra operação.'
                );
                return;
            }

            // Exporta o resultado em formato compatível
            $last_generated_result = call( '__export::export', $result );

            // Define o caminho de controle
            $control_file = core::get_current_path() . '/results/' . substr( get_class( $this ), 5, -8 )
                          . ".{$this->_class_method}.{$this->_class_prefix}.{$index}";

            // Se o arquivo de controle não existir, define como um novo resultado
            $valid_result = null;
            if( !is_file( $control_file . '.valid' ) ) {
                $type = 'new';
            }
            // Se o resultado já existe, compara o conteúdo
            else {
                $old_result = json_decode( file_get_contents( $control_file . '.valid' ), true );

                // Se o novo resultado for diferente do antigo...
                if( $old_result['result'] !== $last_generated_result ) {
                    $type = 'failed';
                    $valid_result = $old_result['result'];
                }
                // Se não, é um sucesso!
                else {
                    $type = 'success';
                }
            }

            // Prepara o conteúdo do arquivo e salva como último resultado
            $last_result = array(
                'result' => $last_generated_result
            );

            // Salva o resultado recebido
            file_put_contents( $control_file . '.last', json_encode( $last_result ) );

            // Em outro caso, preenche os dados do teste
            $this->_class_results[$this->_class_prefix][$index] = array(
                'type' => $type,
                'result' => $last_generated_result
            );

            // Se um valid result está disponível, armazena
            if( $valid_result ) {
                $this->_class_results[$this->_class_prefix][$index]['valid_result'] = $valid_result;
            }
        }
    }
