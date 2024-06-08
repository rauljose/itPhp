// noinspection JSUnusedGlobalSymbols

/**
 * various utility/helpful functions for js
 */

/**
 * Trim, all space  charaters to space and force single space,
 *
 * @param str
 * @returns {string}
 */
function strim(str) {
    if(typeof str === 'undefined' || str === null)
        return '';
    return str.toString().trim().replaceAll(/\s\s+/gm,' ');
}

/**
 * array2object([{id:'Id1',p:'prop'},{id:'Id1',p:'prop'}], 'id') => {{Id1:{id:'Id1',p:'prop'}, Id2:{id:'Id2',p:'prop'}}}
 *
 * @see php.array_column in  php.js
 *
 * @param arr array
 * @param id string
 * @returns {{}}
 *
 *
 */
function array2object(arr, id) {
    let obj = {}
    for(const a of arr)
        if(a.hasOwnProperty(id))
            obj[id] = a;
    return obj;
}

/**
 * obj2Array({Id1:{id:'Id1',p:'prop'}, Id2:{id:'Id2',p:'prop'}}, 'key') =>
 *   [{id:'Id1',p:'prop',key:'Id1'}, {id:'Id2',p:'prop', key:'Id2'}]
 *   requires structuredClone polyfill https://www.npmjs.com/package/@ungap/structured-clone
 *
 * @param obj Object
 * @param addIdProperty string
 * @returns {*[]}
 */
function obj2Array(obj, addIdProperty) {
    let add = typeof addIdProperty !== 'undefined';
    let ret = [];
    for(const key in obj)
        if (obj.hasOwnProperty(key)) {
            let cloned = structuredClone(obj[key]);
            if(add)
                cloned[addIdProperty] = key;
            ret.push(cloned);
        }
    return ret;
}

function print_r(obj, returnTheValue, consoleLogTag) {
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

/**
 *
 *
 * @param obj object
 * @param key string
 * @returns {undefined|*}
 */
function objGetKeyInsensitive(obj, key) {
    if(obj.hasOwnProperty(key))
        return obj[key];
    const kFind = key.toLowerCase();
    if(obj.hasOwnProperty(kFind))
        return obj[kFind];
    for(let k in obj)
        if(obj.hasOwnProperty(k) && kFind === k.toLowerCase())
            return obj[k];
    return undefined;
}

/**
 * Is it valid to use for(let a of any), ie it is iterable
 *
 * @param any
 * @returns {boolean}
 */
function isIterable(any) {
    if (any === null || any === undefined)
        return false
    return typeof any[Symbol.iterator] === 'function'
}

/**
 *
 * @param data object
 *  data = {
 *      "$":{"#id1":{"html":"new content","addClass":"class1 class2"}, ".hide":{"hide":null} },
 *          // $("#id1").html("new content").addClass("class1 class2");
 *          // $(".hide").each(function(){$(this).hide(null)});
 *       "byId":{"id2":{"val":3}}
 *          // document.getElementById("id2").val=3;
 *  }
 */
function setter(data) {
    const jQ = data.hasOwnProperty('$') ? data.$ :
        data.hasOwnProperty('jQuery') ? data.jQuery :
            data.hasOwnProperty('jquery') ? data.jquery : null;
    if(jQ !== null)
        for(let selector in jQ)
            if(jQ.hasOwnProperty(selector)) {
                const $el = $(selector);
                if($el.length) {
                    const one = $el.length === 1;
                    const vals = jQ[selector];
                    for (let func in vals)
                        if (vals.hasOwnProperty(func))
                            try {
                                if (one)
                                    $el[func](vals[func]);
                                else
                                    $el.each(function () {
                                        $(this)[func](vals[func]);
                                    });
                            } catch(ignore) {console.log(`setter $("${selector}").${func}(${vals[func]})`, ignore);}
                } else
                    console.log(`setter $("${selector}")`, "NOT FOUND");
            }
    if(data.hasOwnProperty('byId')) {
        let keys = data.byId;
        for(let el in keys)
            if(keys.hasOwnProperty(el)) {
                let element = document.getElementById(el);
                if(element) {
                    let properties = keys[el];
                    for(let p in properties)
                        if(properties.hasOwnProperty(p))
                            element[p] = properties[p];
                } else
                    console.log(`document.getElementById("${selector}")`, "NOT FOUND");
            }
    }
}

function is_container_number(container_number) {
    container_number = container_number.toString().trim();
    const regex = /[A-Z]{4}[0-9]{7}/i;
    if(!regex.test(container_number))
        return false;
    let code = {
        0: 0, 1: 1, 2: 2, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9,
        A: 10, B: 12, C: 13, D: 14, E: 15, F: 16, G: 17, H: 18, I: 19, J: 20, K: 21, L: 23, M: 24,
        N: 25, O: 26, P: 27, Q: 28, R: 29, S: 30, T: 31, U: 32, V: 34, W: 35, X: 36, Y: 37, Z: 38
    }
    let sum = 0, m = 1, len = container_number.length -1;
    for(let i=0; i < len; i++) {
        sum += code[container_number[i]] * m;
        //console.log(container_number[i], `c=${code[container_number[i]]} * ${m} = ${code[container_number[i]]* m}  sum=${sum}`);
        m = m << 1;
    }
    let checkDigit = sum - Math.floor(sum/11) * 11;
    if(checkDigit === 10)
        checkDigit = 0;
    //console.log(container_number, `sum=${sum} m=${m} ck=${checkDigit} len=${len} last = ${container_number[len]}`)
    return checkDigit === parseInt(container_number[len]);
    // http://www.gvct.co.uk/2011/09/how-is-the-check-digit-of-a-container-calculated/
    // is_container_number('GVTU3000389') true, is_container_number('CSIU2000820') true
}

function numFormatter(n, dec) {
    if(isNaN(n))
        return n;
    if(typeof Number === 'function')
        return parseFloat(n).toLocaleString('en-us', {minimumFractionDigits: dec, maximumFractionDigits:dec});

    if(typeof typeof Intl === 'object' && Intl.NumberFormat === 'function')
        return dec > 0 ?
            new Intl.NumberFormat('en-US',
                {minimumFractionDigits:dec,maximumFractionDigits:dec}).format(n) :
            new Intl.NumberFormat('en-US',
                {minimumFractionDigits:0,maximumFractionDigits:0}).format(Math.round(n));

    return n;
}

/**
 * Para un element pone print, pdf, copy text, copy as image, save as image toolbar y funcion
 *
 * Requiere: jQuery, jQuery Icons https://mkkeck.github.io/jquery-ui-iconfont,
 *  jQuery.printThis https://github.com/jasonday/printThis,
 *  copyClipboard https://clipboardjs.com/ https://github.com/zenorocha/clipboard.js,
 *  jQuery.notifyjs https://github.com/jpillora/notifyjs
 *  jspdf https://github.com/parallax/jsPDF con auto table https://github.com/simonbengtsson/jsPDF-AutoTable
 * Por hacer: pdf con unicode, pdf de un div, table a excel /[^\x20-\xFF]/g matchea unicode para elminarlos
 *
 * @type {{dateTime: (function(*): string), imageCopy: exporter.imageCopy, toolBar: (function(*): string), print: exporter.print, copy: exporter.copy, imageCopyPuede: (function(): boolean), imageSave: exporter.imageSave, pdf_table: exporter.pdf_table}}
 */
const exporter = {

    dateTime:function(d) {
        let meses=['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        if(typeof d === 'undefined')
            d = new Date();
        return d.getDate() + '/' + meses[d.getMonth()+2] + '/' + d.getFullYear().toString().substring(-2) + ' ' +
            d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
    },

    copy:function(querySelector) {
        let c = new ClipboardJS(querySelector);
        c.on('success', function (e) {
            e.clearSelection();
            $(e.trigger).notify("¡Texto Copiado!",{ position:'left', globalPosition:'bottom left',style: 'copiado', className: 'supercopiado',autoHideDelay: 2000, });
        });
    },

    imageCopy: function(querySelector) {
        try {
            $(".noprint").hide();
            html2canvas(document.querySelector(querySelector)).then(function (canvas) {
                canvas2Clipboard(canvas);
            });
            $(querySelector).notify("¡Imagen Copiada!", {
                position: 'top',
                globalPosition: 'top',
                style: 'copiado',
                className: 'supercopiado',
                autoHideDelay: 2000
            });
        } catch(e) {
            console.log("exporter.imageCopy ERROR: ",e);
        }
        $(".noprint").show();
    },

    imageCopyPuede: function() {
        return typeof ClipboardItem !== 'undefined'
    },

    /**
     * Save as image
     *
     * @param querySelector #elementId
     * @param fileName defaults to page title
     */
    imageSave:function(querySelector, fileName ) {
        try {
            if(typeof fileName !== 'string' || fileName === '')
                fileName = $("title").text();
            fileName = fileName.replaceAll(/[^a-z0-9_]/gi, ' ').trim() + ".pdf";
            $(".noprint").hide();
            html2canvas(document.querySelector(querySelector)).then(function (canvas) {
                saveAs(canvas.toDataURL(), fileName);
            });
        } catch(e) {
            console.log("exporter.imageSave ERROR: ",e);
        }
        $(".noprint").show();
    },

    /**
     * Table to PDF
     *
     * @param $table object $("#tableId")
     * @param title string defaults to page title
     * @param filename string defaults to title
     */
    pdf_table:function(table_query_selector, title, fileName) {
        try {
            if (typeof title !== 'string' || title === '')
                title = $("title").text().replaceAll(/[^a-z0-9_]/gi, ' ').trim();
            if (typeof fileName === 'undefined' || fileName === '')
                fileName = title;
            fileName = fileName.replaceAll(/[^a-z0-9_]/gi, ' ').trim() + ".pdf";
            $(".noprint").hide();
            let doc = new jspdf.jsPDF({
                orientation: 'l',
            });
            doc.setFontSize(12);
            doc.setTextColor(40);

            let startY = 10;
            let $table = $(table_query_selector);
            let caption = $table.children("caption").first().text();
            if (caption.length) {
                doc.text(caption, 5, startY);
                startY += ((caption.match(/\n/g) || []).length + 1) * 5;
            }
            doc.autoTable({html: '#' + $table.attr('id'), useCss: true, startY: startY});

            const addHeaderFooters = doc => {
                const pageCount = doc.internal.getNumberOfPages();
                const headerHeight = 5;
                const footerHeight = doc.internal.pageSize.height - 8;
                const center = doc.internal.pageSize.width / 2;
                const rgt = doc.internal.pageSize.width - 20;
                const date = exporter.dateTime();
                doc.setFontSize(8);
                for (var i = 1; i <= pageCount; i++) {
                    doc.setPage(i);
                    // header
                    doc.text(title, center, headerHeight, {align: 'center'});
                    // footer
                    doc.text(date, 10, footerHeight, {align: 'left'});
                    doc.text($vitex_globales.nick, center, footerHeight, {align: 'center'});
                    doc.text('Pg ' + String(i) + '/' + String(pageCount), rgt, footerHeight, {align: 'center'});
                }
            };
            addHeaderFooters(doc);
            doc.save(fileName);
        }catch(e) {
            console.log("exporter.pdf ERROR: ",e);
        }
        $(".noprint").show();
    },

    /**
     * Print element
     *
     * @param el object $("#elemntId")
     * @param title defaults to page title
     */
    print:function(querySelector, title) {
        if(typeof title !== 'string' || title === '' )
            title = $("TITLE").text();
        $(querySelector).printThis({
            debug: false,               // show the iframe for debugging
            importCSS: true,            // import parent page css
            importStyle: true,         // import style tags
            printContainer: false,       // print outer container/$.selector
            loadCSS:"/vitex/css2/iastyles.css", // path to additional css file - use an array [] for multiple
            pageTitle: title,              // add title to print page
            removeInline: false,        // remove inline styles from print elements
            removeInlineSelector: "*",  // custom selectors to filter inline styles. removeInline must be true
            printDelay: 666,            // variable print delay erea 333
            header: null,               // prefix to html o null
            footer: null,               // postfix to html o null / (new Date()).toDateString()
            base: false,                // preserve the BASE tag or accept a string for the URL
            formValues: true,           // preserve input/form values
            canvas: false,              // copy canvas content
            doctypeString: '',       // enter a different doctype for older markup
            removeScripts: false,       // remove script tags from print content
            copyTagClasses: false,      // copy classes from the html & body tag
            beforePrintEvent: null,     // function for printEvent in iframe
            beforePrint: null,          // function called before iframe is filled
            // afterPrint: function(){}            // function called before iframe is removed
        });
    },

    toolBar: function(query_selector) {
        let items = $(query_selector).length;
        let pdf = items === 1 &&  $(query_selector)[0].tagName === 'TABLE' ?
            `<span title='PDF' class='ui-icon ui-icon-file-pdf noprint pointer' onclick='exporter.pdf_table("${query_selector}")' ></span>`
            : '';
        let copyImage = exporter.imageCopyPuede() ?
            `<span title='Copiar imagen al clipboard' class='ui-icon ui-icon-paste noprint pointer' onclick='exporter.imageCopy("${query_selector}")'></span>`
            : '';
        return `
            <span title='Imprimir' class='ui-icon ui-icon-print-b noprint pointer' onclick='exporter.print("${query_selector}")' ></span>
            ${pdf}
            <span title='Copiar texto al clipboard' class='ui-icon ui-icon-copy pointer copyClipBloard noprint' data-clipboard-target='${query_selector}'></span>
            ${copyImage}
            <span title='Guardar como imagen' class='ui-icon ui-icon-file-image noprint pointer' onclick='exporter.imageSave("${query_selector}")'></span>
          `.replaceAll(/\s{2,}/g, ' ');
    },

};
Object.freeze(exporter);
