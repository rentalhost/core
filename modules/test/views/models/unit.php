<div class="unit-test <?php echo $type; ?>-type" data-unit-id="<?php echo $id; ?>">
	<span class="strong">unidade</span>
	<span class="name"><?php echo $method; ?></span>
	<span class="index"><?php echo $prefix; ?> #<?php echo $index; ?></span>
	<span>::</span>
	<span class="result"><?php echo $type; ?></span>
	<span class="message">
		<?php echo @$message ? " - {$message}" : null; ?>
	</span>

	<ul class="actions">
		<li class="button accept-button disabled">Aceitar</li>
		<li class="button reject-button disabled">Rejeitar</li>
	</ul>
</div>

<?php if(@$result): ?>
	<?php if(isset($result['new'])): ?>
		<div class="unit-result code <?php echo $type; ?>-type hidden">
			<div class="code-title">Resultado obtido:</div>
			<?php echo call( '__export::export_html', $result['new'] ); ?>
		</div>
		<div class="unit-result code <?php echo $type; ?>-type hidden">
			<div class="code-title">Resultado original:</div>
			<?php echo call( '__export::export_html', $result['old'] ); ?>
		</div>
	<?php else: ?>
		<div class="unit-result code <?php echo $type; ?>-type hidden">
			<?php echo call( '__export::export_html', $result ); ?>
		</div>
	<?php endif; ?>
<?php endif; ?>