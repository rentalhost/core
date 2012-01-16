<div class="unit-test <?php echo $unit->type; ?>-type" data-unit-id="<?php echo $unit->id; ?>">
	<span class="strong"><?php echo $lang->strong_unit; ?></span>
	<span class="name"><?php echo $unit->method; ?></span>
	<span class="index"><?php echo $unit->prefix; ?> #<?php echo $unit->index; ?></span>
	<span>::</span>
	<span class="result"><?php echo $lang->get_value("type_{$unit->type}"); ?></span>
	<span class="message">
		<?php echo @$unit->message ? " - {$unit->message}" : null; ?>
	</span>

	<ul class="actions">
		<li class="button accept-button disabled"><?php echo $lang->button_accept; ?></li>
		<li class="button reject-button disabled"><?php echo $lang->button_reject; ?></li>
	</ul>
</div>

<?php if(@$unit->result): ?>
	<?php if(isset($unit->result['new'])): ?>
		<div class="unit-result code <?php echo $unit->type; ?>-type hidden">
			<div class="code-title"><?php echo $lang->result_received; ?></div>
			<?php echo call( '__export::export_html', $unit->result['new'] ); ?>
		</div>
		<div class="unit-result code <?php echo $unit->type; ?>-type hidden">
			<div class="code-title"><?php echo $lang->result_original; ?></div>
			<?php echo call( '__export::export_html', $unit->result['old'] ); ?>
		</div>
	<?php else: ?>
		<div class="unit-result code <?php echo $unit->type; ?>-type hidden">
			<?php echo call( '__export::export_html', $unit->result ); ?>
		</div>
	<?php endif; ?>
<?php endif; ?>