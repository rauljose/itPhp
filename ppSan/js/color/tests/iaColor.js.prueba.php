<!DOCTYPE HTML>
<html lang="es-MX">
<head>
<meta charset="UTF-8">
<title>iaColor</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<style>
    .testDiv {border:2px silver solid; padding:0.5em; margin:0.5em}
</style>
<script src="../js2/jquery-3.4.1.min.js"></script>
<script src="../js2/iaColor.js"></script>
<style>
</style>
</head>
<body>
<h1>iaColor.js visual test</h1>

<div style="margin: 4em;">
    <fieldset id="selected"><legend>Runs</legend>
        
    </fieldset>

</div>


<i>Ver la consola</i>
<p><i>Poner scripts a probar:</i></p>
<pre>
    /**
    *
    *
    * @param gradientColors array same colors as for a gradient ie ['FF0000'] or
    * @param textColor null|array|string null:choose, array options in order of preference, string single option, if good use else choose
    * @param treshold float minimum contrast accepted default 3.4, recomended 4
    * @param test boolean use false, true mensajes en consola y agrega div con background y selected TextColor a #selected
    * @return string hex color like '#FFCC00'
    *
    * usage css='color:' +  iaColor.backgroundGetTextColor(['#FF0000', '#FF00FF', '10%', '#00FF00 20%'])+';';
    *
    */
    recomendTextColor:function(gradientColors, textColor, treshold, testMe)
</pre>
<script>
    iaColor.recomendTextColor(['#FFFFFF',],"#FFFFFF", 3.4, true);
    
    iaColor.recomendTextColor(['#FFFFFF 50%', '#FF0000 20%',], [  '#FF0000','#FFFFFF', '#CCFF00', '#0000FF',], null, true);
    iaColor.recomendTextColor(['#003333 50%', '#FF0000 20%',], ['#FFFFFF'], null, true);
    iaColor.recomendTextColor(['#003333 50%', '#FF0000 20%',], '#CCFF00', 3.4, true);
    iaColor.recomendTextColor(['#0011FF 20%', '20%', '#FF0000 30%', '#FF9900',], null, null, true);
    iaColor.recomendTextColor(['rgb(255,120,12) 50%', '#FF0000 20%',],['#331111', "#220000"], 3.4, true);
    
</script>
</body>
</html>