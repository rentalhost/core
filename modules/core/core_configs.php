<?php

    // Define as configurações padrões e salva
    $configs = array(
        // Definições de rota
        'route.default.modular'     => 'site',      // string, array
        'route.default.controller'  => 'master',    // string, array
        'route.default.method'      => 'index'      // string
    );

    // Salva as configurações na raiz
    core_config::save_configs( '', $configs );

    //TODO: carregar as configurações modular (modules/configs.php)
