<?php

    // Inclui as classes principais
    require_once 'classes/core.php';
    require_once 'classes/core_config.php';
    require_once 'classes/core_controller.php';

    // Inclui alguns arquivos importantes
    require_once 'core_constants.php';
    require_once 'core_configs.php';
    require_once 'core_helpers.php';

    // Ao chamar uma classe, o core ficará responsável por identificar sua localização
    spl_autoload_register( 'core::__autoload' );

    // Executa o controlador na URL
    core_controller::_init_url();
