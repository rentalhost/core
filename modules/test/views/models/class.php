<div class="unit-class waiting-type">
    <span class="strong">classe</span>
    <span class="name"><?php echo htmlspecialchars($classname); ?></span>
    <span>::</span>
    <span class="result">aguardando resultados...</span>
    <span class="message"></span>
</div>

<div class="unit-tests waiting-type">
    <?php

        // Imprime informações sobre cada método da classe
        foreach( $methods as $value ) {
            load('models/unit', $value);
        }

    ?>
</div>

<div class="unit-footer waiting-type"></div>