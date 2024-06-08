/**
 * jqGridFilterBuilder: Helper for filling jqGrid.postData.filter
 *
 * @version 1.0.0 2021-05-18
 */

/** @noinspection ES6ConvertVarToLetConst */
/* jshint undef: true, unused: true, latedef:true,futurehostile: true */
/* jshint browser: true, jquery:true */
/* jshint esversion:3 */
/*jshint globalstrict: true*/

/**
 * Helper for filling jqGrid.postData.filter
 *
 * @example
 *
 *   var f = jqGridFilterBuilder; // para escribir menos
 *
 *   // ['col', 'eq', 1] es igual que {field:'col', 'op':'eq', data:1}
 *  let aOr = f.OR({field:'col', 'op':'eq', data:1}, ['fieldName', 'eq', 'value']);  // (col=1 OR fieldName='value')
 *  let aAnd = f.AND(['colA', 'eq', 1], ['colB', 'eq',2]); // (colA = 1 AND colB = 2)
 *   let bOR_AND = f.OR(['col', 'eq', 1], f.AND(['col2', 'ge', 2], ['col3', '=', 3]) ); // col=1 OR (col2 >= 2 AND col3=3)
 *
 *   let cXOR_AND = f.XOR(['col', 'eq', 1], f.AND(['col2', '=', 2], ['col3', '=', 3]) ); // col=1 XOR (col2=2 AND col3=3)
 *
 *  let dNot = f.NOT( f.XOR(['col2', 'ge', 2], ['col3', 'eq', 3]) ); //  NOT( `col2` >='2' XOR `col3` ='3')
 *
 *  let or2Filters = f.filterOrFilter(aOr, aAnd) // ((col=1 OR fieldName='value') OR (colA = 1 AND colB = 2) )
 *
 *  let and2Filters = f.filterAndFilter(aOr, aAnd) // ((col=1 OR fieldName='value') AND (colA = 1 AND colB = 2) )
 *
 * @type {{OP: (function(*=): {groups: [], rules: [], groupOp: string}), NOT: jqGridFilterBuilder.NOT, OR: jqGridFilterBuilder.OR, AND: jqGridFilterBuilder.AND}}
 *
 */
const jqGridFilterBuilder= {
    /**
     *
     * @param groupOp string logical operator: AND, OR, NOT,...
     * @param ...arrays ['col', 'eq', 2],{field:'col', 'op':'eq', data:1} o jqGridFilterBuilder.AND(), ...
     * @returns {{groups: [], rules: [], groupOp: string}}
     */
    OP: function(groupOp) {
        var rules= [], groups= [];
        for(var i= 1, len= arguments.length; i < len; ++i ) {
            var a = arguments[i];
            if(a.hasOwnProperty('groupOp')) {
                groups.push(a);
                continue;
            }
            if(Array.isArray(a)) {
                rules.push({field:a[0], op:a[1], data:a[2]});
                continue;
            }
            rules.push(a);
        }
        return {groupOp:groupOp, rules: rules, groups:groups};
    },

    /**
     * Anded clause
     *
     * @param ...arrays ['col', 'eq', 2],{field:'col', 'op':'eq', data:1} o jqGridFilterBuilder.OR(), ...
     * @returns {{groupOp:'AND', rules: [], groups:[]}};
     */
    AND: function() {
        if(arguments.length === 0 )
            return {groupOp:'AND', rules: [], groups: []};
        var arg = Array.from(arguments);
        arg.unshift('AND');
        return this.OP.apply(null, arg);
    },

    /**
     * Ored clause
     *
     * @param ...arrays ['col', 'eq', 2],{field:'col', 'op':'eq', data:1} o jqGridFilterBuilder.AND(), ...
     * @returns {{groupOp:'OR', rules: [], groups:[]}};
     */
    OR: function() {
        if(arguments.length === 0 )
            return {groupOp:'OR', rules: [], groups:[]};
        var arg = Array.from(arguments);
        arg.unshift('OR');
        return this.OP.apply(null, arg);
    },

    /**
     * XOred clause
     *
     * @param ...arrays ['col', 'eq', 2],{field:'col', 'op':'eq', data:1} o jqGridFilterBuilder.AND(), ...
     * @returns {{groupOp:'OR', rules: [], groups:[]}};
     */
    XOR: function() {
        if(arguments.length === 0 )
            return {groupOp:'XOR', rules: [], groups:[]};
        var arg = Array.from(arguments);
        arg.unshift('XOR');
        return this.OP.apply(null, arg);
    },

    /**
     * Not clause
     *
     * @param ...arrays ['col', 'eq', 2] o {field:'col', 'op':'eq', data:1} o jqGridFilterBuilder.AND(), ...
     * @returns {{groupOp:'NOT', rules: [], groups: []}};
     */
    NOT: function() {
        if(arguments.length === 0 )
            return {groupOp:'NOT', rules: [], groups:[]};
        var arg = Array.from(arguments);
        arg.unshift('NOT');
        return this.OP.apply(null, arg);
    },

    filterOrFilter:function(filterLeft, filterRight) {return {groupOp:'OR', rules:[], groups:[filterLeft, filterRight]};},

    filterAndFilter:function(filterLeft, filterRight) {return {groupOp:'AND', rules:[], groups:[filterLeft, filterRight]};},
};


Object.freeze(jqGridFilterBuilder); // protect from hacking
