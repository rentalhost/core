var PageObject = Class.extend({
    _elemStatus: false, // Armazena o status da página

    // Inicia o controle da página
    init: function() {
        this._elemStatus = $('span.labs-status');
    },

    // Define o status da página
    setStatus: function(status, info) {
        this._elemStatus.html(LOCALE[status + '-status']);
    }
});

// Cria um valor legível especial (array, object/stdClass, classes)
PageObject.prototype.logSpecial = function(appendAfter, type, data){
    appendAfter.addClass('special')
    .click(function(){
        if(data){
            var elem = $('<div class="code-body" />');

            for(var i in data)
                PageObject.logObject(elem.eq(0), data[i], false, i);
            data = false;

            appendAfter.after(elem)
                .addClass('opened');
        }
        else {
            appendAfter.toggleClass('opened')
                .next().toggle();
        }
    });
}

// Converte para um valor legível
PageObject.prototype.logObject = function(appendTo, data, firstOpen, key, titleMessage){
    var type = typeof data;

    var isSpecial = typeof data === 'object';
    if(isSpecial){
        type = data.type;
        data = data.value;
    }

    var elem = ModelObject._logModel.clone()
        .addClass('type-' + type).appendTo(appendTo);
    var realElem;

    if(titleMessage) {
        elem.eq(0).text(titleMessage);
    }
    else {
        elem.eq(0).remove();
    }

    elem.children().eq(0).text(type);

    if(typeof data === 'object') {
        PageObject.logSpecial(elem.eq(1), type, data);

        if(firstOpen !== false)
            elem.triggerHandler('click');

        return;
    }

    elem.children().eq(1).text(data);

    if(key) {
        elem.eq(1).prepend(
            $('<span class="code-key"/><span>: </span>')
                .eq(0).text(key).end()
        );
    }
}