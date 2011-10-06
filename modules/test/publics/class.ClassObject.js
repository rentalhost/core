var ClassObject = ModelObject.extend({
    _classId: false,
    _elemTests: false,

    // Cria um ClassObject
    init: function(data){
        var elem = ModelObject._classModel.clone(true);

        elem.appendTo('div#classes-realm')
            .data('object', this);

        var spans = elem.eq(0).find('> span');

        this._classId = data.name;

        this._elemTests = elem.eq(1);
        this._super(data, elem, spans.eq(1), spans.eq(3), spans.eq(4), elem.eq(0).find('li.button'));

        ClassObject._sClasses.push(this);
    },

    // Adiciona um novo item
    addItem: function(data){
        this._itens.push( new ItemObject(this, data, true) );
    },

    // Executa uma classe, obtendo todos os units primários
    doRun: function(emptyTests, leaveOpen){
        this._leaveOpen = leaveOpen === true;
        var self = this;

        // Se for necessário limpar os testes...
        if(emptyTests === true){
            this._elemTests.empty();
        }

        // Obtém as unidades da classe
        $.ajax({
            url: 'classes/get_units',
            type: 'POST',
            dataType: 'json',
            data: {
                from_class: this._elemData.name
            },
            success: function(data){
                self._itens = [];

                // Após receber o nome dos métodos, cria seus itens
                for(var i in data){
                    self.addItem( data[i] );
                }

                // Depois, executa seus testes
                self.setType('running');
                self.doRunItens();
            }
        });
    },

    // Executa um item em espera
    doRunItens: function(){
        for(var i in this._itens)
            if(this._itens[i]._type === 'waiting-type'
            || this._itens[i]._type === 'stopped-type') {
                this._itens[i].run();
                return;
            }

        // Ao terminar de executar todos os itens, avança para a próxima classe pendente
        this.discoveryType();
        ClassObject.sRun();
    },

    // Cancela uma operação
    doCancel: function(){
        this._leaveOpen = true;

        for(var i in this._itens)
            this._itens[i].doCancel();

        this.discoveryType();
    }
});

// As classes geradas são definidas aqui
ClassObject._sClasses = [];

// Analisa um array contendo os dados da classe
ClassObject.sParse = function(data){
    for(var i in data)
        new ClassObject(data[i]);
}

// Executa das classes que estão aguardando atualização
ClassObject.sRun = function(runAll){
    // Executa todas as classes
    if(runAll === true){
        for(var i in ClassObject._sClasses)
            ClassObject._sClasses[i].setType('waiting');
    }

    // Executa a próxima classe em espera
    PageObject.setStatus('running');
    for(var i in ClassObject._sClasses)
        if(ClassObject._sClasses[i]._type === 'waiting-type'){
            ClassObject._sClasses[i].doRun(true);
            return;
        }

    // Se nenhuma classe esta esperando, muda o status global
    PageObject.setStatus('idle');
}