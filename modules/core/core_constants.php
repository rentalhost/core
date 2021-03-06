<?php

	// Define os dados do core
	define('CORE_TITLE', 'core');
	define('CORE_VERSION', '0.1.0a');

	// Define algumas constantes de caminhos
	define('CORE_INDEX', realpath( dirname(__FILE__) . '/../..' ));
	define('CORE_MODULES', realpath( dirname(__FILE__) . '/..' ));
	define('CORE_ROOT', dirname(__FILE__));

	// Define o tipo de informação aceito em um nome de arquivo ou pasta
	// Organizado por ordem de prioridade; apenas caracteres singulares para flexibilidade de sub-definições
	define('CORE_VALID_PATH_ID', '[a-z_\-0-9A-Z\x7f-\xff]');
	// Define o ID de variáveis (usado em nomes de classes ou métodos, por exemplo)
	define('CORE_VALID_ID', '[a-zA-Z\x7f-\xff]' . CORE_VALID_PATH_ID . '*');
	// Define um path válido para views e outras ocasiões
	// http://stackoverflow.com/a/8748376/755393
	define('CORE_VALID_PATH', '~^(?:(?:' . CORE_VALID_PATH_ID . '*|\[' . CORE_VALID_PATH_ID . '+\])(?=/|$)/?)+$~');

	// Define um word para hostnames
	// http://stackoverflow.com/a/106223/755393 @smink
	define('CORE_HOSTNAME_WORD', '([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])');
	// Define um hostname completo
	define('CORE_HOSTNAME_VALID', '~^('.CORE_HOSTNAME_WORD.'\.)*'.CORE_HOSTNAME_WORD.'$~');

	// Define o separador de chamada (usado para separar classe de método, como a::b)
	define('CORE_VALID_CALLER', '~^(?:(?<object>(?:__)?' . CORE_VALID_ID . ')::)?(?<method>(?:__)?' . CORE_VALID_ID . ')$~');
