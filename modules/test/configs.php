<?php

	set_time_limit(15);

	// Define uma informação para testes, apenas
	$config->test = true;

	// Cria uma conexão padrão
	if(is_localhost())
		create_connection('mysqli://root@127.0.0.1/core_project');

	// Cria uma conexão falsa, para testes
	create_connection('mysqli://username:password@servername:1234/dbname' .
		'?persistent=false&charset=latin1&connect=false', 'fake');
	create_connection('mysqli://fakeserver', 'fake2');
	create_connection('fakeserver', 'fake3');

	// Inclui alguns arquivos que não vão para o commit
	if(CORE_DEBUG === true
	&& is_file(dirname(__FILE__) . '/configs.extra.php'))
		require dirname(__FILE__) . '/configs.extra.php';
