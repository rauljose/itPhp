// @version 1.0.1
/*

var frma = $("FORM");var bals = frma.serializeObject();
$.populateForm(frma, bals);$(".multiselect").each(function(){$(this).multiselect('refresh')});

based on https://github.com/saurshaz/jquery.jquery-json2form.js
*/
debug=true;
(function (factory) {
    if (typeof define === 'function' && define.amd)
        define(['jquery'], factory);
    else
        factory(jQuery);
}(function ($) {

    var fillForm = function(frm,name,val) {

        var $el = frm.find($('input[name="'+name+'"]'));
        if($el.length === 1) {
            fillInputValue($el,val);
            return;
        } else if($el.length > 1) {
            $el.each(function(i){fillInputValue($(this),val,i);});
            return;
        }
        var $el = frm.find($('input[name="'+name+'[]"]'));
        if($el.length === 1) {
            fillInputValue($el,val);
            return;
        } else if($el.length > 1) {
            $el.each(function(i){fillInputValue($(this),val,i);});
            return;
        }
        var $elSelect = frm.find($('SELECT[name="' + name + '"]'));
        if($elSelect.length === 1) {
            fillSelectValue($elSelect,val);
            return;
        } else if($elSelect.length > 1) {
            $elSelect.each(function(){fillSelectValue($(this),val);});
            return;
        }
        var $elSelect = frm.find($('SELECT[name="' + name + '[]"]'));
        if($elSelect.length === 1) {
            fillSelectValue($elSelect,val);
            return;
        } else if($elSelect.length > 1) {
            $elSelect.each(function(){fillSelectValue($(this),val);});
            return;
        }
        var $elTextarea = frm.find($('textarea[name="' + name + '"]'));
        if($elTextarea.length === 1) {
            fillTextAreaValue($elTextarea,val);
            return;
        } else if($elTextarea.length > 1) {
            $elTextarea.each(function(i){fillTextAreaValue($(this),val,i);});
            return;
        }
        var $elTextarea = frm.find($('textarea[name="' + name + '[]"]'));
        if($elTextarea.length === 1) {
            fillTextAreaValue($elTextarea,val);
            return;
        } else if($elTextarea.length > 1) {
            $elTextarea.each(function(i){fillTextAreaValue($(this),val,i);});
            return;
        }
    };

    var fillTextAreaValue = function($elTextarea,val,i) {
        if(val !== undefined && val instanceof Array) {
            if(i<val.length)
                $elTextarea.val(val[i]);
        } else
            $elTextarea.val(val);
    };

    var fillInputValue = function($el,val,i) {
        var type = $el.attr('type');
        switch(type) {
            case 'checkbox':
                if(val !== undefined && val instanceof Array) {
                    var l=val.length,i,elval=$el.val();
                    for(i=0;i<l;i++)
                        if(val[i] == elval) {
                            if(!$el.prop('checked'))
                                $el.trigger('click');
                            break;
                        }
                } else if(val == $el.val())
                    $el.trigger('click');
                break;
            case 'radio':
                if($el.val() == val) {
                    $el.trigger('click');
                }
                break;
            default:
                if(val !== undefined && val instanceof Array) {
                    if(i<val.length)
                        $el.val(val[i]);
                } else
                    $el.val(val);
                break;
        }
    };

    var fillSelectValue = function( $elSelect,val) {
        var typeOfSelect = $elSelect.attr('multiple');
        if((typeOfSelect) && (typeOfSelect === 'multiple')) {
            if(val !== undefined && val instanceof Array) {
                $elSelect.val(val);
            } else {
                $elSelect.find("option[value='" + val + "']").prop('selected', true);
            }
        } else
            $elSelect.val(val);
    };

    var handleVerboseFormJson = function (frm, data) {
        $.each(data, function (id, node) {
            fillForm(frm, node.name, node.value);
        });
    };

    var handleConciseFormJson = function (frm, data) {
       $.each(data,function (name, value) {
            fillForm(frm, name, value);
        });
    };

    var config = $.populateForm = function (frm, data, options) {
        if(data !== undefined && frm !== undefined) {
            options = $.extend({}, config.defaults, options);
            if(options.format && options.format === 'verbose') {
                // if the format is [{name:<name_val1>,value:,<val_value1>},{name:<name_val2>,value:,<val_value2>}]
                handleVerboseFormJson(frm, data);
            } else if(options.format && options.format === 'provided') {
                // support any other JSON format TODO :: if needed -- consider the option -- 'format'
            } else {
                // if the format is [{<name_val1>:<val_value1>,<name_val2>:<val_value2>}] :: DEFAULT choice
                handleConciseFormJson(frm, data);
            }
        }
    };
}));