/**
 * Funciones de conjuntos para arrays
 *
 * @type {{array_symmetric_diff: (function(*, *): *), array_diff: (function(*, *): *), array_intersection: (function(*, *): *), object_intersection: (function(*, *, *): *), object_diff: (function(*, *, *): *)}}
 */
const conjuntos = {
    // has element, object symetric diff, union all and union distinct, for objects same but with keys insetad of value by key

    /**
     * intersection ∩, elements in both arrays
     *
     * @param arr1 array
     * @param arr2 array
     * @returns  array elements in both arrays
     */
    array_intersection: function(arr1, arr2) {return arr1.filter(x => arr2.includes(x)); },

    /**
     * symmetric difference Δ (disjunctive union ⊖): elements not in intersection
     *
     * @param arr1 array
     * @param arr2 array
     * @returns array
     */
    array_symmetric_diff: function(arr1, arr2) {
        return arr1.filter(x => !arr2.includes(x))
            .concat(arr2.filter(x => !arr1.includes(x)));
    },

    /**
     *  sets A - B, elements in the first set (elements) not in the second set ( param: notIn)
     *
     * @param elements
     * @param notIn
     * @returns array
     */
    array_diff: function(elements, notIn) {return elements.filter(x => !notIn.includes(x)); },

    /**
     * intersection ∩, list whose's keyName's value are the same
     *
     * @param keyName
     * @param list1
     * @param list2
     * @returns list whose's keyName's value are the same
     */
    object_intersection: function(keyName, list1, list2) {
        return list1.filter(e => {
            return list2.some(item => item[keyName] === e[keyName]);
        });
    },

    object_diff: function(keyName, elements, notIn) {
        return elements.filter(e => {
            return !notIn.some(item => item[keyName] === e[keyName]);
        });
    },


}
Object.freeze(conjuntos);