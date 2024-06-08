
/*
//@todo
function rgba2hex(orig) {
      var a, isPercent,
        rgb = orig.replace(/\s/g, '').match(/^rgba?\((\d+),(\d+),(\d+),?([^,\s)]+)?/i),
        alpha = (rgb && rgb[4] || "").trim(),
        hex = rgb ? 
        (rgb[1] | 1 << 8).toString(16).slice(1) +
        (rgb[2] | 1 << 8).toString(16).slice(1) +
        (rgb[3] | 1 << 8).toString(16).slice(1) : orig;
          if (alpha !== "") {
            a = alpha;
          } else {
            a = 01;
          }

          a = Math.round(a * 100) / 100;
            var alpha = Math.round(a * 255);
            var hexAlpha = (alpha + 0x10000).toString(16).substr(-2).toUpperCase();
            hex = hex + hexAlpha;

      return hex;
}
*/

var iaColor = {    
    //@todo color name to hex
    //@todo hsl, hue to hex
    //@TODO rgb2opacity
    
    
    
    /**
    * @param colors ['FF0000', '444', '#999 20%', '10%', '#00FF33']  el #% cambia lugar de la transicion de color
    * @param gradient_type string linear-gradient, repeating-linear-gradient, radial-gradient, repeating-radial-gradient, conic-gradient, repeating-conic-gradient (sin firefox)
    * @param direction string linear: to bottom, to right, to bottom, to top, -45deg, 0.2turn
    * @return string ie "linear-gradient(to bottom,#FF0000,#FF00FF,10%,#00FF00 20%);"
    * 
    * @usage css='background:' +  iaColor.gradient(['#FF0000', '#FF00FF', '10%', '#00FF00 20%'])
    *
    * see text-color gradient: {-webkit-background-clip: text;-webkit-text-fill-color: transparent;background:linear-gradient(..);}
    */
    gradient: function(colors, gradient_type, direction) {
        if(typeof gradient_type === 'undefined' || gradient_type == null || gradient_type == '')
            gradient_type = 'linear-gradient';
        if(typeof direction === 'undefined' || direction == null || direction == '')
            direction = gradient_type === 'linear-gradient' ? 'to bottom' : 'cricle';
        return  gradient_type + '(' + (direction === '' ? '' : direction + ',') + colors.join(',') + ');'; 
    },
    
    /**
    *
    *
    * @param gradientColors array same colors as for a gradient ie ['FF0000'] or
    * @param textColor null|array|string null:choose, array options in order of preference, string single option, if good use else choose
    * @param treshold float minimum contrast accepted default 2.5, recomended 4
    * @param test boolean use false, true is for iaColorTest.php only
    * @return string hex color like '#FFCC00'
    *
    * usage css='color:' +  iaColor.backgroundGetTextColor(['#FF0000', '#FF00FF', '10%', '#00FF00 20%'])+';';
    *
    */
    recomendTextColor:function(gradientColors, textColor, treshold, testMe) {

        //console.log('recomendTextColor', arguments);

        let colors = extractColors(gradientColors),
            colorsLen = colors.length;
                   
        if(treshold === 'undefined' || isNaN(treshold) || treshold === null)
            treshold = 2.5;
         
         if(testMe) {
            console.log("______ recomendTextColor: ");
            console.log("  gradientColors: ", gradientColors.join(', '));
            console.log("  textColor: ",typeof textColor === 'object' && textColor !== null ? textColor.join(', ') : textColor    );
            console.log("  treshold: ", treshold);
         }
         
        // 1.- su el color solicitado pasa el treshold usalo       
        if(typeof textColor === 'string' && textColor !== '') {
            textColor = iaColor.hexFix(textColor);
            if(goodContrast(textColor, treshold)) {
                if(testMe)
                    showResult('Proposed color in textColor parameter, ok ', textColor  );
                return textColor;        
             }    
        } 
        else if(typeof textColor === 'object' && textColor !== null) {
            for(let i in textColor) 
                if(textColor.hasOwnProperty(i)) {
                   textColor[i] = iaColor.hexFix(textColor[i]);
                   if(goodContrast(textColor[i],treshold)) {
                        showResult('Proposed color in [textColor] parameter, ok ', textColor[i])
                        return textColor[i]; 
                   }
                }
        }

        // 2.- busca el mejor color
        let candidateColors = [ 
                'FFFFFF', 'B0B0B0', '808080', 'A9A9A9',
                'FFCC00', 'CCFF00', 'FFFF00', '00FF33',
                'FF0000', '660000'
            ]; 
        for(let i = 0; i < colorsLen; ++i)
            candidateColors.push(iaColor.invertColor(colors[i], false))            
        if(typeof textColor === 'string' && textColor !== '') {
            candidateColors.push(textColor);
            candidateColors.push(iaColor.invertColor(textColor, false));
        }
        else if(typeof textColor === 'object' && textColor !== null) {
            for(let i in textColor)
                if(textColor.hasOwnProperty(i)) {
                    candidateColors.push(textColor[i]);
                    candidateColors.push(iaColor.invertColor(textColor[i], false));
                }
        }

        let candidateColorsLen = candidateColors.length, 
            colorTreshold = {};
        for(let iCandidate = 0; iCandidate < candidateColorsLen; ++iCandidate) {
            let checkColor = candidateColors[iCandidate];            
            colorTreshold[checkColor] = {min:20, max:0};
            for(let i = 0; i < colorsLen; ++i) {          
                let contrast = iaColor.contrast(checkColor, colors[i]);
                if(contrast < colorTreshold[checkColor].min)
                    colorTreshold[checkColor].min = contrast;
                if(contrast > colorTreshold[checkColor].max)
                    colorTreshold[checkColor].max = contrast;
                if(contrast < 1.2)
                    continue;
            }
            if(testMe)
                console.log("  Alternative color " + checkColor + " minimum contrast: ",  colorTreshold[checkColor].min);
        }   
        
        let bestColor = '', bestContrast = 0.00;
        for(let i in colorTreshold)
            if(colorTreshold.hasOwnProperty(i) && bestContrast <= colorTreshold[i].min) {
                bestContrast = colorTreshold[i].min;
                bestColor = i;
            }
            
        if(testMe)
            showResult('Selected alternative color: ', bestColor, bestContrast)
        
        return bestColor;
        
        function showResult(label, checkColor, contrast) {
            if(testMe) {
                if(label[0] !== ' ')
                    console.log(" * Recomended Text color: " + checkColor,  (typeof contrast === 'undefined') ? '' : ' contrast: ' + contrast );
              
                $("#selected").append(
                    '<div class= "textDiv" style="background:'+iaColor.gradient(gradientColors)+'color:#'+checkColor+'">' + label +'   ' + checkColor + ' background: ' + colors.join(', ') +
                    '<p>Frase Larga que, Te presentan, a mostrar, textColor propuestos: ' + (typeof textColor === 'object' && textColor !== null ? textColor.join(', ') : textColor ) + 
                    '<p style="border:1px silver solid;background:yellow;color:black">(Contrast: '+contrast+')</p></div>' );
            }
        }
        
        /**
        * Quita porcentajes de gradientes de la lista de colores y estandariza los colores
        *
        * @param color array ['#EAD0BE', ''FFF', '22%',  'FEAFED 20%', ...]
        * @return array ['EAD0BE', 'FFFFFF', 'FEAFED']
        */
        function extractColors(colors) {
            let useColors = [];
            for(let i = 0, colorsLen = colors.length; i < colorsLen; ++i) {
                let c = colors[i].split(' ');
                if(c[0].search('%') > 0)
                    continue;
                useColors.push(iaColor.hexFix(c[0])); 
            }
            return useColors;
        }
        
        /**
        * @param color string
        * @return bool true all colors contrast with color at or above treshold, false at least 1 below treshold
        */
        function goodContrast(checkColor, treshold) {
            for(let i = 0; i < colorsLen; ++i) {                    
                if(iaColor.contrast(checkColor, colors[i]) < treshold) {
                    if(testMe) {
                        console.log('  Proposed color, goodContrast says ' + checkColor + " rejected with backgruond color " + colors[i]+" has contrast: ",iaColor.contrast(checkColor, colors[i]));
                    }
                    return false;
                }
                
            }                
            return true;
        }
        
    },
    
    /**
    * @see https://stackoverflow.com/questions/35969656/how-can-i-generate-the-opposite-color-according-to-current-color
    * @see https://stackoverflow.com/questions/9733288/how-to-programmatically-calculate-the-contrast-ratio-between-two-colors 
    */
    
    /**
    * @param hex string #FFEEDD o FFEEDD o EEE
    * @return string EEEEEE
    * @throws error in invalida hex color
    */
    hexFix: function(hex) {
        hex = hex.trim();
        if(hex.indexOf('r') === 0)
            hex = iaColor.rgb2hex(hex);
        if (hex.indexOf('#') === 0)
            hex = hex.slice(1);    
        if (hex.length === 3)
            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        if (hex.length !== 6 && hex.length !== 8)
            throw new Error('Invalid HEX color: ' + hex);
        return hex;    
    },
    
    /**
    * @param hex string #FFEEDD o FFEEDD
    * @return [0-255, 0-255, 0-255] ie [255,0,0]
    */
    hex2rgb: function(hex) {
        let hexcolor = iaColor.hexFix(hex);
        return [parseInt(hexcolor.substr(0,2),16), parseInt(hexcolor.substr(2,2),16), parseInt(hexcolor.substr(4,2),16)];
    },

    /**
    *
    *
    * @param cssRgb string "rgb(0, 255, 0)" o "rgb(0, 255, 0, 12)" (ingora el alfa del rgba -usar iaColor.rgbOpacity("rgb(0, 255, 0, 12)")) 
    * @return string #00FF00
    */
    rgb2hex: function (cssRgb) {
          var 
            rgb = cssRgb.replace(/\s/g, '').match(/^rgba?\((\d+),(\d+),(\d+),?([^,\s)]+)?/i),
            hex = rgb ? 
            (rgb[1] | 1 << 8).toString(16).slice(1) +
            (rgb[2] | 1 << 8).toString(16).slice(1) +
            (rgb[3] | 1 << 8).toString(16).slice(1) : cssRgb;
        return '#' + hex;
    },
        
    /**
    * @param r decimal 0-255
    * @param g decimal 0-255
    * @param b decimal 0-255
    * @return float luminance level
    */
    luminanace: function(r, g, b) {        
        var a = [r, g, b].map(function (v) {
            v /= 255;
            return v <= 0.03928
                ? v / 12.92
                : Math.pow( (v + 0.055) / 1.055, 2.4 );
        });
        return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
    },
    
    /**
     * minimal recommended contrast ratio is 4.5, or 3 for larger font-sizes.
     
     * @param rgb1 [0-255, 0-255, 0-255] ie [255,0,0]
     * @param rgb2 [0-255, 0-255, 0-255] ie [255,0,0]
     * @return float  
    */
    contrast: function(rgb1, rgb2) {
        if(typeof rgb1 === 'string')
            rgb1 = iaColor.hex2rgb(rgb1);
        if(typeof rgb2 === 'string')
            rgb2 = iaColor.hex2rgb(rgb2);
        var lum1 = iaColor.luminanace(rgb1[0], rgb1[1], rgb1[2]);
        var lum2 = iaColor.luminanace(rgb2[0], rgb2[1], rgb2[2]);
        var brightest = Math.max(lum1, lum2);
        var darkest = Math.min(lum1, lum2);
        return (brightest + 0.05) / (darkest + 0.05);
    },

    /**
    *
    * @param hex string #FFEEDD o FFEEDD
    * @param bw bool true choose black or white, else inverse color
    * @return hex string FFEEDD
    */
    invertColor: function(hex, bw) {
        hex = iaColor.hexFix(hex);
        var r = parseInt(hex.slice(0, 2), 16),
            g = parseInt(hex.slice(2, 4), 16),
            b = parseInt(hex.slice(4, 6), 16);
        if(bw) {
            // http://stackoverflow.com/a/3943023/112731
            return (r * 0.299 + g * 0.587 + b * 0.114) > 186
                ? '000000'
                : 'FFFFFF';
        }
        // invert color components
        r = (255 - r).toString(16);
        g = (255 - g).toString(16);
        b = (255 - b).toString(16);
        // pad each with zeros and return
        return  iaColor.padZero(r) + iaColor.padZero(g) + iaColor.padZero(b);
    },
    
    padZero: function (str, len) {
        len = len || 2;
        var zeros = new Array(len).join('0');
        return (zeros + str).slice(-len);
    },

};

