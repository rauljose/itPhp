/**
 * Promise Dialog que pide al usuario captura: select, multiSelect, selectizer
 *
 * @example
 var selectUser = new Dialoger({keyNameValue:'iac_usr_id', keyNameLabel:'nick', dialog:{title:'Choose a user'} });
 selectUser.config.dialog.width='50em';
 var selectUserResult = selectUser.multiSelect(true, [{iac_usr_id:1,nick:'Rony'}, {iac_usr_id:2,nick:'Joel'}, {iac_usr_id:33,nick:'Mari'}],[2]);
 selectUserResult.done(function(selected){ console.log("Ok, selecciono:", selected); });
 selectUserResult.fail(function() { console.log("Usuario cancelo"); });

 var selectUser = new Dialoger({keyNameValue:'iac_usr_id', keyNameLabel:'nick', dialog:{title:'Choose a user'} });
 selectUser.config.dialog.width='50em';
 var selectUserResult = selectUser.selectizer(true, true, [{iac_usr_id:1,nick:'Rony'}, {iac_usr_id:2,nick:'Joel'}, {iac_usr_id:33,nick:'Mari'}],[2]);
 selectUserResult.done(function(selected){ console.log("Ok, selecciono:", selected); });
 selectUserResult.fail(function() { console.log("Usuario cancelo"); });

 *
 * @param settings
 * @returns {Dialoger|{makeOptions: function(bool|string, array, array): string, config: Dialoger.config}}
 * @constructor
 */
function Dialoger(settings) {
    if (!(this instanceof Dialoger)) {
        return new Dialoger(settings);
    }
    const config = $.extend(true,
        {
            keyNameValue: 'id',
            keyNameLabel: 'label',
            keyNameClass: 'class', // si existe este key pone su valor como class='' del option
            keyNameAddAttribute: '', // agrega estos atributos al tag  option
            keyUseIndexAsValue: false,
            sortOptions: true,
            label: 'Pal Label',
            dialog:{
                autoOpen: true,
                modal: true,
                closeOnEscape: true,
                title: 'Seleccione:',
                width: '40em',
                height: 550,
                resizable: true,
                draggable: true,
            },
            multiselect:{
                class: "notSelectize multiselect",
                size: 8,
                style: "width:35em"
            },
            selectizerAttributes:{

            },
            selectizerOptions: {
                create: false,
            },
        },
        settings
    );

    /**
     *
     * @param extra
     * @param defalt
     * @returns {string}
     * @private
     */
    function _attributes(extra, defalt) {
        let attributes = [];
        const pon = $.extend(true, {}, defalt, extra);
        for(let a in pon)
            if(pon.hasOwnProperty(a)) {
                let valor = pon[a];
                if(valor === null) {
                    attributes.push(a);
                    continue;
                }
                switch(typeof(valor)) {
                    case "object":
                        valor = JSON.stringify(valor);
                        break;
                    case "boolean":
                        valor = valor ? "1" : "0";
                        break;
                    default:
                        valor = valor.toString().replaceAll("\'", "&apos;");
                }
                attributes.push(a + "='" + valor + "'" );
            }
        return attributes.join(" ");
    }

    /**
     * @param selected  [{id:value, label:EsteIdEstaSelected}, ...] o [value, id,]
     * @private
     */
    function _selectedNormalize(selected) {
        let sel = {};
        for(let a in selected) if(selected.hasOwnProperty(a)) {
            let o = selected[a];
            if(typeof o  === 'object')
                sel[o[config.keyNameValue]] = o;
            else
                sel[o] = o;
        }
        return sel;
    }

    /**
     *
     * @param ponEmptyOption bool|string false: nothing, true: adds <option></option> or  '<option>Chooose</option>'
     * @param options
     * @param selected
     * @returns {string} '<option value=''>Label</option>...'
     */
    function buildOptions(ponEmptyOption, optionsIn, selected) {
        let opt = [], sel = _selectedNormalize(selected);
        if(ponEmptyOption === true)
            opt.push( '<option></option>' );
        else if(ponEmptyOption !== false)
            opt.push( ponEmptyOption );
        let options = []
        if(config.sortOptions) {
            for(let d in optionsIn)
                if(optionsIn.hasOwnProperty(d))
                    options.push(optionsIn[d]);
            options.sort(function(a, b){
                if(typeof a === 'object')
                    return a[config.keyNameLabel].strcasecmp(b[config.keyNameLabel]);
                if(typeof a === 'string')
                    return a.strcasecmp(b);
                if(a === b)
                    return 0;
                if(a > b)
                    return 1;
                return 0;
            });
        } else
            options = optionsIn;

        for(let a in options) {
            if (options.hasOwnProperty(a)) {
                let o = options[a], value, label, addClass = '', addTag = '';
                if (config.keyUseIndexAsValue) {
                    value = a;
                    label = o;
                } else if (typeof o === 'object') {
                    value = o[config.keyNameValue];
                    label = o[config.keyNameLabel];
                    addClass = o.hasOwnProperty(config.keyNameClass) ? ' class="' + o[config.keyNameClass] + '" ' : '';
                    addTag = o.hasOwnProperty(config.keyNameAddAttribute) ? ' ' + o[config.keyNameAddAttribute] + ' ' : '';
                } else {
                    value = label = o;
                }

                let selected = sel.hasOwnProperty(value) ? " selected " : '';
                opt.push(`<option value="${value}"${selected}${addClass}${addTag}>${label}</option>`)
            }
        }
        return opt.join("\r\n");
    }

    function select(required, ponEmptyOption, options, selected, selectAttributes) {
        let defer = $.Deferred();
        const selectTag = `<select id='dialoger_select' ${selectAttributes}>`;
        const selectOptions = buildOptions(ponEmptyOption, options, typeof selected === 'undefined' ? [] : selected);
        $(`<div class='dialoger' style='padding-top:1em'><p>${selectTag}${selectOptions}</select></div>`)
            .dialog($.extend(true,
                    {
                        autoOpen: true,
                        modal: true,
                        closeOnEscape: true,
                        title: 'Seleccione',
                        width: '40em',
                        height: '550',
                        resizable: true,
                        draggable: true,
                        close: function () {
                            if(!defer.isResolved && !defer.isRejected)
                                defer.reject();
                            $(this).dialog('destroy').remove();
                        },
                        buttons:[
                            {
                                text: "Guardar",
                                icon: "ui-icon-check",
                                click: function () {
                                    let selected = $("#dialoger_select").val();
                                    if(required && selected.length === 0) {
                                        $(this).dialog("option", "title", "¡Dato Requerido!")
                                        return;
                                    }
                                    if(!defer.isResolved && !defer.isRejected)
                                        defer.resolve( selected);
                                    $(this).dialog("close");
                                },
                            },
                            {text: "Cancelar", icon: "	ui-icon-closethick", click: function () {$(this).dialog("close");},}
                        ],
                    },
                    config.dialog
                )
            );
        return defer.promise();
    }

    function selectizer(required, ponEmptyOption, options, selected, selectAttributes, selectizerOptions) {
        let defer = $.Deferred();
        let attr = _attributes(config.selectizerAttributes, {} );
        const selectTag = `<select id='dialoger_select' ${attr} ${selectAttributes}>`;
        const selectOptions = buildOptions(ponEmptyOption, options, typeof selected === 'undefined' ? [] : selected);
        $(`<div class='dialoger' style='padding-top:1em'><p>${selectTag}${selectOptions}</select></div>`)
            .dialog($.extend(true,
                    {
                        autoOpen: true,
                        modal: true,
                        closeOnEscape: true,
                        title: 'Seleccione',
                        width: '40em',
                        height: '550',
                        resizable: true,
                        draggable: true,
                        open: function() {
                            if(typeof selectizerOptions !== 'object')
                                selectizerOptions = {};
                            $("#dialoger_select").selectize($.extend(true,{create: false}, config.selectizerOptions,selectizerOptions ) );
                        },
                        close: function () {
                            if(!defer.isResolved && !defer.isRejected)
                                defer.reject();
                            $(this).dialog('destroy').remove();
                        },
                        buttons:[
                            {
                                text: "Guardar",
                                icon: "ui-icon-check",
                                click: function () {
                                    let selected = $("#dialoger_select").val();
                                    if(required && selected.length === 0) {
                                        $(this).dialog("option", "title", "¡Dato Requerido!")
                                        return;
                                    }
                                    if(!defer.isResolved && !defer.isRejected)
                                        defer.resolve( selected);
                                    $(this).dialog("close");
                                },
                            },
                            {text: "Cancelar", icon: "	ui-icon-closethick", click: function () {$(this).dialog("close");},}
                        ],
                    },
                    config.dialog
                )
            );
        return defer.promise();
    }

    /**
     * Pone un multiselect
     *
     * @param required bool true field is required
     * @param options array [{id:value, label:letrero}, ...] o ['option1','option2']
     * @param selected      [{id:value, label:EsteIdEstaSelected}, ...] o [id, id,]
     * @param selectAttributes string
     */
    function multiSelect(required, options, selected, selectAttributes) {
        let defer = $.Deferred();
        let attr = _attributes(config.multiSelect, {'class': "notSelectize multiselect", 'size': 8, 'style': "width:35em;height:400px;"} );
        const selectTag = `<select multiple id='dialoger_select' ${attr} ${selectAttributes}>`;
        const selectOptions = buildOptions(false, options, typeof selected === 'undefined' ? [] : selected);
        $(`<div class='dialoger' style='padding-top:1em'><div style='clear-after:both'>${selectTag}${selectOptions}</select></div>`)
            .dialog($.extend(true,
                    {
                        autoOpen: true,
                        modal: true,
                        closeOnEscape: true,
                        title: 'Seleccione',
                        width: '40em',
                        height: '550',
                        resizable: true,
                        draggable: true,
                        open: function() {
                            $(this).find('#dialoger_select').multiselect({sortable: true, locale: 'es'});
                        },
                        close: function () {
                            if(!defer.isResolved && !defer.isRejected)
                                defer.reject();
                            $(this).dialog('destroy').remove();
                        },
                        buttons:[
                            {
                                text: "Guardar",
                                icon: "ui-icon-check",
                                click: function () {
                                    let selected = $("#dialoger_select").val();
                                    if(required && selected.length === 0) {
                                        $(this).dialog("option", "title", "¡Dato Requerido!")
                                        return;
                                    }
                                    if(!defer.isResolved && !defer.isRejected)
                                        defer.resolve( selected);
                                    $(this).dialog("close");
                                },
                            },
                            {text: "Cancelar", icon: "	ui-icon-closethick", click: function () {$(this).dialog("close");},}
                        ],
                    },
                    config.dialog
                )
            );
        return defer.promise();
    }

    return {
        config: config,
        makeOptions: function(ponEmptyOption, options, selected) {return  buildOptions(ponEmptyOption, options, selected); },
        select: function(required, ponEmptyOption, options, selected, selectAttributes) {
            return select(required, ponEmptyOption, options, selected, selectAttributes);
        },
        selectMultiple: function(required, options, selected, tags) { return multiSelect(required, options, selected, tags); },
        selectize: function(required, ponEmptyOption, options, selected, selectAttributes, selectizerOptions) {
            return selectize(required, ponEmptyOption, options, selected, selectAttributes, selectizerOptions);
        },
        selectizer: function(required, ponEmptyOption, options, selected, selectAttributes, selectizerOptions) {
            return selectizer(required, ponEmptyOption, options, selected, selectAttributes, selectizerOptions);
        },

    }

}