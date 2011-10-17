<?php

    // Carrega o arquivo e quebra por linhas
    $file_content = preg_split('/\r?\n/', file_get_contents($file));

?>
<ul class="file-lines">
    <?php

        foreach($file_content as $line => $content):
            $line++;
            $class = null;

            if(isset( $lines[$line] )) {
                switch($lines[$line]) {
                    case 1:
                        $class = 'coverage-on';
                        break;
                    case -1:
                        $class = 'coverage-off';
                        break;
                    case -2:
                        $class = 'coverage-dead';
                        break;
                }
            }

            if($class !== null)
                $class = " class=\"{$class}\"";

    ?>
    <li<?php echo $class; ?>><span><?php echo htmlspecialchars($content); ?></span></li>
    <?php endforeach; ?>
</ul>