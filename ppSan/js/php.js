
const php = {};

php.empty = function(any) {
    if(typeof any === 'undefined' || any == null || any == '' || any == 0 || (Array.isArray(any) && any.length === 0))
        return true;
    if(Symbol.iterator in Object(any)) {
        for(const key of any)
            return true;
        return false;
    }
    return typeof any === 'object' && Object.keys(any).length === 0;
};

php.array_column = function(arr, col, index) {
    if(!Array.isArray(arr) || arr.length === 0)
        return [];
    const sinIndex = typeof index === 'undefined';
    let ret = sinIndex ? [] : {};
    for(const a of arr) {
        const aObject = Object(a);
        if(!(col in aObject))
            continue;
        if(sinIndex)
            ret.push(a.col);
        else if(index in aObject)
            ret[a.index] = a.col;
    }
    return ret;
};

php.ucwords = function(phrase) {
    return phrase.toLowerCase().replace(
        /(^([\p{L}\p{M}]))|([\s-][\p{L}\p{M}])/gmiu,
        function(s){
            switch(s) {
                case ' a':
                case ' e':
                case ' o':
                case ' ó':
                case ' u':
                case ' y':
                case ' de':
                case ' del':
                case ' en':
                case ' el':
                case ' la':
                case ' los':
                case ' las':
                    return s;
                case 'usd':
                case ' usd':
                case 'usa':
                case ' usa':
                case 'ue':
                case ' ue':
                case 'mx':
                case ' mx':
                case 'mex':
                case ' mex':
                case 'mn':
                case ' mn':
                case 'mxp':
                case ' mxp':
                case ' imss':
                case 'imss':
                case ' isr':
                case 'isr':
                case ' sat':
                case 'sat':
                case ' shcp':
                case 'shcp':
                    return s.toUpperCase();
                default:
                    return s.toUpperCase();
            }
        }
    );
};

php.print_r = function (obj, returnTheValue, consoleLogTag) {
    if(returnTheValue)
        return dump(obj);
    console.log(consoleLogTag || '', obj);
    function dump(obj) {
        if (obj === null)
            return 'null';
        if (typeof obj !== 'object')
            return obj;
        let tab = arguments[1] || '';
        if (Array.isArray(obj)) {
            let outAr = [];
            for (let e of obj)
                outAr.push(dump(e, typeof e === 'object' ? "\t" + tab : ""));
            return "[" + outAr.join(", ") + "]";
        }
        if (obj instanceof Date)
            return `new Date(${obj.getTime()})`;
        let tab2 = tab + (tab === '' ? '\t' : tab);
        let out = [];
        for (let e in obj)
            if (obj.hasOwnProperty(e))
                out.push(e + ": " + dump(obj[e], tab2));
        return "{\n" + tab2 + out.join(",\n" + tab2) + "\n" + tab + "}\n";
    }
}

Object.freeze(php);
