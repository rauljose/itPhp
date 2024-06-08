<?php
use Iac\inc\sql\IacSqlBuilder;
// document.getElementById("numero").classList.remove("Inactive") .add() .classList.contains("class-name") toggle
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once('../../inc/config.php');

global $gDebugging; $gDebugging = true;
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
if(!usuarioTipoRony()) die("SIN PREMISO");
ignore_user_abort(false);
/*




You can use following code

var cm = jQuery(“#list1″).jqGrid(“getGridParam”, “colModel”);
var colName = cm[iCol];

to get the column name colName
function resetColumnOrder(initialcolumnorder)
{
     var currentColumnOrder = $("#Grid").getGridParam('colNames');
     var columnMappingArray = [];
     $.each(initialcolumnorder, function (index, value) {
         var currentIndex = $.inArray(value, currentColumnOrder);
         columnMappingArray.push(currentIndex);
     });

     $("#Grid").remapColumns(columnMappingArray, true, false);
}
 */
?><!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <title>Pepe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <?php echo "<style>" . iaTableIt::getCssClases() . "</style>"; ?>
    <style>
        .marmol {background-color:  #e6e4d8}
    </style>
    <style id="tabler_styles">
        TABLE.tabler {border:1px silver solid; border-collapse: collapse; }
        TABLE.tabler CAPTION {border:1px silver solid; font-weight: bold; font-size:1.1em }
        TABLE.tabler TD {border:1px silver solid; text-align: left; vertical-align: top; padding:0.4em; }
        TABLE.tabler TH {border:1px silver solid; text-align: center; vertical-align: middle; padding:0.4em; font-weight: bold}
        TABLE.tabler TD.izq {text-align: left; }
        TABLE.tabler TD.lft {text-align: left;}
        TABLE.tabler TD.cen {text-align: center;}
        TABLE.tabler TD.der {text-align: right; }
        TABLE.tabler TD.rgt {text-align: right; }
    </style>
    <style id="gradients_styles">

        .gradient_azulito { background-image: linear-gradient(135deg, #93a5cf 10%, #e4efe9 100%); }

        .gradient_chequered {
            background: conic-gradient(
                    #fff 0.25turn,
                    #000 0.25turn 0.5turn,
                    #fff 0.5turn 0.75turn,
                    #000 0.75turn
            ) top left / 25% 25% repeat;
            border: 1px solid;
        }
        .gradient_bullseye{
            background-image: radial-gradient(circle,
            #FFF535 5px,
            #FFF535 10px,

            #FB1D14 10px,
            #FB1D14 25px,

            #0000FF 25px,
            #0000FF 40px,

            #000000 40px,
            #000000 55px,

            #FFFFFF 55px,
            #FFFFFF 60px,
            #FFFFFF 100px);
        }
        .gardient_piquitos {
            /* https://css-tricks.com/background-patterns-simplified-by-conic-gradients/ */
            background:
                    linear-gradient(315deg, transparent 75%, #d45d55 0) -10px 0,
                    linear-gradient(45deg, transparent 75%, #d45d55 0) -10px 0,
                    linear-gradient(135deg, #a7332b 50%, transparent 0) 0 0,
                    linear-gradient(45deg, #6a201b 50%, #561a16 0) 0 0 #561a16;
            background-size: 10px 10px;
        }
    </style>

    <style id="grider_styles">
        .grider_grid-auto {
            --auto-grid-min-size: 4em;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(var(--auto-grid-min-size), 1fr));
            grid-gap: 1em;
        }

        /* a table */
        .grider_table {
            --auto-grid-min-size: 4em;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(var(--auto-grid-min-size), 1fr));
            grid-gap: 0;
            border:1px silver solid;
            border-collapse: collapse;
        }

        .grider_table DIV {
            padding:0.3em;
            border:1px silver solid;
        }

        /* masonry: https://css-tricks.com/a-lightweight-masonry-solution/ */

        /* toolbar */
        .grider_toolbar {
            --auto-grid-min-size: 4em;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(var(--auto-grid-min-size), 1fr));
            grid-gap: 0;
        }

        /* calendario */
        .grider_calendario {
            margin:0;
            padding:0.3em;
            border:1px silver solid;
            font-family: "Courier New", Courier, monospace;
        }
        .grider_calendario_titulo_mes {color:blue;text-align: center;width:100%}
        .grider_calendario_titulo_dias {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap:0.5em;
            background-color:whitesmoke;
            margin-bottom:0.5em
        }
        .grider_calendario_dias {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap:0.5em
        }

        .grider_calendario_dias DIV  {
            padding:1em;
            border:1px silver solid;
            color:blue;
        }
        .grider_calendario_dias DIV:first-child  { grid-column-start: 3; }

    </style>
    <script>
        let iaHtml = {
            jsLoadOnce: function(src) {
                const scripts = document.getElementsByTagName('script');
                for (let script of scripts)
                    if (src === script.src)
                        return;
                const tag = document.createElement('script');
                tag.src = src;
                document.getElementsByTagName('body')[0].appendChild(tag);
            },
            cssLoadOnce: function(href, media) {
                const links = document.getElementsByTagName('link');
                for (let link of links)
                    if (href === link.href)
                        return;
                const tag = document.createElement('link');
                tag.rel = 'stylesheet';
                tag.type = 'text/css';
                if(typeof media === 'string')
                    tag.media = media;
                tag.href = href;
                document.getElementsByTagName('body')[0].appendChild(tag);
            },
        };
        Object.freeze(iaHtml);

        const debugIt = {
            logEvents: function (eventNamePart) {
                let events = debugIt.getEvents(window, eventNamePart);
                for (let ev of events)

                    console.log("events for := " + eventNamePart + " ev=", ev);
            },
            getEvents: function (root, eventNamePart) {
                let events = [];
                let regExp = typeof eventNamePart === 'string' ? new RegExp(eventNamePart, 'i') : '';
                const objectHasSubPrototype = (object, comp) => {
                    let proto = Object.getPrototypeOf(object);
                    while (proto !== null && proto !== EventTarget) {
                        proto = Object.getPrototypeOf(proto);
                    }
                    return (proto !== null);
                };
                const addEventNames = (propNames) => {
                    propNames.filter(x => x.match(/^on\w+$/)).forEach((propName) => {
                        propName = propName.substr(2);
                        if (events.indexOf(propName) === -1 && (typeof eventNamePart !== 'string' || propName.search(regExp) > 0))
                            events.push(propName);
                    });
                };

                Object.getOwnPropertyNames(root).forEach((name) => {
                    let value = root[name];
                    if (value) {
                        if (objectHasSubPrototype(value, EventTarget)) {
                            let propNames = Object.getOwnPropertyNames(Object.getPrototypeOf(value).prototype);
                            addEventNames(propNames);
                            propNames = Object.getOwnPropertyNames(window);
                            addEventNames(propNames);
                        }
                    }
                });
                return events;
            }
        }
        Object.freeze(debugIt);
    </script>
    <script>
        function jsLoadOnce(src) {
            let paramsAt = fileName.lastIndexOf('?');
            const base = urlOnly(src);
            const scripts = document.getElementsByTagName('script');
            for (let script of scripts)
                if (src === script.src || urlOnly(script.src) === base)
                    return;
            const tag = document.createElement('script');
            tag.src = src;
            document.getElementsByTagName('body')[0].appendChild(tag);
            function urlOnly(url) {
                let upTo = url.lastIndexOf('?');
                if(upTo !== -1)
                    url = url.substring(0, upTo)
                upTo = fileName.lastIndexOf('#');
                return upTo === -1 ? url : url.substring(0, upTo);
            }
        }
        function basename(url) {
            var fileName = url.substring(url.lastIndexOf('/') + 1);
            var params = fileName.lastIndexOf('?');
            return params === -1 ? fileName : fileName.substring(0, params);
        }
    </script>
</head>
<body>
<hr>
<fieldset><legend>On Masonry</legend>
    <div>Flutter<p>
        <ol>
            <li>view-source:https://w3bits.com/labs/css-grid-masonry/</li>
            <li>You can create view using the RecyclerView and use StaggeredGridLayoutManager to create masonry layout Effect.
            For further details on how to implement this you can check https://www.geeksforgeeks.org/recyclerview-as-staggered-grid-in-android-with-example/ example.
            <li>https://pub.dev/packages/flutter_masonry_view</li>
            <li>https://pub.dev/packages/flutter_staggered_grid_view</li>
        </ol>
    </div>
    <ol>
        <li>https://freefrontend.com/css-masonry-layout-examples/</li>
    </ol>
    <pre>
        &lt;div class="grid-wrapper"&gt; &lt;div&gt;&lt;img&gt;&lg;div&gt; &lg;div&gt;
        /* Reset CSS */
* {
	margin: 0;
	padding: 0;
	box-sizing: border-box;
}
html,
body {
	background: linear-gradient(45deg, #190f2c, #200b30);
	padding: 15px;
}
img {
	max-width: 100%;
	height: auto;
	vertical-align: middle;
	display: inline-block;
}

/* Main CSS */
.grid-wrapper > div {
	display: flex;
	justify-content: center;
	align-items: center;
}
.grid-wrapper > div > img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	border-radius: 5px;
}

.grid-wrapper {
	display: grid;
	grid-gap: 10px;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	grid-auto-rows: 200px;
	grid-auto-flow: dense;
}
.grid-wrapper .wide {
	grid-column: span 2;
}
.grid-wrapper .tall {
	grid-row: span 2;
}
.grid-wrapper .big {
	grid-column: span 2;
	grid-row: span 2;
}

        <hr><h2>altri</h2>
        .container {
            max-width: 1224px;
            margin: 0 auto;
        }

        img {
            width: 500px;
            object-fit: contain;
            border-radius: 15px;
        }



figure {
 margin: 0;
 display: inline-block;
 margin-bottom: 0px;
 width: 100%;
}

.gallery-container {
 column-count: 3;
 column-gap: 20px 20px;
 width: 1200px;
}

        <hr><h2>Con huecos</h2><hr>
        img {max-width: 100%;display: block;}
        figure {
          margin: 0;
          display: grid;
          grid-template-rows: 1fr auto;
          margin-bottom: 10px;
          break-inside: avoid;
        }

        figure > img {
          grid-row: 1 / -1;
          grid-column: 1;
        }

        figure a {
          color: black;
          text-decoration: none;
        }

        figcaption {
          grid-row: 2;
          grid-column: 1;
          background-color: rgba(255,255,255,.5);
          padding: .2em .5em;
          justify-self: start;
        }

        .container {
          column-count: 4;
          column-gap: 10px;
        }
        <hr><h2></h2><hr>
</pre></fieldset>
<hr>
<fieldset style="margin:1em;padding:1em;display:flex;flex-direction:row;gap:2em"><legend>Gradientes</legend>
    <div class="gradient_chequered" style="width:20px;height:20px;padding:0;"></div>
    <div class="gradient_bullseye" style="width:20px;height:20px;padding:0;"></div>
    <div class="gardient_piquitos" style="width:20px;height:20px;padding:0;"></div>
</fieldset>
<hr>
<?php
    $valores = [true,false];
    echo "<table class='tabler'><caption>&&, ||, AND, OR<p style='font-weight: lighter;font-size:0.8em'>Orden de las Operaciones<br><i>y otros trucos</i></caption>";
    //echo "<thead><tr><th>lhs<th>Op<th>lhs<th>=<th>Vs<th>lhs<th>Op<th>lhs<th>=<th>";
    echo "</thead><tbody>";
    foreach($valores as $lhs) {
        $l = $lhs ? 'T' : 'F';
        foreach($valores as $rhs) {
            $r = $rhs ? 'T' : 'F';
            echo "<tr>";
            echo "<th>$l && $r ? 'T' : 'F'<th>=<td>" . ($lhs && $rhs ? "T" : "F");
            echo "<th>$l AND $r ? 'T' : 'F'<th>=<td>" . ($lhs AND $rhs ? "T" : "F");
            echo "<th>($l AND $r) ? 'T' : 'F'<th>=<td>" . (($lhs AND $rhs) ? "T" : "F");
        }
    }
echo "<tr><td colspan='8'></td>";
foreach($valores as $lhs) {
    $l = $lhs ? 'T' : 'F';
    foreach($valores as $rhs) {
        $r = $rhs ? 'T' : 'F';
        echo "<tr>";
        echo "<th>$l || $r ? 'T' : 'F'<th>=<td>" . ($lhs || $rhs ? "T" : "F");
        echo "<th>$l OR $r ? 'T' : 'F'<th>=<td>" . ($lhs or $rhs ? "T" : "F");
        echo "<th>($l OR $r) ? 'T' : 'F'<th>=<td>" . (($lhs or $rhs) ? "T" : "F");
    }
}
    echo "</tbody></table>";
?>
<hr><h1>Flex Grid Things</h1>
<div style="margin:auto;width:fit-content;text-align:center">
    <script>
    function ponCal(year, month) {
        let dated = new Date(`${year}-${month}-01`);
        let firstDay = dated.getDay();
        let days = [];
        for(let day = 1, weekDay = firstDay; i <= dated.getDate(); day++) {

        }
    }
    </script>
    <div class="grider_calendario">
        <div class="grider_calendario_titulo_mes">Agosto 2023</div>
        <div class="grider_calendario_titulo_dias">
            <div>Do</div>
            <div>Lu</div>
            <div>Ma</div>
            <div>Mi</div>
            <div>Ju</div>
            <div>Vi</div>
            <div>Sa</div>
        </div>
        <div class="grider_calendario_dias">
            <div>1</div>
            <div>2</div>
            <div>3</div>
            <div>4</div>
            <div>5</div>
            <div>6</div>
            <div>7</div>
            <div>8</div>
            <div>9</div>
            <div>10</div>
            <div>11</div>
            <div>12</div>
            <div>13</div>
            <div>14</div>
            <div>15</div>
            <div>16</div>
            <div>17</div>
            <div>18</div>
            <div>19</div>
            <div>20</div>
            <div>21</div>
            <div>22</div>
            <div>23</div>
            <div>24</div>
            <div>25</div>
            <div>26</div>
            <div>27</div>
            <div>28</div>
            <div>29</div>
            <div>30</div>
        </div>
    </div>
</div>
<script id="sonoilScript">
    console.log("estoy corriendo en document.currentScript = ", document.currentScript.src || document.currentScript.id || 'inline script tag sin id')
</script>
<pre>document.currentScript.src || document.currentScript.id || 'n/a' </pre>
<fieldset id="table_grid_container"
    style="margin:1em;padding:1em;border:1px silver solid;
        width:50%;
        max-height: 12em;overflow-y: scroll"><legend>Como tabla pero dobla renglón en resize</legend>
    <div class="grider_table">
        <div>01 Hola celda del auto grid e questa que es largona</div>
        <div>02 Hola celda del auto grid e questa que es largona</div>
        <div>03 Hola celda del auto grid e questa que es largona</div>
        <div>04 Hola celda del auto grid e questa que es largona</div>
        <div>05 Hola celda del auto grid e questa que es largona</div>
        <div>06 Hola celda del auto grid e questa que es largona</div>
        <div>07 Hola celda del auto grid e questa que es largona</div>
        <div>08 Hola celda del auto grid e questa que es largona</div>
        <div>09 Hola celda del auto grid e questa que es largona</div>
        <div>10 Hola celda del auto grid e questa que es largona</div>
        <div>11 Hola celda del auto grid e questa que es largona</div>
        <div>12 Hola celda del auto grid e questa que es largona</div>
        <div>13 Hola celda del auto grid e questa que es largona</div>
        <div>14 Hola celda del auto grid e questa que es largona</div>
        <div>15 Hola celda del auto grid e questa que es largona</div>
        <div>16 Hola celda del auto grid e questa que es largona</div>
        <div>17 Hola celda del auto grid e questa que es largona</div>
        <div>18 Hola celda del auto grid e questa que es largona</div>
    </div>
</fieldset>
<?php
/*
echo "<h1>bodega_existencia_diaria_recalcula</h1>";
echo "<i>" . Date('H:i:s') . "</i>";
echo str_repeat('&nbsp;', 1024*8);
ob_flush();
flush();
bodega_existencia_diaria_recalcula();
echo "<i>" . Date('H:i:s') . "</i>";
*/
class formatMe {
    public string $prefix = '';
    public string $suffix = '';
    public string $nullAs = '';
    public string $emptyAs = '';
    public string $trueAs = '';
    public string $falseAs = '';
    public array $valueAs = [
        'Si' => 'Si',
        'No' => 'No',
    ];
    public int $decimals = 2;
    public string $thousandSperator = ',';
    public string $decimalPoint = '.';

    public string $dateFormat = 'd/M/y';
    public string $dateTimeFormat = 'd/M/y H:i';
    public string $label = '';
    public string $title = '';

}


function diaVer($bodega, $producto, $color) {
    $builder = new IacSqlBuilder();
    $where = $builder->where(['bodega' => $bodega, 'producto' => $producto, 'color' => $color]);
    $sql =
        "SELECT fecha,
                existencia_rollos as R_final,  
                    entrada_rollos as R_entrada, 
                    salida_rollos as R_Salidas,
                existencia_rollos_inicial as R_inicial,
                '' as R_Ok,

                existencia_quantity as Q_final,  
                    entrada_quantity as Q_entrada, 
                    salida_quantity as Q_Salidas,
                existencia_quantity_inicial as Q_inicial,
                '' as Q_Ok
         FROM bodega_existencia_diaria
         WHERE $where
         ORDER BY fecha DESC";
    echo iaTableIt::getTableIt(ia_sqlArrayIndx($sql), "$bodega $producto $color");
}

echo "<ol>";
echo "<li><a href='pepe.php?recalc_new=1'>Recalcular Bodega Diaria de notas y resets new</a>";
echo "<li><a href='pepe.php?recalc_old=1'>Recalcular Bodega Diaria de notas y resets OLD</a>";
echo "</ol>";
$reclac = $_REQUEST['recalc_new'] ?? '';
if(!empty($reclac)) {
    echo "<h1>Recalc New</h1>";
    $i = 0;
    bodega_existencia_diaria_recalcula(true, $i, true);
    diaVer("Amarilla", "Azucena", "Agua");
    diaVer("Virgoma", "Torneo", "Cielo");
}
if(!empty($reclac)) {
    echo "<h1>Recalc OLD</h1>";
    $i = 0;
    bodega_existencia_diaria_recalcula(true, $i, false);
    diaVer("Amarilla", "Azucena", "Agua");
    diaVer("Virgoma", "Torneo", "Cielo");
}
diaVer("Cielo", "Azucena", "Agua");
diaVer("Virgoma", "Torneo", "Cielo");

function bodega_existencia_diaria_recalcula($showProgress, &$i, $useNewClass) {
    $bed = new nbed();
    $oldBed =  new BodegaExistenciaDiaria(false);

    $resets = ia_sqlArrayIndx(
        "SELECT DATE(alta_db) as fecha, bodega_id, producto_general_id, color_id, rollos, quantity, alta_por as usuario_por 
            FROM reset_history 
            ORDER BY alta_db");

    $notas = ia_sqlArray(
        "SELECT nb.nota_bodega_id, nb.fecha, nb.bodega_id, nb.producto_general_id, nb.entrada_salida, nb.tipo, nb.origen_id, nb.por_sistema 
        FROM nota_bodega nb
            JOIN nota_bodega_items nbi on nb.nota_bodega_id = nbi.nota_bodega_id
        WHERE nb.tipo NOT IN ('Cancelacion', 'Borrado')    
        ORDER BY fecha", 'nota_bodega_id'
    );

    global $gSqlClass;
    $gSqlClass->traceOn = false;
    $con = $useNewClass ? "nbed" : "NotaBodegaExistenciaDiaria";
    if($showProgress)
        echo "<div style='padding:1em;margin:1em'><h1>Recalculando Bodega Existencia Diaria, $con: <span id='van'></span> nota de " . bcformat(count($notas) + count($resets), 0) . " notas y resets</h1></div>";
    ia_query("DELETE FROM bodega_existencia_diaria");

    foreach($notas as $nota_bodega_id => $nota) {
        $items = ia_sqlArrayIndx("SELECT * FROM nota_bodega_items WHERE nota_bodega_id='$nota_bodega_id'");
        if($useNewClass)
            $queries = $bed->notaAdd($nota, $items);
        else {
            $queries = [];
            foreach($items as $item) {
                if($nota['tipo'] === 'Correccion' && $nota['por_sistema'] == '1')
                    $queries[] = $oldBed->sqlAjustaItem($nota, $item);
                else
                    $queries[] = $oldBed->sqlRegistraItem($nota, $item);
                $next = $oldBed->updateRegistraExistenciaInicial($nota, $item);
                if(!empty($next))
                    $queries[] = $next;
            }
        }
        if( !empty($queries))
            ia_transaction($queries);
        if( !empty($gSqlClass->errorLog_get())) {
            break;
        }
        if((++$i % 100) === 0 && $showProgress) {
            echo "<script>document.getElementById('van').innerHTML = '" . bcformat($i, 0) . "';</script>";
            ob_flush();
            flush();
        }
    }
    unset($notas);
    if($showProgress)
        echo "<script>document.getElementById('van').innerHTML = '" . bcformat($i, 0) . "';</script>";

    global $gDime;
    $gDime[] = "<h2>Resets</h2>";
    $gDime['resets'] = 0;

    foreach($resets as $r) {
        $gDime['resets']++;
        $r['rollos'] = bcmul("-1", $r['rollos'] ?? '0', 0);
        $r['quantity'] = bcmul("-1", $r['quantity'] ?? '0.00', 2);
        if($useNewClass)
            $queries = $bed->sqlResetItem($r);
        else
            $queries = $oldBed->sqlResetItem($r);
if($r['bodega_id'] === '74867af2fe60ad6b11ec6d7b3badcba6' && $r['producto_general_id'] === '54bf6469e2cc850b11ec1ca9fdfac21f' && $r['color_id'] === '54bf6469e2cc850b11ec1c9224a12ebf') {
    $gDime[] = "<hr>";
    $gDime[] = $r;
    $gDime[] = $queries;
    echo "<hr><pre>" . print_r($r, true) . print_r($queries, true) . "</pre>";
}
        if( !empty($queries))
            ia_transaction($queries);
        if( !empty($gSqlClass->errorLog_get())) {
            break;
        }
        if((++$i % 100) === 0) {
            echo "<script>document.getElementById('van').innerHTML = '" . bcformat($i, 0) . "';</script>";
            ob_flush();
            flush();
        }
    }
    if($showProgress)
        echo "<script>document.getElementById('van').innerHTML = '" . bcformat($i, 0) . "';</script>";

    if($showProgress)
        echo "<h1>DONE</h1>";
}

class nbed {
    //  [...$a, ...$b];
    protected IacSqlBuilder $builderSql;
    protected $bodegaIdGrupo = [];
    protected $bodegaIdBodega = [];
    protected $productoIdProducto = [];
    protected $colorIdColor = [];

    public function __construct() {
        $this->builderSql = new IacSqlBuilder();
        $method = __METHOD__;
        $this->bodegaIdGrupo = ia_sqlKeyValue("SELECT /*$method*/ bodega_id, grupo FROM bodega");
        $this->bodegaIdBodega = ia_sqlKeyValue("SELECT /*$method*/ bodega_id, bodega FROM bodega");
        $this->productoIdProducto = ia_sqlKeyValue("SELECT /*$method*/ producto_general_id, producto FROM producto_general");
        $this->colorIdColor = ia_sqlKeyValue("SELECT /*$method*/ color_id, color FROM color");
    }

    public function notaAdd($notaBodega, $items):array {
        return $this->nota2Diario($notaBodega, $items, '+');
    }

    public function notaQuita($notaBodega, $items):array {
        return $this->nota2Diario($notaBodega, $items, '-');
    }

    protected function nota2Diario(array $notaBodega, array $items, string $sumaResta):array {
        if(strcasecmp('Cancelacion', $notaBodega['tipo']) === 0 || strcasecmp('Borrado', $notaBodega['tipo']) === 0)
            return [];
        global $gStrIt;
        $method = __METHOD__;
        $bed = $this->baseRecord($notaBodega);
        $incialWhere = " fecha < " . strit($notaBodega['fecha']) . " AND " .
            $this->builderSql->where([
                'bodega_id' => $notaBodega['bodega_id'],
                'producto_general_id' => $notaBodega['producto_general_id'],
            ]);
        $nextWhere = " fecha > " . strit($notaBodega['fecha']) . " AND " .
            $this->builderSql->where([
                'bodega_id' => $notaBodega['bodega_id'],
                'producto_general_id' => $notaBodega['producto_general_id'],
            ]);
        if(strcasecmp('Correccion', $notaBodega['tipo']) === 0) {
            if($notaBodega['por_sistema'] == '1') {
                $notaBodega['entrada_salida'] = 'Entrada';
            }
        }
        $prefijo = strtolower($notaBodega['entrada_salida']) . '_' . $this->columna($notaBodega);
        $columnaRollos = $prefijo . '_rollos';
        $columnaQuantity = $prefijo . '_quantity';

        $queries = [];
        foreach($items as $color) {
            $bed[$columnaRollos] = $rollos = $color['rollos'];
            $bed[$columnaQuantity] = $quantity = $color['quantity'];
            $color_id = $color['color_id'];
            $bed['color_id'] = $color_id;
            $bed['color'] = $this->colorIdColor[$color_id] ?? 'N/A';
            $signoFixInicial =strcasecmp('Entrada', $notaBodega['entrada_salida']) ? '-' : '+';
            $inicial = ia_singleton(
                "SELECT  /*$method Última existencia registrada */ existencia_rollos, existencia_quantity 
                 FROM bodega_existencia_diaria
                 WHERE $incialWhere AND color_id = {$gStrIt($color_id)}
                 ORDER BY fecha DESC LIMIT 1");
            $bed['existencia_rollos_inicial'] = $inicial['existencia_rollos'] ?? '0.00';
            $bed['existencia_quantity_inicial'] = $inicial['existencia_quantity'] ?? '0.00';

            $queries[] = $this->builderSql->insert('bodega_existencia_diaria', $bed,
                onUpdate: $this->fixSign(
                    "$columnaRollos = $columnaRollos $sumaResta {$gStrIt($rollos)}, " .
                    "$columnaQuantity = $columnaQuantity $sumaResta {$gStrIt($quantity)}"
                ),
                comment: "$method Registra Existencia Diaria"
            );
            $queries[] = $this->fixSign(
                "UPDATE /*$method inventario inicial fechas posteriores */ bodega_existencia_diaria SET 
                  existencia_rollos_inicial = existencia_rollos_inicial $signoFixInicial {$gStrIt($rollos)},
                  existencia_quantity_inicial = existencia_quantity_inicial $signoFixInicial {$gStrIt($quantity)}
                WHERE $nextWhere AND color_id = {$gStrIt($color_id)}"
            );
            echo "</ul>";
        }
        return $queries;
    }

    public function sqlResetItem($resetInfo):array {
        global $gStrIt;
        $method = __METHOD__;
        if(empty($resetInfo['fecha']))
            $resetInfo['fecha'] = Date('Y-m-d');


        $bodega_id = $resetInfo['bodega_id'];
        $producto_general_id = $resetInfo['producto_general_id'];
        $color_id = $resetInfo['color_id'];

        $sqlExistenciaIncial =
            "SELECT  /*$method Última existencia registrada */ fecha, existencia_rollos, existencia_quantity 
                 FROM bodega_existencia_diaria
                 WHERE fecha < {$gStrIt($resetInfo['fecha'])} AND " .
            $this->builderSql->where([
                'bodega_id' => $bodega_id, 'producto_general_id' => $producto_general_id, 'color_id' => $color_id
            ]) . "
                 ORDER BY fecha DESC LIMIT 1";

        $inicial = ia_singletonfull($sqlExistenciaIncial, '0');


        $bodega_existencia_diaria = [
            'fecha' => $resetInfo['fecha'],
            'bodega_id' => $bodega_id,
            'producto_general_id' => $producto_general_id,
            'color_id' => $color_id,
            'grupo' => $this->bodegaIdGrupo[$bodega_id] ?? 'N/A',
            'bodega' => $this->bodegaIdBodega[$bodega_id] ?? 'N/A',
            'producto' => $this->productoIdProducto[$producto_general_id] ?? 'N/A',
            'color' => $this->colorIdColor[$color_id] ?? 'N/A',

            'existencia_rollos_inicial' => $inicial['existencia_rollos'] ?? '0.00',
            'existencia_quantity_inicial' => $inicial['existencia_quantity'] ?? '0.00',

            'en_remate' => $resetInfo['en_remate'] ?? 'No',
            'es_saldo' => $resetInfo['es_saldo'] ?? 'No',
            'lento' => $resetInfo['lento'] ?? 'No',
            'super_lento' => $resetInfo['super_lento'] ?? 'No',
            'reset_rollos' => $resetInfo['rollos'] ?? '0',
            'reset_quantity' => $resetInfo['quantity'] ?? '0.00',
            'usuario_por' => $_SESSION['usuario'],
        ];

        return
            [
                $this->builderSql->insert('bodega_existencia_diaria', $bodega_existencia_diaria) .
                    " /*$method*/ ON DUPLICATE KEY UPDATE 
                        reset_rollos = reset_rollos + {$gStrIt($resetInfo['rollos'])},
                        reset_quantity = reset_quantity + {$gStrIt($resetInfo['quantity'])},
                        usuario_por= {$gStrIt($_SESSION['usuario'])}",

                "UPDATE /*$method arregla existencia inicial en el futuro */ bodega_existencia_diaria SET
                    existencia_rollos_inicial = existencia_rollos_inicial + {$gStrIt($resetInfo['rollos'])},
                    existencia_quantity_inicial = existencia_quantity_inicial + {$gStrIt($resetInfo['quantity'])}
                    WHERE fecha > " .strit($bodega_existencia_diaria['fecha']) . " AND " .
                    $this->builderSql->where(
                        ['bodega_id' => $bodega_id, 'producto_general_id' => $producto_general_id, 'color_id' => $color_id
                        ]),
            ];

    }

    protected function baseRecord(array $notaBodega):array {
        $bodega_id = $notaBodega['bodega_id'];
        return [
            'fecha' => $notaBodega['fecha'],
            'bodega_id' => $bodega_id,
            'grupo' => $this->bodegaIdGrupo[$bodega_id] ?? 'N/A',
            'bodega' => $this->bodegaIdBodega[$bodega_id] ?? 'N/A',
            'producto_general_id' => $notaBodega['producto_general_id'],
            'producto' => $this->productoIdProducto[$notaBodega['producto_general_id']] ?? 'N/A',
            'usuario_por' => $_SESSION['usuario'],
        ];
    }

    protected function columna(array $notaBodega):string {
        return
            match (strtoupper( $notaBodega['tipo'] )) {
                'TRASLADO', 'TRASPASO' => "traslado",
                'MOVIMIENTO', 'VENTA CLIENTE', 'VENTA' => 'venta',
                'DEVOLUCION', 'DEVOLUCION CLIENTE' => 'devolucion',
                'CONTAINER', 'IMPORTACION' => 'compra',
                'DEVOLUCION FABRICANTE' => 'rechazado',
                default => 'correccion'
            };
    }

    protected function fixSign(string $update):string {
        return str_replace("'+", "'", $update);
    }

}

//$clientes_cheque = obtenCatalogo('cheque_cliente_id');
// echo "<pre>" . print_r($clientes_cheque, true) . "</pre>";

function reprocesaBED_01()
{
    echo "<h1>reprocesaBED_01()</h1>";
    ia_query("DELETE FROM bodega_existencia_diaria");
    $notas = ia_sqlArray("SELECT * FROM nota_bodega WHERE tipo NOT IN ('Cancelacion', 'Borrado')
                          ORDER BY alta_db DESC", 'nota_bodega_id');
    foreach ($notas as $nota_bodega_id => $nota) {
        $notaBodega = new NotaBodega();
        $items = $notaBodega->getItems($nota_bodega_id);
        $es_salida = strcasecmp($nota['entrada_salida'], 'Salida') === 0;
        if($es_salida) {
            $signo = '-';
            $campo_ultima_entrada_salida = 'ultima_salida';
            $campo_ultima_entrada_salida_rollos = 'ultima_salida_rollos';
            $campo_ultima_entrada_salida_quantity = 'ultima_salida_quantity';
        } else {
            $signo = '+';
            $campo_ultima_entrada_salida = 'ultima_entrada';
            $campo_ultima_entrada_salida_rollos = 'ultima_entrada_rollos';
            $campo_ultima_entrada_salida_quantity = 'ultima_entrada_quantity';
        }
        $bodegaExistenciaDiaria = new BodegaExistenciaDiaria(false);
        foreach ($items as $i => $item) {
            $producto = "$item[producto] $item[color]";
            $bodega_id = $nota['bodega_id'];
            $producto_id = $nota['producto_general_id'];
            $color_id = $item['color_id'];
            $producto_Bodega_id = $bodega_id . "_".$producto_id."_".$color_id;
            $productoBodega = [
                'producto' => $producto,
                'bodega_id' => $nota['bodega_id'],
                'producto_general_id' => $nota['producto_general_id'],
                'color_id' => $item['color_id'],
                'descripcion' => '',
                'remarks' => '',
                'remarks_internos' => '',
                'alta_db' => 'NOW()',
                'alta_por' => 'system',
            ];

            //$insert = $sqlBuilder->insert('producto_bodega', $productoBodega, false,
            //    "bodega_id = " . strit($bodega_id));
            //ia_query($insert);

            ia_query($bodegaExistenciaDiaria->sqlRegistraItem($nota, $item));
            ia_query($bodegaExistenciaDiaria->updateRegistraExistenciaInicial($nota, $item));

            $update = "UPDATE  /* ACTUALIZA producto_bodega **/ producto_bodega set 
                   existencia_rollos = existencia_rollos $signo $item[rollos], 
                   existencia_quantity = existencia_quantity $signo $item[quantity], 
                    $campo_ultima_entrada_salida = " . strit($nota['fecha']) . ",
                    $campo_ultima_entrada_salida_rollos = $item[rollos],
                    $campo_ultima_entrada_salida_quantity = $item[quantity]
                    WHERE producto_bodega_id = ". strit($producto_Bodega_id);
            //ia_query($update);
        }
    }
    echo "<h3>TERMINE</h3>";
}


echo "<hr><h1>Ist Done</h1><hr>";
ia_errores_a_dime();
echo ia_report_status_collapsable();
?>
https://gist.github.com/vineeth-pappu/2b57bb4eed3900af463cadadd7cee93c

<svg id="game" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 400" overflow="visible">
    <linearGradient id="ArcGradient" >
        <stop offset="0"  stop-color="#fff" stop-opacity=".2"/>
        <stop offset="50%" stop-color="#fff" stop-opacity="0"/>
    </linearGradient>
    <path id="arc" fill="none" stroke="url(#ArcGradient)" stroke-width="4" d="M100,250c250-400,550-400,800,0"  pointer-events="none"/>
    <defs>
        <g id="arrow">
            <line x2="60" fill="none" stroke="#888" stroke-width="2" />
            <polygon fill="#888" points="64 0 58 2 56 0 58 -2" />
            <polygon fill="#88ce02" points="2 -3 -4 -3 -1 0 -4 3 2 3 5 0" />
        </g>
    </defs>
    <g id="target">
        <path fill="#FFF" d="M924.2,274.2c-21.5,21.5-45.9,19.9-52,3.2c-4.4-12.1,2.4-29.2,14.2-41c11.8-11.8,29-18.6,41-14.2 C944.1,228.3,945.7,252.8,924.2,274.2z" />
        <path fill="#F4531C" d="M915.8,265.8c-14.1,14.1-30.8,14.6-36,4.1c-4.1-8.3,0.5-21.3,9.7-30.5s22.2-13.8,30.5-9.7 C930.4,235,929.9,251.7,915.8,265.8z" />
        <path fill="#FFF" d="M908.9,258.9c-8,8-17.9,9.2-21.6,3.5c-3.2-4.9-0.5-13.4,5.6-19.5c6.1-6.1,14.6-8.8,19.5-5.6 C918.1,241,916.9,250.9,908.9,258.9z" />
        <path fill="#F4531C" d="M903.2,253.2c-2.9,2.9-6.7,3.6-8.3,1.7c-1.5-1.8-0.6-5.4,2-8c2.6-2.6,6.2-3.6,8-2 C906.8,246.5,906.1,250.2,903.2,253.2z" />
    </g>
    <g id="bow" fill="none" stroke-linecap="round" vector-effect="non-scaling-stroke" pointer-events="none">
        <polyline fill="none" stroke="#ddd" stroke-linecap="round" points="88,200 88,250 88,300"/>
        <path fill="none" stroke="#88ce02" stroke-width="3" stroke-linecap="round" d="M88,300 c0-10.1,12-25.1,12-50s-12-39.9-12-50"/>
    </g>
    <g class="arrow-angle"><use x="100" y="250" xlink:href="#arrow"/></g>
    <clipPath id="mask">
        <polygon opacity=".5" points="0,0 1500,0 1500,200 970,290 950,240 925,220 875,280 890,295 920,310 0,350" pointer-events="none"/>
    </clipPath>
    <g class="arrows" clip-path="url(#mask)"  pointer-events="none">
    </g>
    <g class="miss" fill="#aaa" opacity="0" transform="translate(0, 100)">
        <path d="M358 194L363 118 386 120 400 153 416 121 440 119 446 203 419 212 416 163 401 180 380 160 381 204"/>
        <path d="M450 120L458 200 475 192 474 121"/>
        <path d="M537 118L487 118 485 160 515 162 509 177 482 171 482 193 529 199 538 148 501 146 508 133 537 137"/>
        <path d="M540 202L543 178 570 186 569 168 544 167 546 122 590 116 586 142 561 140 560 152 586 153 586 205"/>
        <path d="M595,215l5-23l31,0l-5,29L595,215z M627,176l13-70l-41-0l-0,70L627,176z"/>
    </g>
    <g class="bullseye" fill="#F4531C" opacity="0">
        <path d="M322,159l15-21l-27-13l-32,13l15,71l41-14l7-32L322,159z M292,142h20l3,8l-16,8 L292,142z M321,182l-18,9l-4-18l23-2V182z"/>
        <path d="M340 131L359 125 362 169 381 167 386 123 405 129 392 183 351 186z"/>
        <path d="M413 119L402 188 450 196 454 175 422 175 438 120z"/>
        <path d="M432 167L454 169 466 154 451 151 478 115 453 113z"/>
        <path d="M524 109L492 112 466 148 487 155 491 172 464 167 463 184 502 191 513 143 487 141 496 125 517 126z"/>
        <path d="M537 114L512 189 558 199 566 174 533 175 539 162 553 164 558 150 543 145 547 134 566 148 575 124z"/>
        <path d="M577 118L587 158 570 198 587 204 626 118 606 118 598 141 590 112z"/>
        <path d="M635 122L599 198 643 207 649 188 624 188 630 170 639 178 645 162 637 158 649 143 662 151 670 134z"/>
        <path d="M649,220l4-21l28,4l-6,25L649,220z M681,191l40-79l-35-8L659,184L681,191z"/>
    </g>
    <g class="hit" fill="#ffcc00" opacity="0" transform="translate(180, -80) rotate(12) ">
        <path d="M383 114L385 195 407 191 406 160 422 155 418 191 436 189 444 112 423 119 422 141 407 146 400 113"/>
        <path d="M449 185L453 113 477 112 464 186"/>
        <path d="M486 113L484 130 506 130 481 188 506 187 520 131 540 135 545 119"/>
        <path d="M526,195l5-20l22,5l-9,16L526,195z M558,164l32-44l-35-9l-19,51L558,164z"/>
    </g>
    <!-- 	<line x1= "875", y1= "280", x2= "925", y2= "220" stroke="red"/>
    <circle class="point" r="7" fill="purple" opacity=".4"/> -->
</svg>


</body>
</html>