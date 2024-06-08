/**
 *  agregar,modificar y consultar clases en stylesheets o style tags
 *
 * @version 1.1 2021-04-09 Docblocks, termina ejemplo y prueba
 */

styleSheetsHelper = {

    /**
     * is className defined?. use .lbl or #id
     *
     * @param className {string}
     * @returns {boolean}
     */
    exists: function(className) {
        for(let iStyleSheet=0, lenStyleSheets=document.styleSheets.length; iStyleSheet < lenStyleSheets; ++iStyleSheet) {
            let classes = document.styleSheets[iStyleSheet].rules || document.styleSheets[iStyleSheet].cssRules;
            for (let x = 0; x < classes.length; x++)
                if (classes[x].selectorText === className)
                    return true;
        }
        return false;
    },

    /**
     * getClassDefiniton(".lbl") returns  "color:blue;"
     *
     * @param className  {string}
     * @returns  {string}, empty string className not defined
     */
    getStyleString: function(className) {
        return styleSheetsHelper.simplyfy(styleSheetsHelper._get(className));
    },


    /**
     * getStyleObject(".lbl") returns  {color:'blue'}
     *
     * @param className  {string}
     * @returns {object}, empty {} on className not defined
     */
    getStyleObject: function(className) {
        return styleSheetsHelper.styleString2object(styleSheetsHelper._get(className));
    },


    /**
     * Modify or add className, .lbl or #id, with style definition
     *
     * @param className  {string}
     * @param style string "color:red;" or object {color:'red'}
     */
    setStyle: function(className, style) {
        if(typeof style === 'string' && style.indexOf("{") >= 0 && style.lastIndexOf("}") >=0 )
            style = style.substring(style.indexOf("{") + 1,  style.lastIndexOf("}")).trim();
        var rule = styleSheetsHelper._getLastRule(className);
        if(rule === false) {
            var iStyleSheet = document.styleSheets.length - 1;
            document.styleSheets[iStyleSheet].insertRule(className + " {" + (typeof style === 'string' ? style : styleSheetsHelper.styleObject2String(style)) +  "}",
                document.styleSheets[iStyleSheet].length);
            return;
        }
        var useStyle = (typeof style === 'string') ? styleSheetsHelper.styleString2object(style) : style;
        for(var s in useStyle)
            if(useStyle.hasOwnProperty(s))
                rule.style[s] = useStyle[s];
    },

    // helper

    /**
     *
     * @param obj
     * @returns {string}
     */
    styleObject2String: function(obj) {
        var str = '';
        for(var s in obj)
            if(obj.hasOwnProperty(s))
                str += s.trim() + ':' + obj[s].toString().trim() + ";";
        return str;
    },

    /**
     *
     * @param str  {string}
     * @returns {}
     */
    styleString2object: function(str) {
        var style = {},
            pairs = str.split(';');
        for(var i=0, len=pairs.length; i < len; ++i) {
            if(pairs[i].length > 0) {
                var s = pairs[i].split(':');
                if(s.length === 2)
                    style[s[0].trim()] = s[1].trim();
            }
        }
        return style;
    },

    /**
     *
     * @param str {string}
     * @returns {string}
     */
    simplyfy:function(str) {
        return styleSheetsHelper.styleObject2String(styleSheetsHelper.styleString2object(str));
    },

    // private

    /**
     * Obtiene todos los estilos de la clase
     *
     * @param className {string}
     * @returns {string}
     * @private
     */
    _get: function(className) {
        var cssText = "";
        for(var iStyleSheet=0, lenStyleSheets=document.styleSheets.length; iStyleSheet < lenStyleSheets; ++iStyleSheet) {
            var classes = document.styleSheets[iStyleSheet].rules || document.styleSheets[iStyleSheet].cssRules;
            for (var x = 0; x < classes.length; x++)
                if (classes[x].selectorText === className) {
                    var s = classes[x].cssText || classes[x].style.cssText;
                    cssText += s.substring(s.indexOf("{") + 1,  s.lastIndexOf("}")).trim();
                }
        }
        return cssText;
    },

    /**
     * Get document.styleSheets[LastDefinition].rules for className, use .lbl or #id
     *
     * @param className  {string}
     * @returns {CSSRule|boolean} false on not found
     */
    _getLastRule: function(className) {
        for(var iStyleSheet=document.styleSheets.length -1; iStyleSheet >= 0; --iStyleSheet) {
            var classes = document.styleSheets[iStyleSheet].rules || document.styleSheets[iStyleSheet].cssRules;
            for (var x = classes.length -1; x >= 0; --x) {
                if (classes[x].selectorText === className)
                    return classes[x];
            }
        }
        return false;
    },



};
