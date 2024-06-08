/* jshint strict: true */
/* jshint futurehostile: true */
/* jshint browser: true */
/* jshint devel: true */
/* jshint jquery: true */
/* jshint undef: true, unused: true */

///var $ = $ || {};
///var $.fn = $.fn || {};

$(function(){
    $.fn.ia = $.fn.ia || {};
    $.fn.muestra = function() {
        var args = arguments, wa=args.length>0;
        return this.each(function() {
            var e = $(this);
            if(e.css('visibility')==='hidden')
                e.css({'visibility':'visible'}).data('iavisibility',1);
            if(e.css('display')==='none')
                if(wa)
                    e.show(args);
                else
                    e.show();
        });
    };

    $.fn.oculta = function() {
        var args = arguments, wa=args.length>0;
        return this.each(function() {
            var e = $(this);
            if(e.data('iavisibility')=='1')
                e.css({'visibility':'hidden'});
            else if(wa)
                e.hide(arguments);
            else
                e.hide();
        });
    };

    $.fn.muestraConLabel = function() {
        var args = arguments, wa=args.length>0;
        return this.each(function() {
            $(this).muestra();
            var lbl = $("label[for='"+this.id+"']");
            if(lbl.length>0)
                lbl.muestra();
        });
    };
    $.fn.ocultaConLabel = function() {
        var args = arguments, wa=args.length>0;
        return this.each(function() {
            $(this).oculta();
            var lbl = $("label[for='"+this.id+"']");
            if(lbl.length>0)
                lbl.oculta();
        });
    };

    $.fn.isHidden = function () {
        if(this.length === 1) {
            var el = $(this);
            return el.css('display')==='none' || el.css('visibility')==='hidden';
        }
        var r={};
        this.each(function() {
            var el = $(this);
            if(typeof el.type !== 'undefined' && el.type==='hidden')
                r = false;
            r = el.css('display')==='none' || el.css('visibility')==='hidden';
        });
        return r;
    };

    $.fn.valVisible = function(onHiddenReturn) {
        // only visible, onhidden dflt, unchecked checkedbox,
        // $("#valeIncome").find("INPUT").valVisible()
        var e = $(this);
        if(e.length === 0)
            return;
        var ret=[];
        this.each(function() {
            var e = $(this), name=e.attr('name'), r={};
            if(typeof name==='string' && name.length>0) {
                if(e.isHidden())
                    r[this.id] = typeof onHiddenReturn === 'undefined' ? 0.00 : onHiddenReturn;
                else
                    r[this.id] = myval(e);
                ret[ret.length] = r;
            }
        });
        return ret;
        function myval(el) {
            if(el.attr('type')==='checkbox' || el.attr('type')==='radio')
                if(el.prop('checked'))
                    return el.val();
                else
                    return;
            if(typeof el.data('autoNumeric') !== 'undefined')
                return el.autoNumeric('get');
            return el.val();
        }
    };

});
