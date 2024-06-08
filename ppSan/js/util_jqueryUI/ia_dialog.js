//@version 1.0.2
/*
1.0.1 '&lt;' agrega espacio a '&lt; '
1.0.2 corrige confrim por confirm, added textWidth
*/
/**

ia.alert, ia.alertHighlight, ia.alertInfo, ia.alertWarn, ia.alertError:  promise.done clicked ok, promise.fail esc or closed
ia.confirm, ia.confirmDelete

ejemplo alert
    ia.alertError("el error fue","paso un <b>error</b>",true);
    var promise = ia.alertWarn("sigo", "ok <o> esc");
    promise.done(function(){
       console.log("iaAlert* dice: ","Clicked ok="+lalocal);
    });
    promise.fail(function() {
        console.log("iaAlert* dice: ","cancelo! tache o esc, mi var lalocal="+lalocal);
    });

example ia.confirm, ia.confirmDelete
    function confirmExample() {
        var lalocal="La local variable dice soy locatl";
        var promise = ia.confirm("quest its quest", "cera o sera");
        promise.done(function(){ // podria ser then
           console.log("confirmPromiseExample dice:","hizo click en ok mi var lalocal="+lalocal);
        });
        promise.fail(function() { // podria ser then
            console.log("confirmPromiseExample dice:","click en cancelo, tache o escape mi var lalocal="+lalocal);
        });
        console.log("confirmPromiseExample dice:","mientras le piensa sigo");
    }
*/

/* jshint strict: true */
/* jshint futurehostile: true */
/* jshint browser: true */
/* jshint devel: false */
/* jshint jquery: true */
/* jshint undef: true, unused: true */

var ia = ia || {};
ia.alert = function(message, title, html, messageIconSpan, addClass, buttonText, buttonIcon) {
    var defer = $.Deferred(), m=messageIconSpan==null ? '' : messageIconSpan+' ';
    $('<div/>')
        .html(m+(html===true ? message : message.replace(/</g,'&lt; '))).addClass(addClass==null ? '' : addClass)
        .dialog({
            autoOpen:true,
            resizable:true,
            draggable:true,
            modal:true,
            closeOnEscape:true,
            width:'auto',
            height:'auto',
            title: title==null ? 'Aviso' : title,
            buttons: [
                {text:buttonText==null ? 'Ok' : buttonText,icon:buttonIcon==null ? 'ui-icon-check' : buttonIcon,
                 click:function(e){
                    e.target.disabled = true;
                    if(!defer.isResolved && !defer.isRejected)
                        defer.resolve("true");
                    $(this).dialog("close");
                 }
                },
            ],
            close: function () {
                if(!defer.isResolved && !defer.isRejected)
                    defer.reject();
                $(this).dialog('destroy').remove();
            }
        });
    return defer.promise();
};
ia.alertHighlight = function(message, title, html, messageIconSpan, addClass, buttonText, buttonIcon) {
    return ia.alert(message, title, html, messageIconSpan==null ? '<span class="ui-icon ui-icon-info"></span>':messageIconSpan, addClass==null ? 'ui-state-highlight' : addClass, buttonText, buttonIcon);
};
ia.alertInfo = function(message, title, html, messageIconSpan, addClass, buttonText, buttonIcon) {
    return ia.alert(message, title, html, messageIconSpan==null ? '<span class="ui-icon ui-icon-info"></span>':messageIconSpan, addClass, buttonText, buttonIcon);
};
ia.alertWarn = function(message, title, html, messageIconSpan, addClass, buttonText, buttonIcon) {
    return ia.alert(message, title, html, messageIconSpan==null ? '<span class="ui-icon ui-icon-alert"></span>':messageIconSpan, addClass, buttonText, buttonIcon);
};
ia.alertError = function(message, title, html, messageIconSpan, addClass, buttonText, buttonIcon) {
    return ia.alert(message, title, html, messageIconSpan==null ? '<span class="ui-icon ui-icon-alert"></span>':messageIconSpan,addClass==null ? 'ui-state-error-text' : addClass, buttonText, buttonIcon);
};

ia.form = function(message, title, ok_label = 'Si', cancel_label = 'No') {
    var defer = $.Deferred();
    $('<div/>')
        .html(message)
        .dialog({
            autoOpen:true,
            resizable:true,
            draggable:true,
            modal:true,
            closeOnEscape:true,
            width:'auto',
            height:'auto',
            title: title==null ? 'Por favor, confirma:' : title,
            buttons: [
                {text:ok_label,icon:'ui-icon-check',
                    click:function(e){
                        let valid = true;
                        let values = {};
                        $(this).find("INPUT,SELECT,TEXTAREA").each( function() {
                            let $el = $(this);
                            if(this.checkValidity()) {
                                this.setCustomValidity("Invalid.");
                                $el.removeClass('inputInvalid');
                            } else {
                                valid = false;
                                this.setCustomValidity("");
                                $el.addClass('inputInvalid');
                            }
                            if($el.attr('type') === 'radio')
                                values[ this.name ] = $(`input[name='${this.name}']:checked`).val();
                            else
                                values[ this.name ] = $el.val();
                        });
                        if(!valid)
                            return;
                        e.target.disabled = true;
                        if(!defer.isResolved && !defer.isRejected)
                            defer.resolve(values);
                        $(this).dialog("close");
                    }
                },
                {text:cancel_label,icon:'ui-icon-cancel',
                    click:function(e){
                        e.target.disabled = true;
                        if(!defer.isResolved && !defer.isRejected)
                            defer.reject();
                        $(this).dialog("close");
                    }
                }
            ],
            open:function() {
                $(this).find('INPUT').on('change', function(event){
                    if(event.target.checkValidity()) {
                        event.target.setCustomValidity("");
                        $(event.target).removeClass('inputInvalid');
                    } else {
                        event.target.setCustomValidity("Invalid.");
                        $(event.target).addClass('inputInvalid');
                    }
                });
            },
            close: function () {
                if(!defer.isResolved && !defer.isRejected)
                    defer.reject();
                $(this).dialog('destroy').remove();
            }
        });
    return defer.promise();
};

ia.confirm = function(message, title, html) {
    var defer = $.Deferred();
    $('<div/>')
        .html(html===true ? message : message.replace(/</g,'&lt; '))
        .dialog({
            autoOpen:true,
            resizable:true,
            draggable:true,
            modal:true,
            closeOnEscape:true,
            width:'auto',
            height:'auto',
            title: title==null ? 'Por favor, confirma:' : title,
            buttons: [
                {text:'Si',icon:'ui-icon-check',
                 click:function(e){
                    e.target.disabled = true;
                    if(!defer.isResolved && !defer.isRejected)
                        defer.resolve("true");
                    $(this).dialog("close");
                 }
                },
                {text:'No',icon:'ui-icon-cancel',
                 click:function(e){
                    e.target.disabled = true;
                    if(!defer.isResolved && !defer.isRejected)
                        defer.reject();
                    $(this).dialog("close");
                 }
                }
            ],
            close: function () {
                if(!defer.isResolved && !defer.isRejected)
                    defer.reject();
                $(this).dialog('destroy').remove();
            }
        });
    return defer.promise();
};

ia.confirmDelete = function(message,title,html){
    var defer = $.Deferred();
    $("<div />").html('<span class="ui-icon ui-icon-trash"></span> ' + (html===true ? message : message.replace(/</g,'&lt; '))).addClass('ui-state-error-text')
    .dialog({
        autoOpen:true,
        resizable:true,
        draggable:true,
        modal:true,
        closeOnEscape:true,
        width:'auto',
        height:'auto',
        title: title==null ? 'Confirme Eliminar:' : title,
        buttons: [
            {text: "Eliminar",icon:"ui-icon-trash",'class':"ui-state-error-text",
                click: function(e) {
                    e.target.disabled = true;
                    if(!defer.isResolved && !defer.isRejected)
                        defer.resolve("true");
                    $(this).dialog("close");
                }
            },
            {text:'Cancelar',icon:'ui-icon-cancel',
                click:function(e){
                    e.target.disabled = true;
                    if(!defer.isResolved && !defer.isRejected)
                        defer.reject();
                    $(this).dialog("close");
                }
            }
        ],
        close: function () {
            if(!defer.isResolved && !defer.isRejected)
                defer.reject();
            $(this).dialog('destroy').remove();
        }
    });
    return defer.promise();
};

ia.tagProtect = function(html) {
    var tags=['script','object','embed','iframe','frame','applet','button','input','form','base','param']   ;
    for(var i=0,tagsLen=tags.length; i<tagsLen; i++ ) {
        var regExp = new RegExp( '<(' + tags[i] + ')|<\/(' + tags[i] +')', 'gimu');
        html = html.replace(regExp, ' $1 /$2');
    }
    return html;
}

ia.textWidth = function(text,addClass){
     var calc = $('<span style="display:none">' + text + '</span>');
     if(typeof addClass!=='undefined')
        calc.addClass(addClass);
     else
        calc.addClass('ui-dialog-title-bar ui-widget-header ui-dialog-title'); // add 46 for dialog width
     $('body').append(calc);
     var width = calc.width();
     calc.remove();
     return width;
}
