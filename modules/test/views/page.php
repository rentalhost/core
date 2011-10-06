<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
    <head>
        <title>core laboratório :: <?php echo CORE_VERSION; ?></title>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <base href="<?php echo get_baseurl(); ?>" />
        <link href="publics/default.css" rel="stylesheet" type="text/css" />
        <script src="publics/jquery-1.6.js"></script>
        <script src="publics/jquery.css-1.45.js"></script>
        <script src="publics/class.js"></script>
        <script src="publics/class.PageObject.js"></script>
        <script src="publics/class.ModelObject.js"></script>
        <script src="publics/class.ClassObject.js"></script>
        <script src="publics/class.ItemObject.js"></script>
        <script src="publics/default.js"></script>
    </head>
    <body>
        <!-- Cabeçalho -->
        <div id="header">
            <div class="content">
                <img src="publics/images/labs-icon.png" title="Icone por Oliver Scholtz" width="50" height="50" />
                <span class="labs-title">core laboratório</span>
                <span class="labs-dcolon">::</span>
                <span class="labs-status">tomando um ar...</span>
                <span class="labs-platform">rodando o <strong>core <?php echo CORE_VERSION; ?></strong> no <strong>PHP <?php echo PHP_VERSION; ?></strong></span>
            </div>
        </div>

        <!-- Conteúdo -->
        <div id="content">
            <div class="content">
                <!-- Barra de Ferramentas -->
                <ul id="toolbar">
                    <li class="button button-all run-button">Rodar</li>
                </ul>

                <!-- Classes -->
                <div id="classes-realm">

                </div>

                <script>
                    $(function(){
                        <?php

                            // Carrega as informações sobre as classes e gera suas chamadas
                            $classes_data = call( '__class::get_all' );

                            // Imprime no script responsável pela criação dos elementos
                            echo 'ClassObject.sParse(' . json_encode($classes_data) . ');';

                        ?>
                    });
                </script>

                <br />
            </div>
        </div>

        <!-- Modelo de Classe -->
        <div class="class-model model-type">
            <!-- Classe -->
            <div class="unit-class">
                <span class="strong">classe</span>
                <span class="name"></span>
                <span>::</span>
                <span class="result"></span>
                <span class="message"></span>

                <ul class="actions">
                    <li class="button cancel-button">Cancelar</li>
                    <li class="button run-button">Rodar</li>
                </ul>
            </div>
            <div class="unit-tests hidden">
            </div>
            <div class="unit-footer"></div>
        </div>

        <!-- Modelo de Item -->
        <div class="item-model model-type">
            <!-- Unidade -->
            <div class="unit-test">
                <span class="strong">unidade</span>
                <span class="name"></span>
                <span class="index"></span>
                <span>::</span>
                <span class="result"></span>
                <span class="message"></span>

                <ul class="actions">
                    <li class="button cancel-button">Cancelar</li>
                    <li class="button accept-button">Aceitar</li>
                    <li class="button reject-button">Rejeitar</li>
                    <li class="button run-button">Rodar</li>
                </ul>
            </div>
        </div>

        <!-- Resultado -->
        <div class="result-model model-type">
            <div class="unit-result hidden">
            </div>
        </div>

        <!-- Objeto Log -->
        <div class="log-model model-type">
            <div class="code-title"></div>
            <div class="code-header">
                <span class="code-type"></span>
                <span class="code-value"></span>
            </div>
        </div>
    </body>
</html>