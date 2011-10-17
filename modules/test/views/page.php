<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>core laboratório :: <?php echo CORE_VERSION; ?></title>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <base href="<?php echo get_baseurl(); ?>" />
        <link href="publics/default.css" rel="stylesheet" type="text/css" />
        <script src="publics/jquery-1.6.js"></script>
        <script src="publics/jquery.css-1.45.js"></script>
        <script src="publics/default.js"></script>
    </head>
    <body>
        <div id="header">
            <div class="content">
                <img src="publics/images/labs-icon.png" title="Icone por Oliver Scholtz" width="50" height="50" />
                <span class="labs-title">core laboratório</span>
                <span class="labs-platform">rodando o <strong>core <?php echo CORE_VERSION; ?></strong> no <strong>PHP <?php echo PHP_VERSION; ?></strong></span>
            </div>
        </div>

        <div id="content">
            <div class="content">
                <ul id="toolbar">
                </ul>

                <div id="classes-realm">
                    <?php

                        // Obtém e imprime as classes diretamente no modelo
                        foreach( call('__class::get_all') as $value ) {
                            load('models/class', $value);
                        }

                    ?>
                </div>
                <br />
            </div>
        </div>
    </body>
</html>