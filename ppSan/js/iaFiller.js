/**
 Requiers
    jquery, jqueryui
    iaValuesIn.js, diatrics.js, populateForm

 Usage:


// document.getElementById('name').addEventListener('keypress', function(event) {if(event.keyCode == 13) event.preventDefault();});

 Options {
        'url':'ajax/iaFiller.php',
        container: $('#formfiltro'),
        allowEdit:true, // false quita save,delete plantilla
 }

//@TODO id/iaFiller_id
//@TODO clear set default in data-default o en populate
//@TODO toast saved,applied,cleared,deleted
//@TODO generalTemplates vs UserTemplates, general solo con allowWrite, tag en data (tag/tag+userid) select con group, leer 2 veces, ?
$("#iaFiller").filler();
*/
/**
 * @author Informática Asocaida SA de CV
 * @author Raul Jose Santos Bernard
 * @version 1.0.2
 * @copyright 2017
 * @license MIT
 */
;(function($) {
'use strict';
$.widget( "ia.filler", {

// Default options.
options: {
    'url':'ajax/iaFiller.php',
    container: $('#formfiltro'),
    allowEdit:true,
    version:"1.0.0",
},

_create: function() {
    // Options are already merged and stored in this.options, dom element in this.element
    this._loadPlantillas();
    iac.selectColorized(this.element);

   this._on(false, $(".iaFiller_nombre" , this.element), {'change':this.descriptionPut} );
   this._on( $(".iaFiller_apply" , this.element), {'click':this.fill} );

   if(this.options.allowEdit) {
       this._on( $(".iaFiller_save" , this.element), {'click':this.fillerSaveAs} );
       this._on( $(".iaFiller_del" , this.element), {'click':this.fillerDelConfirm} );
   } else {
        $(".iaFiller_save" , this.element).prop('disabled',true).hide();
        $(".iaFiller_del" , this.element).prop('disabled',true).hide();
   }
   this._on( $(".iaFiller_reset" , this.element), {'click':this.containerClear} );
},

_loadPlantillas: function() {
    var me=this;
    $.ajax({
        url:me.options.url,
        method:'POST',
        cache:false,
        dataType:'json',
        data:{action:'list',tag:me.options.tag},
    }).done(function(data, textStatus, jqXHR ) {
        if(typeof data.errorMessage !== 'undefined' && data.errorMessage != ''  ) {
            me._alert_error(data.errorMessage);
            return;
        }
        for(var i=0,iLen=data.length; i<iLen; i++) {
            var v=data[i];
            me._fillerOptionAddReplace(v.nombre,v.iaFiller_id,v.descripcion,v.color,v.vals);
        }
        me.element.find(".iaFiller_nombre").val('');
        me.descriptionPut();
    }).fail(function( jqXHR, textStatus, errorThrown ) {
        me._alert_error(textStatus);
    });
},

///////////////////
// values controled by filter
///////////////////
    containerClear: function() {
        this._cursorSetWait();
        this.options.container.find("INPUT").each(function(){
                var e=$(this), type=e.attr('type').toLowerCase();
                if( type === 'checkbox')
                    e.prop('checked',false).trigger('change');
                else if( type === 'radio')
                    ;
                else if(typeof e.data('autoNumeric') === 'object')
                    e.autoNumeric('set','');
                else if(type!=='button' && type!=='submit')
                    e.val('');
        });
        this.options.container.find("select").find(":selected").each(function(){$(this).prop('selected',false);});
        this.options.container.find(".multiselect").each(function(){$(this).multiselect('refresh');});
        this._cursorSetDefault();
     },

    values : function() {
        return jQuery(this.options.container).iaValuesIn(options);
    },

    fill: function () {
        var data = this.element.find('.iaFiller_nombre').find(":selected").data();
        if(typeof data === 'undefined' || typeof data.vals !== 'object') {
            return;
        }
        this.containerClear(false);
        this._cursorSetWait();
        $.populateForm(this.options.container,data.vals);
        this.options.container.find(".multiselect").each(function(){$(this).multiselect('refresh');});
        this._cursorSetDefault();
    },

    descriptionPut:function () {
        var data = this.element.find(":selected").data();
        if(typeof data === 'undefined') {
            this.element.find("#iaFillerDescribe").html("");
            return;
        }
        this.element.find("#iaFillerDescribe").text(data.descr || '')
        .removeClass( this.element.find("#iaFillerDescribe").data('color') )
        .data('color',data.color)
        .addClass(data.color);
    },

///////////////////
// add/edit filler
///////////////////
    fillerSaveAs: function() {
        var me=this, $option = this.element.find(".iaFiller_nombre").find(":selected"),nombre='',id='',descr='', color='';
        if($option.length == 1) {

            id = $option.val();
            nombre = id == '' ? '' : $option.text();
            descr =  id == ''  ? '' : $option.data('descr');
            color = $option.data('color');

        }
        var normal = color == 'iaFillerNormal' ? 'selected=selected' : '',
            azul = color == 'iaFillerAzul' ? 'selected=selected' : '',
            rojo = color == 'iaFillerRojo' ? 'selected=selected' : '',
            verde = color == 'iaFillerVerde' ? 'selected=selected' : '';
        var table = `<div><table class='tabla'>
            <thead><tr><th>Nombre</th></thead><tbody>
            <tr><td><input type='text' value='${nombre}' nombre='iaFillerNewName' id='iaFillerNewName' maxlength='48' style='width:30em' required='required'/>
            <br /><span class='iaFillerErrorMsg'></span>
            </td></tr>
            <tr><th>Descripción</th></tr>
            <tr><td><input type='text' value='${descr}' nombre='iaFillerNewDescripcion' id='iaFillerNewDescripcion' maxlength='254' style='width:30em' /></td></tr>
            <tr><td><b>Color</b>:
            <select nombre='iaFillerNewColor' id='iaFillerNewColor'>
                <option value='iaFillerNormal' class='iaFillerNormal' ${normal} >Normal</option>
                <option value='iaFillerAzul' class='iaFillerAzul' ${azul} >Azul</option>
                <option value='iaFillerRojo' class='iaFillerRojo' ${rojo} >Rojo</option>
                <option value='iaFillerVerde' class='iaFillerVerde' ${verde} >Verde</option>
            </select></td></tr>
            </tbody></table></div>
            `;
        $(table).dialog({
          autoOpen: true,
          height: 'auto',
          width: 600,
          modal: true,
          title: 'Plantilla a guardar',
          open:function(event) {
            iac.selectColorized( $(event.target));
          },
          close:function() {$(this).remove();},
          buttons: {
            "Guardar": function(e) {
                $(e.target).button('disable');
                var newName = me._strim($("#iaFillerNewName").val()),
                    newDesc = me._strim($("#iaFillerNewDescripcion").val()),
                    newColor = $("#iaFillerNewColor").val();
                if(newName == '') {
                    $("#iaFillerErrorMsg").html("Nombre es un dato requerido");
                    $(e.target).button('enable');
                    return;
                }
                if(me._nameExists(newName))
                    me._fillerSaveConfirm(newName, newDesc, id, newColor, $( this ));
                else
                    me._fillerSave(newName, newDesc, '', newColor, $( this ));
            },
            "Cancelar": function() { $( this ).dialog( "close" ); }
          },
        });
    },

    _fillerSaveConfirm:function(nombre, descr, id, color, saveAsDialog) {
        var me=this, msg = $("<div/>").text("Ya existe la plantilla " + nombre);
        msg.dialog({
            autoOpen:true,
            modal:true,
            title:'Reemplazar la Plantilla',
            close:function() {$(this).remove();},
            buttons: {
                "Guardar": function(e) {
                    $(e.target).button('disable');
                    me._fillerSave(nombre, descr, id, color,saveAsDialog);
                    $( this ).dialog( "close" );
                },
                "Cancelar": function() {
                    saveAsDialog.dialog( "close" );
                    $( this ).dialog( "close" );
                }
            }
        });
    },

    _fillerSave:function(nombre, descr, id, color, saveAsDialog) {
        var me = this, data={
            'nombre':nombre,
            'descr':descr,
            'color': color,
            'id':id,
            'vals':JSON.stringify(this.options.container.iaValuesIn({getUnchecked : true})),
            'action':'save',
            tag:this.options.tag
        };
        $.ajax({
            url:this.options.url,
            method:'POST',
            cache:false,
            dataType:'json',
            data:data,
        }).done(function(got, textStatus, jqXHR ) {
            if(typeof got.errorMessage != 'undefined') {
                me._alert_error(got.errorMessage);
                return;
            }
            me._fillerOptionAddReplace(got.nombre, got.iaFiller_id, got.descripcion, got.color, got.vals);
        }).fail(function(jqXHR, textStatus, errorThrown ) {
            me._alert_error(textStatus);
        }).always(function(data, textStatus, errorThrown ) {
            saveAsDialog.dialog( "close" );
        });
    },

    _fillerOptionAddReplace:function(nombre, iaFiller_id, descr, color, vals) {
        var data = {};
        data.descr = descr;
        data.color = color;
        data.normalized = this._strim(nombre).unaccentLower();
        data.vals = vals;
        var option = $("<option>").text(nombre).val(iaFiller_id).addClass(color).data(data).prop('selected',true),
            exists = this.element.find(".iaFiller_nombre").children().filter( function(){ return $(this).data('normalized') === data.normalized;});
        if(exists.length == 0)
            this.element.find(".iaFiller_nombre").append(option);
        else
            $(exists).replaceWith(option);
    },

///////////////////
// filler delete
///////////////////
    fillerDelConfirm:function() {
        var me=this,$option = this.element.find(".iaFiller_nombre").find(":selected");
        if($option.length == 0) {
            me._alert_error('Invalid option');
            return;
        }
        if($option.val()=='') {
            return;
        }
        var
            id = $option.val(),
            nombre = $option.text(),
            descr = $option.data('descr'),
            icono = '', //$("<span/>").addClass("ui-icon ui-icon-error"),
            confirme = $("<span/>").text("Confirme borrar la plantilla: " + nombre),
            descrita = $("<p/>").text(descr),
            msg = $("<div/>").append(icono).append(confirme).append(descrita);
        msg.dialog({
            autoOpen:true,
            modal:true,
            title:'Reemplazar la Plantilla',
            close:function() {$(this).remove();},
            buttons: {
                "Borrar": function(e) {
                    $(e.target).button('disable');
                    $.ajax({
                        url:me.options.url,
                        method:'POST',
                        cache:false,
                        dataType:'json',
                        data:{id:id, action:'del'},
                    }).done(function(data, textStatus, jqXHR ) {
                        if(data.ok == false) {
                            me._alert_error(data.errorMessage);
                            msg.dialog( "close" );
                            return;
                        }
                        $option.remove();
                        msg.dialog( "close" );
                    }).fail(function( jqXHR, textStatus, errorThrown ) {
                            me._alert_error(textStatus);
                            msg.dialog( "close" );
                    });
                },
                "Cancelar": function() { $( this ).dialog( "close" );}
            }
        });
    },

///////////////////
// helper functions
///////////////////
_strim:function(s) {
    if(typeof s !== 'string')
        return '';
    const regex = /\s{2,}/gu ;
    return s.trim().replace(regex, " ");
},

_nameExists:function (nombre) {
    if(typeof nombre !== 'string')
        return false;
    nombre = this._strim(nombre);
    var normalized =  nombre.unaccentLower();
    return this.element.find(".iaFiller_nombre").children().filter( function(){ return $(this).data('normalized') === normalized;}).length == 1;
},

_alert_error:function(title,msg) {
    $("<div/>").html("<div class='ui-state-error-text'><span class='ui-icon ui-icon-alert'></span>" + msg+"</div>").dialog({modal:true,classes: {"ui-dialog-content": "ui-state-error-text",},close:function() {$(this).remove();},title:title,buttons:[{text:"Ok",click:function(){$(this).dialog("close");} }]});
},
_alert_info:function(title,msg, modal) {
    $("<div/>").html("<span class='ui-icon ui-icon-alert'></span> " + msg).dialog({modal:modal || true,close:function() {$(this).remove();},title:title,buttons:[{text:"Ok",click:function(){$(this).dialog("close");} }]});
},
_cursorSetWait:function(){$(document.body).css({ 'cursor': 'work' }).css({ 'cursor': 'progress' });},
_cursorSetDefault:function(){$(document.body).css({ 'cursor': 'default' });},

});
})( jQuery );
