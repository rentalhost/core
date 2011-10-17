<div class="unit-class <?php echo $type; ?>-type">
    <span class="strong">classe</span>
    <span class="name"><?php echo htmlspecialchars($classname); ?></span>
    <span>::</span>
    <span class="result"><?php echo $type; ?></span>
    <span class="message"></span>
</div>

<div class="unit-tests <?php echo $type; ?>-type">
    <?php

        // Imprime informações sobre cada método da classe
        foreach( $methods as $value ) {
            load('models/unit', $value);
        }

    ?>
</div>

<div class="unit-footer <?php echo $type; ?>-type"></div>