<?php

	// Define uma informação para testes, apenas
	$config->test = true;

	// Cria uma conexão padrão
	if(is_localhost())
		create_connection('mysqli://root@127.0.0.1/core_project');

	// Inclui alguns arquivos que não vão para o commit
	if(CORE_DEBUG === true
	&& is_file(dirname(__FILE__) . '/configs.extra.php'))
		require dirname(__FILE__) . '/configs.extra.php';
