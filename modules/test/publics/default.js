
var LOCALE = {
    'stopped-type': 'pausado',
    'waiting-type': 'esperando...',
    'empty-type': 'sem unidades para teste',
    'running-type': 'executando...',
    'success-type': 'sucesso',
    'failed-type': 'falhou',
    'exception-type': 'excessão',
    'new-type': 'novo',
    'unavailable-type': 'não disponível',
    'idle-status': 'tomando um ar...',
    'running-status': 'em operação...'
};

$(function(){

    var win = $(window);
    var div_content = $('div#content > div.content');

    // Cria o PageObject
    PageObject = new PageObject();

    // Ao redimensionar a janela, esticar o conteúdo, se necessário
    win.resize(function(){
        div_content.css('min-height', win.height() - 60);
    }).resize();

    // Ao clicar, executa todos os testes
    $('li.button-all.run-button').click(function(){
        ClassObject.sRun(true);
    });

    // Ao clicar em um item de classe, exibe seu conteúdo
    $('div.unit-class').click(function() {
        $(this).toggleClass('active');

        var tests_elem = $(this).next();
        tests_elem.toggleClass('hidden');

        var object = $('div.unit-class').data('object');
        if(object._type === 'stopped-type')
            object.doRun(true, true);
    })
    .find('li.cancel-button').click(function(){
        $(this).closest('div.unit-class').data('object').doCancel();
    }).end()
    .find('li.run-button').click(function(){
        $(this).closest('div.unit-class').data('object').doRun(true, true);
    });

    // Ao clicar em um item de resultado, exibe seu código(s)
    $('div.unit-test').click(function() {
        $(this).toggleClass('active');
        $(this).nextUntil('div.unit-test').toggleClass('hidden');

        var object = $(this).data('result');
        if(object) {
            var elem = $(this).data('object')._elemContent;

            elem.html(PageObject.logObject(elem, object, true, null, 'Resultado obtido:'));
            $(this).removeData('result');
        }
    })
    .find('li.cancel-button').click(function(){
        $(this).closest('div.unit-test').data('object').doCancel();
    }).end()
    .find('li.run-button').click(function(){
        $(this).closest('div.unit-test').data('object').run();
    }).end()
    .find('li.accept-button').click(function(){
        $(this).closest('div.unit-test').data('object').acceptResult();
    }).end()
    .find('li.reject-button').click(function(){
        $(this).closest('div.unit-test').data('object').rejectResult();
    });

    // Evita que o elemento da classe/item seja afetado pelo clique no botão
    $('li.button').click(function(){
        return false;
    });

    // Armazena o modelo de classes e itens
    ModelObject._classModel = $('div.class-model').children().detach();
    ModelObject._itemModel = $('div.item-model').children().detach();
    ModelObject._resultModel = $('div.result-model').children().detach();
    ModelObject._logModel = $('div.log-model').children().detach();

});