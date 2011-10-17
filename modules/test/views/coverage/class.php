<div class="unit-class new-type">
    <span class="strong">arquivo</span>
    <span class="name"><?php echo core::get_path_fixed(core::get_path_clipped($file)); ?></span>
    <span>::</span>
    <span class="result">sucesso</span>
    <span class="message">(100%)</span>
</div>

<div class="unit-tests new-type">
    <?php load('coverage/file', array( 'file' => $file, 'lines' => $lines )); ?>
</div>

<div class="unit-footer new-type"></div>