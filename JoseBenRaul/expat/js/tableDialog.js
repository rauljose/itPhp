
function json2tableDialog(title, data) {
    if($("#reporteDialog").length) {
        $("#reporteDialog").remove();
        return;
    }
    closeAllDialog();

    //creando tabla
    let $table = $('<table></table>', {class: 'laTabla', id: 'reporteDialogTable'});
    //creando caption
    let $caption = $('<caption></caption>', {text: title, style:"text-align:center;font-size:1.2em;font-weight:bold;color:#00F;"});
    $table.append($caption);

    //creando el thead
    let $thead = $('<thead></thead>');
    let $tr_h = $('<tr></tr>');
    for(let colTitle of Object.keys( data[0] || {}))
        $tr_h.append( $('<th></th>', {text: colTitle}) );
    $thead.append($tr_h);
    $table.append($thead);

    //creando el body
    let clasesByValue = {si:"cen", no:"cen", active:"cen", inactive:"cen", usd:"cen", pesos:"cen" };
    let $tbody = $('<tbody></tbody>');
    for(let row of data) {
        let $tr = $('<tr></tr>');
        for(let col in row)
            if(row.hasOwnProperty(col)) {
                let clase = "izq";
                let value = row[col];
                if(clasesByValue.hasOwnProperty(value.toLocaleLowerCase() )) {
                    clase = clasesByValue[value];
                } else if(!isNaN(value)) {
                    value = number_format(value, 2);
                    clase = "der";
                }
                $tr.append( $('<td></td>', {text: value, class: clase}) );
            }
        $tbody.append($tr);
    }
    $table.append($tbody);

    let $table_container = $('<div></div>', {style:"width:fit-content;max-width:90%;margin:0.5em auto;text-align:center"});
    $table_container.append("<div id='reporteDialogToolbox'></div>");

    $table_container.append($table);

    let $body_dialogo= $('<div></div>', {style:"margin:0 auto;text-align:center"});
    $body_dialogo.append($table_container);
    let $dialogo= $('<div></div>', {title: title, id: 'reporteDialog'});
    $dialogo.append($body_dialogo);
    $("BODY").append($dialogo);
    $dialogo = $("#reporteDialog");
    $dialogo.dialog({
        closeOnEscape: true,
        width:800,
        height:600,
        open: function() {$("#reporteDialogToolbox").append(exporter.toolBar("#reporteDialogTable" ));},
        close: function() {$(this).remove();},
    });
}
function reportesSelecciona() {
    if($("#reportesSelecciona").length) {
        $("#reportesSelecciona").remove();
        return;
    }
    closeAllDialog();
    let queReporte = `<div style="margin:auto;text-align: center">
            <ul style="line-height: 2.2em;text-align: left">
                <li><input type="radio" name="reportes" value="reporteProductosOcultosPorProducto"
                    id="reporteProductosOcultosPorProducto"><label for="reporteProductosOcultosPorProducto">Relación Usuarios vs Productos Ocultos, por Usuario</label>
                </li>
                <li><input type="radio" name="reportes" value="reporteProductosOcultosPorUsuario"
                    id="reporteProductosOcultosPorUsuario"><label for="reporteProductosOcultosPorUsuario">Relación Productos Ocultos vs Usuarios, por Producto</label>
                </li>
            </ul>
            </div>`;
    let $dialog = $("<div id='reportesSelecciona'>");
    $dialog.append(queReporte);
    $dialog.dialog({
        title: "Seleccione el reporte deseado y click en Reportar",
        closeOnEscape: true,
        width:600,
        close: function() {$(this).remove();},
        buttons: [
            {text: "Reportar",icon:"ui-icon-script",
                click: function() {
                    let accion = valueSelected = $("input[name='reportes']:checked").val();
                    if(typeof accion === 'undefined') {
                        return;
                    }
                    let title = $(`LABEL[for='${accion}']`).text();
                    reportesReporta(accion, title);
                    $(this).dialog("close");
                }
            },
            {text:'Cancelar',icon:'ui-icon-cancel', click:function(){$(this).dialog("close");} }
        ],
    });
}
function reportesReporta(accion, title) {
    $.ajax({
        url: 'ajax/producto_general_acciones.php',
        method: 'GET',
        cache: false,
        dataType: 'json',
        data: {accion},
    })
        .done(function(data, textStatus, jqXHR) {
            if(!data.status) {
                ia.alertError(data.message || "Error inesperado, intente más tarde.", "Error");
                console.log("ajax status wrong: " + this.url, data);
                return;
            }
            json2tableDialog(title, data.reporte || []);
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            try {
                if(typeof jqXHR.responseJSON === 'object') {
                    ia.alertError(jqXHR.responseJSON.message || "Error inesperado, intente más tarde.", "Error");
                } else
                    ia.alertError(`Error inesperado, intente más tarde.<div>${textStatus}</div><div>${errorThrown}</div>`, "Error", true);
                console.log("Ajax error:", this);
            } catch(error) {
                console.log("ajax.fail message failed", error)
            }
            console.log('    ajax.fail: ' + this.url, arguments);
        });
}

const reportTable = {
    json2tableDialog: function(title, data) {

        //@TODO close this if exists
        //@TODO close other dialogs

        //creando tabla
        let $table = $('<table></table>', {class: 'laTabla', id: 'reporteDialogTable'});
        //creando caption
        let $caption = $('<caption></caption>', {text: title, style:"text-align:center;font-size:1.2em;font-weight:bold;color:#00F;"});
        $table.append($caption);

        //creando el thead
        let $thead = $('<thead></thead>');
        let $tr_h = $('<tr></tr>');
        for(let colTitle of Object.keys( data[0] || {}))
            $tr_h.append( $('<th></th>', {text: colTitle}) );
        $thead.append($tr_h);
        $table.append($thead);

        //creando el body
        let clases = {si:"cen", no:"cen", active:"cen", inactive:"cen", usd:"cen", pesos:"cen" };
        let $tbody = $('<tbody></tbody>');
        for(let row of data) {
            let $tr = $('<tr></tr>');
            for(let col in row)
                if(row.hasOwnProperty(col)) {
                    let clase = "izq";
                    let value = row[col];
                    if(clases.hasOwnProperty(value.toLocaleLowerCase() )) {
                        clase = clases[value];
                    } else if(!isNaN(value)) {
                        value = number_format(value, 2);
                        clase = "der";
                    }
                    $tr.append( $('<td></td>', {text: value, class: clase}) );
                }
            $tbody.append($tr);
        }
        $table.append($tbody);

        let $table_container = $('<div></div>', {style:"width:fit-content;max-width:90%;margin:0.5em auto;text-align:center"});
        $table_container.append("<div id='reporteDialogToolbox'></div>");

        $table_container.append($table);

        let $body_dialogo= $('<div></div>', {style:"margin:0 auto;text-align:center"});
        $body_dialogo.append($table_container);
        let $dialogo= $('<div></div>', {title: title, id: 'reporteDialog'});
        $dialogo.append($body_dialogo);
        $("BODY").append($dialogo);
        $dialogo = $("#reporteDialog");
        $dialogo.dialog({
            closeOnEscape: true,
            width:800,
            height:600,
            open: function() {
                $("#reporteDialogToolbox").append(exporter.toolBar("#reporteDialogTable" ));
                $("#reporteDialogToolbox").append(expat.toolbar("#reporteDialogTable"));
                expat.init();

            },
            close: function() {$(this).remove();},
        });
    },
}
const expat = {
    toolbar:function(target) {
        let toolbar = `
                <button type='button' id="reporteDialogCopy" title="Copy" data-clipboard-target='#${target}'>📋</button
                ><button type='button' id="reporteDialogPrint" title="Print" data-target='#${target}'>🖨</button>
                ><button type='button' id="reporteDialogPDF" title="PDF" data-target='#${target}'>PDF</button>
                ><button type='button' id="reporteDialogXLSX" title="Excel" data-target='#${target}'>Excel</button>
                ><button type='button' id="reporteDialogCopyImage" title="Copy as Image" data-target='#${target}'>🖼</button>
                ><button type='button' id="reporteDialogImage" title="Download as Image" data-target='#${target}'>🖨</button>
           `;
        /*
        <span class="material-symbols-outlined">print</span>
        <span class="material-symbols-outlined">content_copy</span>
        <span class="material-symbols-outlined">image</span>
        <span class="material-symbols-outlined">download</span>
        <span class="material-symbols-outlined">picture_as_pdf</span>
         */
        return toolbar;
    },
    init:function() {
        this.initCopy("#reporteDialogCopy");
    },
    initCopy:function(querySelector) {
        //@TODO feedback
        let c = new ClipboardJS(querySelector);
        c.on('success', function (e) {
            e.clearSelection();
            $("#reporteDialogCopyMsg").html("¡Copiado!")
            // $(e.trigger).notify("¡Texto Copiado!",{ position:'left', globalPosition:'bottom left',style: 'copiado', className: 'supercopiado',autoHideDelay: 2000, });
        });
        c.on('error', function(e) {
            $("#reporteDialogCopyMsg", $(e)).html("¡Error!")
        });
    },
    print: function(querySelector, addCssLinks) {
        //@TODO usar querySelectorAll
        //@TODO agregar addCssLinks
        var printWindow = window.open('', '', 'height=900,width=800');
        printWindow.document.write('<html><head><title>Print DIV Content</title><link href="/vitex/css2/iastyles.css" rel="stylesheet" type="text/css"/>' +
            '<style>TABLE {border-collapse: collapse;border:1px silver solid;}TD{padding:0.3em;border:1px silver solid}</style>' +
            '');
        printWindow.document.write('</head><body>');
        var divContents = document.getElementById(querySelector).outerHTML;
        printWindow.document.write(divContents);
        printWindow.document.write('</body></html>');
        printWindow.print();
        printWindow.document.close();
    }
}
