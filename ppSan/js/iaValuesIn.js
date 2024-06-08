/**
 Usage:
        jQuery("#formId")iaFormData(); // returns {name:value,...} for #formId using formData thus must be a form

 Usage: jQuery(selector).iaValuesIn(); // returns {name:value} from selector (may be div)
        jQuery(selector).iaValuesIn({getUnchecked : true}); getUnchecked false: [default] normal unchecked radio/checkboxes not reported, true report unchecked radio and checkboxes
        jQuery(selector).iaValuesIn({
            selector: null,          // null get inputs, else ['input_name',[nonInput_id],...] or {input_name:{}, nonInput_id:{}, ...}
            selectorNonInput: true,  // true if name dosen't match  input (select,textarea) find in non input dom by id, false ignore
            selectorProp : "html",  // on non input dom get: 'html'|'text'|data-xyz. html, text or property name for $(...).prop(); ie data-sendthis, data, text, html, class
        });

        Note collects disabled and readonly flieds

 Options {
    getUnchecked : false,   // false normal, true report unchecked radio and checkboxes
    selector: null,           // null get inputs, else ['input_name',[nonInput_id],...]
    selectorNonInput: false,  // true if name dosen't match  input (select,textarea) find in non input dom by id, false ignore
    selectorProp : "html",  // on non input dom get: 'html'|'text'|data-xyz. html, text or property name for $(...).prop();
 }

 Set defaults:
    jQuery.iaValuesInDefaults({
        selector: null,    // null get inputs, else ['input_name',[nonInput_id],...]
        selectorNonInput:  // false,  true if name dosen't match  input (select,textarea) find in non input dom by id, false ignore
        selectorProp :     // "html",  on non input dom get: 'html'|'text'|data-xyz|data html, text or property name for $(...).prop();
        excludeName:{nameToExclude:0}
    });

@version 1.0.1
    added licence
@version 1.0.2
    exclude value if hasClass('iaFillerExclude') or is in excludeName={nameToExclude:0}
*/
/**
 * @author Informática Asocaida SA de CV
 * @author Raul Jose Santos Bernard
 * @version 1.0.2
 * @copyright 2017
 * @license MIT
 */
 /* jshint strict: true */
/* jshint futurehostile: true */
/* jshint browser: true */
/* jshint devel: true */
/* jshint jquery: true */
/* jshint undef: true, unused: true */
;(function(defaults, $) {
// plugin template from https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.best.options.plugin-boilerplate.js
   'use strict';

    $.extend({
    	// Function to change the default properties of the plugin
    	// Usage: jQuery.iaValuesInDefaults({property:'Custom value'});
    	iaValuesInDefaults : function(options) {
    		return $.extend(defaults, options);
    	}
    }).fn.extend({
        // Each plugin's 'main/init function'

        iaFormData : function() {
            if(typeof formData === 'undefined')
                return $(this).iaValuesIn();
            var result = {};
            $(this).each(function() {
                var formData = new FormData(this);
                for(var entry of formData.entries())
                    result[entry[0]] = entry[1];
            });
            //@TODO add exclude if hasClass('iaFillerExclude') or is in excludeName={nameToExclude:0}
            return result;
        },

        iaValuesIn : function(options) {

            options = $.extend({}, defaults, options);

            var data={}, asArray={};

            $(this).each(function() {
                $(this).find('input').filter(":enabled").each(function(){ //VCA 11-FEB 2019
                    var name = $(this).attr('name');

                    if(typeof name === 'undefined' || name === '' || $(this).hasClass("iaFillerExclude") || typeof options.excludeName[name] !== 'undefined')
                        ;
                    else if(typeof asArray[name] !== 'undefined')
                        asArray[name] = 0;
                    else if(typeof asArray[name] !== 'undefined')
                        asArray[name]++;
                });
            });

            if(options.selector === null)
                $(this).each(function() { getIt($(this)); });
            else
                $(this).each(function() {getselector($(this), options.selector); });

            return data;

            function getIt($container) {
                $container.find('input').filter(":enabled").each(function(){ storeValue($(this), getValue($(this))); });
                $container.find('select').filter(":enabled").each(function(){ storeValue($(this), getValue($(this))); });
                $container.find('textarea').filter(":enabled").each(function(){ storeValue($(this), getValue($(this))); });
                return data;
            }

            function getselector($container, selector) {
                if(Array.isArray(selector)) {
                    for(var i=0, selectorLen=selector.length; i<selectorLen; i++) {
                        getByName($container, selector[i]);
                    }
                    return data;
                }
                for(var c in selector) {
                    if(selector.hasOwnProperty(c)) {
                        getByName($container, c);
                    }
                }
                return data;
            }

            function getValue($el) {
                if($el.attr('type') === 'checkbox' || $el.attr('type') === 'radio') {
                    return $el.prop('checked') ? $el.val() : null;
                }
                if(typeof $el.data('selectize')!=='undefined') {
                    return $el.selectize()[0].selectize.getValue();
                }
                return $el.val();
            }

            function storeValue($el, val, name) {
                if(typeof name === 'undefined')
                    name = $el.attr('name');
                if(typeof name !== 'string' || name === null || name === '') {
                    return;
                }
                var originalName = name;
                name = name.replace(/\[\]/i, '');


                var ist_typeof = typeof data[name],
                    ist_array = (typeof asArray[name] !== 'undefined' && asArray[name] > 0) ||
                                (typeof data[name] === 'object' && data[name] !== null) ||
                                (originalName.indexOf("[") > -1 && originalName.indexOf("]") > -1)
                ;

                if(!options.getUnchecked && val === null && !ist_array && ($el.attr('type') === 'checkbox') ) {
                    return;
                }

                if($el.attr('type') === 'radio') {
                    if(val !== null)
                        data[name] = val;
                    else if(options.getUnchecked && typeof data[name] === 'undefined' )
                        data[name] = val;
                    return;
                }

                if(ist_typeof === 'undefined') {
                    if(ist_array) {
                        data[name] = Array.isArray(val) ? val : [val];
                        return;
                    }
                    data[name] = val;
                    return;
                }

                if(ist_typeof === 'object' && data[name] !== null) {
                    if(Array.isArray(val)) {
                        data[name] = val.concat(data[name]);
                        return;
                    }
                    data[name][data[name].length] = val;
                    return;
                }
                if(!Array.isArray(val)) {
                    val = [val];
                }
                data[name] = val.concat(data[name]);
            }

            function getByName($container, name) {
                var inputType = ['input', 'select', 'textarea'], $el;
                for(var i=0, iLen=inputType.length; i < iLen; ++i) {
                    $el = $container.find($('input[name="'+name+'"]'));
                    if($el.length === 1) {
                        storeValue($el, getValue($el));
                        return;
                    } else if($el.length > 1) {
                        $el.each(function(){ storeValue($(this), getValue($(this))); });
                        return;
                    }
                }

                if(options.selectorNonInput) {
                    var isData = options.selectorProp.toLowerCase().startsWith('data-') || options.selectorProp.toLowerCase() === 'data',
                        dataName=isData ? options.selectorProp.substring( options.selectorProp.indexOf('-') + 1 ) : '';

                    $el = $container.find(name);
                    if($el.length === 1) {
                        if(options.selectorProp === 'html')
                            storeValue($el, $el.html(), name);
                        else if(options.selectorProp === 'text')
                            storeValue($el, $el.text(), name);
                        else if(isData){
                            if(options.selectorProp.toLowerCase() === 'data')
                                storeValue($el, JSON.parse(JSON.stringify($el.data())), name); // remove functions from data object
                            else
                                storeValue($el, JSON.parse(JSON.stringify($el.data(dataName))), name);

                        } else {
                            storeValue($el, $el.prop(options.selectorProp), name);
                        }
                        return;
                    }

                    $el.each(function(){
                        var $el = $(this);
                        if(options.selectorProp === 'html')
                            storeValue($el, $el.html(), name);
                        else if(options.selectorProp === 'text')
                            storeValue($el, $el.text(), name);
                        else if(isData){
                            if(options.selectorProp.toLowerCase() === 'data')
                                storeValue($el, JSON.parse(JSON.stringify($el.data())), name);
                            else
                                storeValue($el, JSON.parse(JSON.stringify($el.data(dataName))), name);

                        } else {
                            storeValue($el, $el.prop(options.selectorProp), name);
                        }
                    });
                }
            }
		}
	});
})({
    // iaValuesIn defaults
    	getUnchecked : false,   // false normal, true report unchecked radio and checkboxes

        selector: null,            // null get inputs, else ['input_name',[nonInput_id],...]
        selectorNonInput: false,    // true if name dosen't match  input (select,textarea) find in non input dom by id, false ignore
        selectorProp : "html",     // on non input dom get: 'html'|'text'|data-xyz. html, text or property name for $(...).prop();
        excludeName : {},
}, jQuery);

