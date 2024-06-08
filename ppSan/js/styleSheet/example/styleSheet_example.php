<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jan 1900 05:00:00 GMT");
?><!DOCTYPE HTML>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <title>test Css Style Sheets</title>
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Last-Modified" content="0">
    <meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
    <meta http-equiv="Pragma" content="no-cache">

    <!-- parte del ejemplo -->
        <style>
            .rojoAmarillo {color:red; background-color: yellow}
        </style>

        <style>
            .rojoAmarillo {margin:1em;color:black}
        </style>
        <script src="../styleSheet.js"></script>
</head>
<body>
<h1>Ejemplo de styleSheetsHelper.js</h1>
<fieldset style="display:table-cell;white-space: pre">
    &lt;style&gt;
        .rojoAmarillo {color:red; background-color: yellow}
    &lt;/style&gt;

    &lt;style&gt;
        .rojoAmarillo {margin:1em;color:black}
    &lt;/style&gt;
    &lt;script src="../styleSheet.js">&lt;/script&gt;
    &lt;script&gt;

    // consulta una clase de stylesheets o style tag
        styleSheetsHelper.getStyleString('.rojoAmarillo'); // regresa: color:black;background-color:yellow;margin:1em;
        styleSheetsHelper.getStyleObject('.rojoAmarillo'); // regresa:  { color: "black", "background-color": "yellow", margin: "1em" }
    // cambia la clase de stylesheets o style tag
        styleSheetsHelper.setStyle('.rojoAmarillo', 'color:white;background-color:black;padding:1em;border:1px yellow solid');
        styleSheetsHelper.getStyleString('.rojoAmarillo'); // regresa: color:white;background-color:black;margin:1em;padding:1em;border:1px solid yellow;

    // agrega una clase
        styleSheetsHelper.setStyle('.nuevaClase', "color:blue;padding:1em;margin:2em;width:80%;text-align:center;");
        styleSheetsHelper.getStyleString('.nuevaClase'); // regresa: color:blue;padding:1em;margin:2em;width:80%;text-align:center;

    // Existe la clase?
        styleSheetsHelper.exists('.noExiste'); // regresa: false
        styleSheetsHelper.exists('.rojoAmarillo'); // regresa: true

    &lt;/script&gt;
</fieldset>

<div class="rojoAmarillo">empece red/yellow, luego black/yellow y debo ser white/black con border amarillo</div>

<div class="nuevaClase">
    Clase creada con styleSheetsHelper
    <br>styleSheetsHelper.setStyle('.nuevaClase', "color:blue;padding:1em;margin:2em;width:80%;text-align:center;");
    <br>Debo ser con letra azul y centrado</div>
<!--suppress ES6ConvertVarToLetConst -->
<script>


    console.log("exists noExiste ", styleSheetsHelper.exists('.noExiste'));

    console.log("exists rojoAmarillo ", styleSheetsHelper.exists('.rojoAmarillo'));
    console.log("get style string ", styleSheetsHelper.getStyleString('.rojoAmarillo'));
    console.log("get style object ", styleSheetsHelper.getStyleObject('.rojoAmarillo'));
    let nuevoAmarillo = 'color:white;background-color:black;padding:1em;border:1px yellow solid';

    console.log("__ camba rojoAmarillo a: ", nuevoAmarillo);
    styleSheetsHelper.setStyle('.rojoAmarillo', nuevoAmarillo);
    console.log("   get new style string ", styleSheetsHelper.getStyleString('.rojoAmarillo'));
    console.log("   get last Rule ", styleSheetsHelper._getLastRule('.rojoAmarillo'));

    styleSheetsHelper.setStyle('.nuevaClase', "color:blue;padding:1em;margin:2em;width:80%;text-align:center;");
    console.log("get nueva clase ", styleSheetsHelper.getStyleString('.nuevaClase'));

    console.log("set nuevoDeStrin  ", styleSheetsHelper.setStyle('.nuevoDeString', "color:blue;padding:1em;margin:2em"));
    console.log("get nuevoDeStrin ", styleSheetsHelper.getStyleObject('.nuevoDeString'));

    console.log("set nuevoDeObject ", styleSheetsHelper.setStyle('.nuevoDeObject', {color:'white',margin:'23em'}));
    console.log("get nuevoDeObject ", styleSheetsHelper.getStyleObject('.nuevoDeObject'));




</script>

<hr>
<ul>
        <li>Ojo el style string debe estar bien escrito, igual que en css,
            del contrario el error y posiblemente despues de el no se consideran.</li>
        <li style="color:red">Abrir la consola y ver page source.</li>
</ul>
</body>
</html>
