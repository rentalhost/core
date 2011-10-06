var ItemObject = ModelObject.extend({
    _itemId: false,
    _caller: false,
    _elemIndex: false,
    _elemContent: false,

    // Cria um item
    init: function(caller, data, isMain, appendTo) {
        var elem = ModelObject._itemModel.clone(true).appendTo(appendTo || caller._elemTests);

        elem.attr('unit-id', data.name)
            .data('object', this);

        var spans = elem.eq(0).find('> span');
        spans.eq(0).text(isMain ? 'unidade' : 'operação');

        var buttons = elem.find('li.button');

        if(isMain === true) {
            buttons.remove('li.reject-button, li.accept-button');
        }
        else {
            buttons.remove('li.run-button, li.cancel-button');
            this._itemId = caller._classId + '.' + data.method + '.' + data.name + '.' + data.index;

            this._elemIndex = spans.eq(2);
            this._elemIndex.text('#' + data.index);

            this._isSubItem = true;
        }

        this._caller = caller;
        this._super(data, elem, spans.eq(1), spans.eq(4), spans.eq(5), buttons);

        if(data.valid_result) {
            var validResult = this.createResult();
            validResult.addClass('code valid-code');
            PageObject.logObject(validResult, data.valid_result, true, null, 'Resultado esperado:');
        }

        this.setMain(isMain);
    },

    // Adiciona um sub-item, anexado a este
    addItem: function(prefix, data) {
        for(var i in data){
            var value = data[i];

            value.method = this._elemData.name;
            value.name = prefix;
            value.index = i;

            var item = new ItemObject(this._caller, value, false, this._elemContent);
            item._elem.data({ result: value.result });
            item.setType(value.type);

            // Se for uma excessão, remove o conteúdo
            value.type === 'exception' &&
            item._elemContent.remove();

            this._itens.push(item);
        }
    },

    // Roda um item
    run: function(){
        var self = this;

        // Apaga os sub-itens
        for(var i in this._itens){
            $(this._itens[i]._elem).remove();
            $(this._itens[i]._elemContent).remove();
            delete this._itens[i];
        }

        // Executa a função
        $.ajax({
            url: 'classes/run_unit',
            type: 'POST',
            dataType: 'json',
            data: {
                from_class: this._caller._elemData.name,
                unit_method: this._elemData.name
            },
            success: function(data){
                self._itens = [];

                // Se não existir funções, define como vazio
                if(data == []){
                    self.setType('empty');
                    return;
                }

                // Após receber o nome dos métodos, cria seus itens
                for(var i in data){
                    self.addItem(i, data[i]);
                }

                // Por fim, finaliza a operação, definindo o tipo do item acolhedor
                self.discoveryType();
                // E depois pede a classe pra executar o próximo item
                self._caller.doRunItens();
            }
        });

        // Marca como iniciado
        this.setType('running');
    },

    // Define o item como principal ou não
    setMain: function(isMain) {
        this._elem.toggleClass('main', isMain);
        this._elemButtons.filter('.run-item').toggle(isMain);

        this._elemContent = this.createResult();
        this._elemContent.toggleClass('code', !isMain);
    },

    // Cancela um item
    doCancel: function(){
        if(this._type === 'waiting-type') {
            this.discoveryType();
        }
    },

    // Aceita o resultado do item
    acceptResult: function(){
        $.ajax({
            url: 'classes/accept_result',
            type: 'POST',
            dataType: 'json',
            data: {
                full_id: this._itemId,
            }
        });

        this.setType('success');
        this.closeElem();
    },

    // Rejeita o resultado do item, tornando-o como um 'novo'
    rejectResult: function(){
        $.ajax({
            url: 'classes/reject_result',
            type: 'POST',
            dataType: 'json',
            data: {
                full_id: this._itemId,
            }
        });

        this.setType('new');
    },

    // Cria e retorna um resultado
    createResult: function(){
        return ModelObject._resultModel.clone().insertAfter(this._elem);
    }
});