/**
 * highlighter. Resalta la frase o palabas indicadas, con el tag MARK <mark>Buscado</mark>
 *
 * var highlighter   = new Highlighter ("content"); // id of the element to parse
 *     highlighter  .setForceColorBlack(false); // usa color de MARK {color:inherit;} o color deseado
 *     highlighter  .setBreakRegExp(""); // buscar como una frase no varias palabras, quitar para palabra por palabra
 *     highlighter  .apply("words to highlight".toLowerCase().trim().replaceAll(/\s\s+/gm,' '));
 *     highlighter  .remove(); // undo highgliht
 * tip en
 *      <style>MARK {color:inherit;}</style>
 *     $gIaHeader->html_head_add( [... , 'highlightor']);
 *     $gIaHeader->html_head_echo();
 *
 * // Original JavaScript code by Chirp Internet: chirpinternet.eu
 * // Please acknowledge use of this code by including this header. https://www.the-art-of-web.com/javascript/search-highlight/

 showAll: function(){ // clear filtro
                $("#diff_notas tbody").children().show();
                diff_notas.setDefaultCaption();
                var highlighter   = new Highlighter ("diff_notas");
                highlighter  .remove();
            } ,
 filtra: function(find) {
                    var highlighter   = new Highlighter ("diff_notas");
                    find = find.trim().toLowerCase().replaceAll(/\s\s+/gm,' ');
                    if(find.length === 0) {
                        diff_notas.showAll();
                        diff_notas.soloProblemas();
                        diff_notas.setDefaultCaption();
                        highlighter  .remove();
                        return;
                    }
                    highlighter  .setBreakRegExp("");
                    highlighter  .apply(find);
                    $("#solo_problemas").prop('checked', false);
                    $("#diff_notas tbody").children("TR[data-ok]").each(function(){
                    let tr=$(this);
                    if(tr.text().toLowerCase().search(find) === -1)
                        tr.hide();
                    else
                        tr.show();
                });
                let caption=document.getElementById("diff_notas_caption");
                caption.innerHTML = "Mostrando: '<i>" + find +"</i>'";
            },

 */
function Highlighter (id, tag) {

    // private variables
    var targetNode = document.getElementById(id) || document.body;
    var hiliteTag = tag || "MARK";
    var skipTags = new RegExp("^(?:" + hiliteTag + "|SCRIPT|INPUT|SELECT|TEXTAREA)$"); // |SPAN|FORM
    var colors = ["#ff6", "#a0ffff", "#9f9", "#f99", "#f6f"];
    var wordColor = [];
    var colorIdx = 0;
    var matchRegExp = "";
    var openLeft = false;
    var openRight = false;
    var forceColor = true;

    // characters to strip from start and end of the input string
    var endRegExp = new RegExp('^[^\\w]+|[^\\w]+$', "g");

    // characters used to break up the input string into words
    var breakRegExp = new RegExp('[^\\w\'-]+', "g");

    this.setForceColorBlack = function(b) { forceColor = b;};

    this.setEndRegExp = function(regex) {
        endRegExp = regex;
        return endRegExp;
    };

    this.setBreakRegExp = function(regex) {
        breakRegExp = regex;
        return breakRegExp;
    };

    this.setMatchType = function(type)
    {
        switch(type)
        {
            case "left":
                this.openLeft = false;
                this.openRight = true;
                break;

            case "right":
                this.openLeft = true;
                this.openRight = false;
                break;

            case "open":
                this.openLeft = this.openRight = true;
                break;

            default:
                this.openLeft = this.openRight = false;

        }
    };

    this.setRegex = function(input)
    {
        input = input.replace(endRegExp, "");
        if(breakRegExp)
            input = input.replace(breakRegExp, "|");
        input = input.replace(/^\||\|$/g, "");
        if(input) {
            var re = "(" + input + ")";
            if(!this.openLeft) {
                re = "\\b" + re;
            }
            if(!this.openRight) {
                re = re + "\\b";
            }
            matchRegExp = new RegExp(re, "i");
            return matchRegExp;
        }
        return false;
    };

    this.getRegex = function()
    {
        var retval = matchRegExp.toString();
        retval = retval.replace(/(^\/(\\b)?|\(|\)|(\\b)?\/i$)/g, "");
        retval = retval.replace(/\|/g, " ");
        return retval;
    };

    // recursively apply word highlighting
    this.hiliteWords = function(node)
    {
        if(node === undefined || !node) return;
        if(!matchRegExp) return;
        if(skipTags.test(node.nodeName)) return;

        if(node.hasChildNodes()) {
            for(var i=0; i < node.childNodes.length; i++)
                this.hiliteWords(node.childNodes[i]);
        }
        if(node.nodeType === 3) { // NODE_TEXT

            var nv, regs;

            if((nv = node.nodeValue) && (regs = matchRegExp.exec(nv))) {

                if(!wordColor[regs[0].toLowerCase()]) {
                    wordColor[regs[0].toLowerCase()] = colors[colorIdx++ % colors.length];
                }

                var match = document.createElement(hiliteTag);
                match.appendChild(document.createTextNode(regs[0]));
                match.style.backgroundColor = wordColor[regs[0].toLowerCase()];
                if(forceColor)
                    match.style.color = "#000";

                var after = node.splitText(regs.index);
                after.nodeValue = after.nodeValue.substring(regs[0].length);
                node.parentNode.insertBefore(match, after);

            }
        }
    };

    // remove highlighting
    this.remove = function()
    {
        var arr = document.getElementsByTagName(hiliteTag), el;
        while(arr.length && (el = arr[0])) {
            var parent = el.parentNode;
            parent.replaceChild(el.firstChild, el);
            parent.normalize();
        }
    };

    // start highlighting at target node
    this.apply = function(input)
    {
        this.remove();
        if(input === undefined || !(input = input.replace(/(^\s+|\s+$)/g, ""))) {
            return;
        }
        if(this.setRegex(input)) {
            this.hiliteWords(targetNode);
        }
        return matchRegExp;
    };

}
Object.freeze(Highlighter );