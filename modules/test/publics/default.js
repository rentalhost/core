
$(function(){

    var win = $(window);
    var div_content = $('div#content > div.content');
    var div_units = $('div.unit-test');

    // Ao redimensionar a janela, esticar o conteúdo, se necessário
    win.resize(function(){
        div_content.css('min-height', win.height() - 60);
    }).resize();

    // Exibe ou esconde um resultado
    div_units.click(function(){
        $(this).nextUntil('div.unit-test').toggleClass('hidden');
    });

    // Dá um auto-click em alguns tipos de resultados
    div_units.filter('.new-type, .failed-type, .exception-type, removed-type').click();

    // Ativa o botão "Aceitar" para um novos ou falhados, e "Rejeitar" para tipo sucesso
    div_units.filter('.new-type, .failed-type').find('li.button.accept-button').removeClass('disabled');
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

    // Cancela o default behavior em clicks
    div_units.find('li.button').click(false);

});