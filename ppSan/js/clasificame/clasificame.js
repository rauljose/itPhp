/**
 * version 1.1.1 2022-10-29
 *
 * Permite mostrar items (id, label, clasificación) en su columna de clasificación, cambiarlos de columna y obtener
 * en que columna quedaron con $("#id").clasificame("value")
 *
 * $("#id").claseificame({
 *             'clasificacion': [
 *             {clasificaId:'Nada', label:'X', title:'Sin Permiso'},
 *             {clasificaId:'RO', label:'RO', title:'Sólo Lectura'},
 *             {clasificaId:'RW', label:'RW', title:'Editar'}
 *         ],
 *         valueId:'user_id', // ie iac_user_id
 *         valueDisplay:'nick', // ie nick
 *         valueColumnKey:'permiso', // puede_editar has values: Nada, RO o RW
 *         'values': [],
 *              // [  {user_id:1, name:'Mary',permiso:'Nada',},],
 *              // o  {1:{name:'Mary',permiso:'RO',},],
 *              // o  {uniqueValue:{user_id:1,name:'Mary',permiso:'Nada',},],
 *              // o {11:"Mary", 12:"Joe",}
 * }); // ver options
 * $("#id").clasificame("value"); => {Nada:[id1, id2,], RO:[], ...}
 *
 * ver ejemplo_clasificame.html en esta carpeta
 *
 * public: method:function
 * privates: _method:function
 */
$.widget( "vitex.clasificame", {
    version:function() {return "1.1.1 2022-10-29"},

    options: {
        clasificacion: [
            {clasificaId:'Nada', label:'X', title:'Sin Permiso'},
            {clasificaId:'RO', label:'RO', title:'Sólo Lectura'},
            {clasificaId:'RW', label:'RW', title:'Editar'}
        ],
        valueId:'user_id', // ie iac_user_id
        valueDisplay:'nick', // ie nick
        valueColumnKey:'permiso', // puede_editar has values: Nada, RO o RW
        values: [],
        // [  {user_id:1, name:'Mary',permiso:'Nada',},],
        // o  {1:{name:'Mary',permiso:'RO',},],
        // o  {uniqueValue:{user_id:1,name:'Mary',permiso:'Nada',},],
        // o {11:"Mary", 12:"Joe",}
        editable:true,
        draggable:{
            scroll: true,
            cancel: ".clasificaToolBar", // clicking div toolbar no inicia drag
            revert: "invalid", // when not dropped, the item will be returned to its initial position
            containment: "document",
            helper: "clone",
            cursor: "move",
            //@see https://api.jqueryui.com/draggable/
        },
        droppable:{
            classes: {"ui-droppable-active": "custom-state-active"},
            //@see https://api.jqueryui.com/droppable/
        },
    },

    _selector: ".clasificaItemList",

    _create: function() {
        this.valueId = this.options.valueId;
        this.valueDisplay = this.options.valueDisplay;
        this.valueColumnKey = this.options.valueColumnKey;

        // create html
        this.element.html(this._html());
        $(".clasificaAllTo", this.element).on('click',this._allTo);

        if(typeof this.options.values !== 'undefined')
            this._addValuesDo(this.options.values);
        if(!this.options.editable) {
            this.readonly();
            return;
        }
        let _selector = this._selector;
        let optionsDropabble = this.options.droppable;
        let optionsDraggable = this.options.draggable;
        // activate html
        $(this._selector, this.element).each(function(){
            const $this  = $(this);
            // set dropabble
            $this.droppable(
                $.extend(
                    {
                        accept: `${_selector} li`,
                        drop: function( event, ui ) {$(event.target).append(ui.draggable)},
                    },
                    optionsDropabble
                ));
            // set dragabble
            $( "li",$this).draggable(optionsDraggable);
        });
    },

    _destroy: function() {
        this.element.html("");
    },

    option:function(name, value) {
        if(typeof value === "undefined") {
            if(name === 'options' || name === 'option')
                return this.options;
            if(this.options.hasOwnProperty(name))
                return this.options[name];
            return null;
        }
        let ro = {valueId:true,valueDisplay:true, valueColumnKey:true,values:true};
        if(ro.hasOwnProperty(name))
            throw 'Attempt to change Read Only Option: ' + name;
        this.options[name] = value;
        if(name === 'editable')
            if(value) {
                let $elem = $(this.element);
                $elem.find(".clasificaToolBar").each(function(){$(this).show();});
                $elem.find(".clasificaAllTo").each(function(){$(this).show();});
                $elem.find(".clasificaItemDontMove").removeClass("clasificaItemDontMove").
                    addClass("clasificaItemMove");
                this.refresh();
            }
            else
                this.readonly();
        return this;
    },

    addValues:function(values) {
        this._addValuesDo(values);
        this.refresh();
    },

    readonly:function() {
        this.options.editable = false;
        $(this._selector, this.element).each(function(){
            const $this  = $(this);
            $this.droppable("destroy");
        });
        let $elem = $(this.element);
        $elem.find(".clasificaToolBar").each(function(){$(this).hide();});
        $elem.find(".clasificaAllTo").each(function(){$(this).hide();});
        $elem.find(".clasificaItemMove").removeClass("clasificaItemMove").addClass("clasificaItemDontMove");

    },

    refresh:function() {
        if(!this.options.editable)
            return;
        // activate html
        $(this._selector, this.element).each(function(){
            const $this  = $(this);
            // set dropabble
            $this.droppable("destroy").droppable(
                $.extend(
                    {
                        accept: `${this._selector} li`,
                        drop: function( event, ui ) {$(event.target).append(ui.draggable)},
                    },
                    this.options.droppable
                ));
            // set dragabble
            $( "li",$this).draggable(this.options.draggable);
        });
    },

    allTo: function(toClasificaId) {
        if(!this.options.editable) {
            console.log("Can't do: editable = false");
            return;
        }
        if(typeof toClasificaId === 'undefined') {
            console.log("this.allTo()", "Missing parameter toClasificaId");
            return;
        }

        let $to =  $(`.clasificaItemList[data-clasificakey='${toClasificaId}']`, this.element);
        if($to.length === 0) {
            console.log("this.allTo()", "Invalid clasificaId ", toClasificaId);
            return;
        }

        let me = this;
        $(this._selector, this.element).each(function() {
            let $this = $(this);
            if($this.data('clasificakey') !== toClasificaId) {
                $this.find("LI").each(function (){
                    let $li = $(this);
                    me._toolbarUnpress($li);
                    $li.find(`SPAN[data-clasificato='${toClasificaId}']`).addClass('pressed');
                    $to.append($li);
                });
            }
        });
    },

    fromTo: function(fromClasificaId, toClasificaId) {
        if(!this.options.editable) {
            console.log("Can't do: editable = false");
            return;
        }
        if(typeof fromClasificaId === 'undefined' || typeof toClasificaId === 'undefined') {
            console.log("this.fromTo(fromClasificaId, toClasificaId)", "Need 2 parameters fromClasificaId, toClasificaId");
            return;
        }
        let $from =  $(`.clasificaItemList[data-clasificakey='${fromClasificaId}']`, this.element);
        if($from.length === 0) {
            console.log("this.fromTo(fromClasificaId, toClasificaId)", "Invalid fromClasificaId: ", fromClasificaId);
            return;
        }
        let $to =  $(`.clasificaItemList[data-clasificakey='${toClasificaId}']`, this.element);
        if($to.length === 0) {
            console.log("this.fromTo(fromClasificaId, toClasificaId)", "Invalid toClasificaId ", toClasificaId);
            return;
        }

        let me = this;
        $from.find("LI").each(function(){
            let $this = $(this);
            me._toolbarUnpress($this);
            $this.find(`SPAN[data-clasificato='${toClasificaId}']`).addClass('pressed');
            $to.append($(this));
        });
    },

    value:function() {
        let ret = {}
        $(this._selector, this.element).each(function() {
            let $this = $(this);
            let clasif = $this.data("clasificakey");
            ret[clasif] = [];
            $this.find("LI").each(function (){
                let id = $(this).data("clasificaid");
                if(typeof id !== 'undefined')
                    ret[clasif].push(id);
            })
        });
        return ret;
    },

    clear:function() {
        $(this._selector, this.element).each(function() {
            let $this = $(this);
            $this.find("LI").each(function (){ $(this).remove(); });
        });
    },

    _allTo: function(event) {
        let elementId = $(event.target).data('clasificaelementid');
        $(`#${elementId}`).clasificame("allTo", $(event.target).data('clasificakey'));
    },

    _addValuesDo:function(values) {
        let nada = this.options.clasificacion[0].clasificaId;
        for (let v in values)
            if (values.hasOwnProperty(v)) {
                let id, label, clasificaId;
                if (typeof values[v] === 'object') {
                    let item = values[v];
                    id = typeof item[this.valueId] === 'undefined' ? v : item[this.valueId];
                    label = typeof item[this.valueDisplay] === 'undefined' ? item['id'] : item[this.valueDisplay];
                    clasificaId = typeof item[this.valueColumnKey ] === 'undefined' ? nada : item[this.valueColumnKey];
                } else {
                    id = v;
                    label = values[v];
                    clasificaId = nada;
                }

                let $to = $(`.clasificaItemList[data-clasificakey='${clasificaId}']`, this.element);
                if ($to.length) {
                    let $li = $(`<li data-clasificaid="${id}">${label}</li>`);
                    let toolbar = this._toolbar(clasificaId)
                    if(toolbar.length)
                        $li.append($(toolbar).on('click',this._clickTo));
                    $to.append($li.on('click', this._clickTo));
                }
            }
    },

    _toolbar: function(clasificaId) {
        if(typeof clasificaId === 'undefined')
            clasificaId = this.options.clasificacion[0]['clasificaId'];
        let buttons = [];
        for(const b of this.options.clasificacion) {
            const classPressed = clasificaId === b.clasificaId ? "class='pressed'" : "";
            const label = typeof b.label === 'undefined' ? b.clasificaId.replaceAll('_', ' ') : b.label;
            const title = typeof b.title === 'undefined' ? b.label : b.title;
            buttons.push(`<span ${classPressed} title="${title}" data-clasificaTo="${b.clasificaId}">${label}</span>`);
        }
        return `<div class="clasificaToolBar">` + buttons.join("\r\n\t") + `</div>`;
    },

    _toolbarUnpress:function(el) {
        el.find(".clasificaToolBar").children("SPAN").each(function(){$(this).removeClass('pressed');});
    },

    _html: function() {
        let columns = [];
        for(const b of this.options.clasificacion) {
            const label = typeof b.label === 'undefined' ? b.clasificaId.replaceAll('_', ' ') : b.label;
            const title = typeof b.title === 'undefined' ? label : b.title;
            columns.push(`
                    <div class="clasificaFlexItem" data-clasificaContainer="${b.clasificaId}">
                            <div class="clasificaItemTitle" data-clasificaTitle="${b.clasificaId}">
                                <h3>${title} 
                                <span title="Pasar todos a ${title}" data-clasificaelementid="${this.element.attr('id')}" data-clasificaKey="${b.clasificaId}"
                                      class="clasificaAllTo" >⯯</span>
                                </h3>
                            </div>
                            <ul class="clasificaItemList" data-clasificakey="${b.clasificaId}">
                            </ul>
                    </div>`
            );
        }
        return `<div class="clasificaFlexRow">` + columns.join("\r\n") + `\r\n</div>`;
    },

    _clickTo:function(event) {
        event.stopImmediatePropagation();
        event.stopPropagation();
        let target = $(event.target);
        const sendTo = target.data('clasificato');
        if(typeof sendTo !== 'string' || sendTo.length === 0)
            return;
        let liElement = $(this).parent();
        if(typeof liElement === 'undefined' || liElement == null)
            return;
        if(liElement[0].tagName !== 'LI')
            return;
        liElement.find(".clasificaToolBar").children("SPAN").each(function(){$(this).removeClass('pressed');});
        target.addClass('pressed')
        $(`.clasificaItemList[data-clasificakey='${sendTo}']`, this.element).append(liElement);
    },

});
