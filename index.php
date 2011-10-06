<?php

	// O modo padrão de trabalho será 'development' se definido true, ou 'production' em outro caso
	define( 'CORE_DEBUG', TRUE );

	// Inicializa o Core
	require_once dirname( __FILE__ ) . '/modules/core/core_engine.php';
