$(function(){

	var win = $(window);
	var div_content = $('div#content > div.content');
	var div_classes = $('div.unit-class');
	var div_units = $('div.unit-test');
	var div_lines = $('ul.file-lines li');

	// Ao redimensionar a janela, esticar o conteúdo, se necessário
	win.resize(function(){
		div_content.css('min-height', win.height() - 60);
	}).resize();

	// Exibe ou esconde as unidades de uma classe
	div_classes.click(function(){
		$(this).next().toggle();
	});

	// Exibe ou esconde um resultado
	div_units.click(function(){
		$(this).nextUntil('div.unit-test').toggleClass('hidden');
	});

	// Dá um auto-click em alguns tipos de classes
	div_classes.filter('.unavailable-type, .success-type').click();

	// Dá um auto-click em alguns tipos de resultados
	div_units.filter('.new-type, .removed-type, .failed-type, .exception-type').click();

	// Ativa o botão "Aceitar" para um novos, falhados e removidos, e "Rejeitar" para tipo sucesso
	div_units.filter('.new-type, .failed-type, .removed-type').find('li.button.accept-button').removeClass('disabled');
	div_units.filter('.success-type').find('li.button.reject-button').removeClass('disabled');

	// Ativa o botão "Aceitar"
	div_units.find('li.button.accept-button').click(function(){
		$(this).addClass('disabled');
		$.ajax({
			url: 'classes/accept_result',
			type: 'POST',
			dataType: 'json',
			data: { id: $(this).closest('.unit-test').data('unit-id') }
		});
	});

	// Ativa o botão "Rejeitar"
	div_units.find('li.button.reject-button').click(function(){
		$(this).addClass('disabled');
		$.ajax({
			url: 'classes/reject_result',
			type: 'POST',
			dataType: 'json',
			data: { id: $(this).closest('.unit-test').data('unit-id') }
		});
	});

	// Ao clicar em ignorar, deixa a linha cinza
	div_lines.find('span.button.ignore-button').click(function(){
		var parent = $(this).closest('li');
		parent.removeClass('coverage-off')
			.addClass('coverage-dead')
			.find('span.button').hide()
			.filter('span.recovery-button').show();

		$.ajax({
			url: 'coverage/ignore_line',
			type: 'POST',
			dataType: 'json',
			data: {
				file: parent.parent().data('file'),
				content: parent.data('content')
			}
		});
	});

	// Ao clicar em recuperar, deixa a linha em off
	div_lines.find('span.button.recovery-button').click(function(){
		var parent = $(this).closest('li');
		parent.addClass('coverage-off')
			.removeClass('coverage-dead')
			.find('span.button').hide()
			.filter('span.ignore-button').show();

		$.ajax({
			url: 'coverage/recovery_line',
			type: 'POST',
			dataType: 'json',
			data: {
				file: parent.parent().data('file'),
				content: parent.data('content')
			}
		});
	});

	// Para os coverage ignorados, muda o status do botão
	div_lines.filter('.coverage-ignored').each(function(){
		$(this).removeClass('coverage-off')
			.addClass('coverage-dead')
			.find('span.button').hide()
			.filter('span.recovery-button').show();
	});

	// Cancela o default behavior em clicks
	div_units.find('li.button').click(false);

	// Ao clicar em um item do toolbar, utilizar seu href
	$('ul#toolbar li[data-href]').click(function(){
		location.search = $(this).data('href');
	});

	// Botão para aceitar todos os resultados
	var div_units_accept_all = $('ul#toolbar li.accept-all');
	var div_units_need_accept = div_units.find('li.button.accept-button').not('.disabled');

	// Se houver itens a serem salvos, ativa o botão
	if(div_units_need_accept.length !== 0)
		div_units_accept_all.removeClass('disabled');

	// Ao clicar em aceitar todos na toolbar, auto-clica em aceitar de cada item pendente
	div_units_accept_all.not('.disabled').click(function(){
		var ids = [];
		div_units_need_accept.addClass('disabled')
			.closest('div.unit-test').each(function(){
				ids.push($(this).data('unit-id'));
			});

		$.ajax({
			url: 'classes/accept_multi_results',
			type: 'POST',
			dataType: 'json',
			data: { ids: ids }
		});
	});

	// Ao clicar no idioma, abrir o modal de seleção
	$('div.lang-change').click(function(){
		$('div.black-background, div.modal-content').css({opacity: 0})
			.show()
			.animate({opacity: 1}, 300);
	});

	// Esconde o modal, se clicar diretamente sobre o black-background
	$('div.black-background').click(function(ev){
		if(ev.target !== this)
			return false;

		$('div.black-background, div.modal-content')
			.animate({opacity: 0}, 200, null, function() {
				$(this).hide();
			});
	});

	// Ao clicar em uma opção de idioma, atualizar a página
	$('ul.lang-list > li').click(function(){
		location.search = '?language-id=' + encodeURIComponent($(this).data('lang-id'));
	});

});