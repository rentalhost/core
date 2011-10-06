var ModelObject = Class.extend({
    // Armazena a coleção do modelo
    _itens: [],
    _isSubItem: false,
    _elem: false,
    _elemData: false,
    _elemName: false,
    _elemResult: false,
    _elemMessage: false,
    _elemButtons: false,
    _leaveOpen: false,
    _type: false,

    // Cria um modelo
    init: function(data, elem, elemName, elemResult, elemMessage, elemButtons) {
        this._elem = elem;
        this._elemData = data;
        this._elemName = elemName.text(data.name);
        this._elemResult = elemResult;
        this._elemMessage = elemMessage;
        this._elemButtons = elemButtons;

        this.setType('stopped');
        this.setMessage(data.message);
    },

    // Define o tipo do modelo
    setType: function(type){
        this._type !== false &&
        this._elem.removeClass(this._type);

        this._type = type + '-type';
        this._elem.addClass(this._type);

        this.toggleButtons('.reject-button, .run-button', true);
        this.toggleButtons('.accept-button, .cancel-button', false);

        var typePriority = 0;
        switch(type){
            case 'stopped':
            case 'empty':
                this.toggleButtons('*', false);
                this.toggleButtons('.run-button', true);
                typePriority = 0;
                break;
            case 'unavailable':
                this.toggleButtons('*', false);
                typePriority = 1;
                break;
            case 'waiting':
                this.toggleButtons('.cancel-button', true);
                this.toggleButtons('.reject-button, .run-button', false);
                typePriority = 2;
                break;
            case 'running':
                this.toggleButtons('.cancel-button', true);
                this.toggleButtons('.reject-button, .run-button', false);
                this._elem.trigger('click');
                typePriority = 3;
                break;
            case 'success':
                this.toggleButtons('.accept-button', false);
                this.toggleButtons('.reject-button', true);
                typePriority = 4;
                break;
            case 'new':
                this.toggleButtons('.reject-button', false);
                this.toggleButtons('.accept-button', true);
                typePriority = 5;
                break;
            case 'failed':
                this.toggleButtons('.reject-button', false);
                this.toggleButtons('.accept-button', true);
                typePriority = 6;
                break;
            case 'exception':
                this.toggleButtons('.reject-button, .accept-button', false);
                typePriority = 7;
                break;
        }

        this._elemResult.text(LOCALE[this._type]);
        this._typePriority = typePriority;
    },

    // Descobre o tipo do item, baseado-se nos seus elementos
    discoveryType: function(){
        var type = 'empty';
        var typePriority = 0;

        for(var i in this._itens)
            if(this._itens[i]._typePriority > typePriority) {
                type = this._itens[i]._type;
                type = type.substr(0, type.length - 5);
                typePriority = this._itens[i]._typePriority;
            }

        this.setType(type);
        this.closeElem();
    },

    // Fecha o elemento, caso não haja nada demais
    closeElem: function(){
        if(this._type === 'success-type'
        || this._type === 'new-type'){
            if(this._elem.is('.active') === this._leaveOpen
            && this._isSubItem === false) {
                this._elem.triggerHandler('click');
                this.openType(this._type);
            }
        }
    },

    // Abre os itens do tipo solicitado
    openType: function(type){
        for(var i in this._itens)
            if(this._itens[i]._type === type)
                this._itens[i]._elem.triggerHandler('click');
    },

    // Define uma mensagem
    setMessage: function(message){
        if(typeof message === 'undefined')
            return;

        this._elemMessage.text(message === '' ? '' : ' - ' + message);
    },

    // Altera a atividade os botões
    toggleButtons: function(filter, mode){
        this._elemButtons.filter(filter).toggleClass('disabled', !mode);
    }
});