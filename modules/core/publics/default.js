$(function(){

	var win = $(window);
	var div_content = $('div#content > div.content');

	// Ao redimensionar a janela, esticar o conteúdo, se necessário
	win.resize(function(){
		div_content.css('min-height', win.height() - 60);
	}).resize();

	// Ao clicar em um item do toolbar, utilizar seu href
	$('[data-href]').click(function(){
		var data = $(this).data('href');
		location[data.charAt(0) === '?' ? 'search' : 'href'] = data;
		return false;
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