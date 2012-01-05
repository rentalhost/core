<?php

	// Define a versão do core
	define( 'CORE_VERSION', '0.1.0a' );

	// Define algumas constantes de caminhos
	define( 'CORE_INDEX', realpath( dirname( __FILE__ ) . '/../..' ) );
	define( 'CORE_MODULES', realpath( dirname( __FILE__ ) . '/..' ) );
	define( 'CORE_ROOT', dirname( __FILE__ ) );

	// Define o tipo de informação aceito em um nome de arquivo ou pasta
	// Organizado por ordem de prioridade; apenas caracteres singulares para flexibilidade de sub-definições
	define( 'CORE_VALID_PATH_ID', '[a-z_0-9A-Z\x7f-\xff]*' );
	// Define o ID de variáveis (usado em nomes de classes ou métodos, por exemplo)
	define( 'CORE_VALID_ID', '[a-z_A-Z\x7f-\xff][a-z_0-9A-Z\x7f-\xff]*' );

	// Define um path válido para views e outras ocasiões
	define( 'CORE_VALID_PATH', '~^(' . CORE_VALID_PATH_ID . '/?)+$~' );

	// Define o separador de chamada (usado para separar classe de método, como a::b)
	define( 'CORE_VALID_CALLER', '~^(?:(?<object>' . CORE_VALID_ID . ')::)?(?<method>' . CORE_VALID_ID . ')$~' );
