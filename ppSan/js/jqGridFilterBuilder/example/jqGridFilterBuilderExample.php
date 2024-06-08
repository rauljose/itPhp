<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <title>jqGridFilterBuilder Example</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <style>
        BODY {margin:0.5em 1em}
        .cen {margin:auto;text-align:center}
        H1 {margin-bottom:0.1em}
        .nota {font-size: small; color:silver; font-weight: normal;margin:0;padding:0}
        .tabla {border-collapse: collapse;margin-top:1em;margin-bottom:3em}
        .tabla TH {text-align: center;vertical-align: top;white-space: pre;border:1px silver solid;padding:1em}
        .tabla TD {text-align: left;vertical-align: top;white-space: pre-wrap;border:1px silver solid;padding:0.1em 1em 0.5em 1em}

        #opBody TD{text-align: center}
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="../jqGridFilterBuilder.js"></script>
</head>
<body>
    <div class="cen">
        <h1>jqGridFilterBuilder.js example</h1>
        <i>Builder for filling jqGrid.postData.filter.<br>
            <a href="#op">jqGrid Filter Operators (op) cheat sheet</a>
        </i>
    </div>

    <table class="tabla cen">
        <thead>
            <tr>
                <th>Code<br><p style="font-weight:normal;text-align: left;margin:0;padding:0">let f = jqGridFilterBuilder;</p></th>
                <th>Js object returned<p class="nota">* ver la consola</p></th>
                <th>Filter2Where.php</th>
            </tr>
        </thead>
        <tbody id="examples"></tbody>
    </table>


    <h1 class="cen" id="op">jqGrid Filter Operators</h1>
    <table class="tabla cen">
        <thead>
            <tr>
                <th>Op</th><th>In Sql</th><th>Note</th>
            </tr>
        </thead>
        <tbody id="opBody">
            <tr><td>eq<td>=<td></tr>
            <tr><td>ge<td>&gt;=<td></tr>
            <tr><td>gt<td>&gt;<td></tr>
            <tr><td>le<td>&lt;=<td></tr>
            <tr><td>lt<td>&lt;<td></tr>
            <tr><td>ne<td>&lt;&gt;<td></tr>
            <tr><td>bt<td>BETWEEN<td>Values in array or coma separated. </tr>
            <tr><td>nu<td>IS NULL<td></tr>
            <tr><td>nn<td>IS NOT NULL<td></tr>
            <tr><td>in<td>IN<td>Values in array or  coma separated: value1,value2,...</tr>
            <tr><td>ni<td>NOT IN<td>Values in array or  coma separated: value1,value2,...</tr>


            <tr><td>bw<td> LIKE 'asdf%'<td>begins with</td></tr>
            <tr><td>bn<td> NOT LIKE 'asdf%'<td>not begins with</td></tr>


            <tr><td>cn<td> LIKE '%asdf%'<br>
                    MATCH(field) AGAINST('+word1 +word2' IN BOOLEAN MODE)
                <td>contains<br>If filter2where defines field has full text index, respetando min y max token size</td></tr>
            <tr><td>nc<td> NOT LIKE '%asdf%'<td>not contains</td></tr>
            <tr><td>ma<td>
                    MATCH(field) AGAINST('+word1 +word2' IN BOOLEAN MODE)
                    <BR>LIKE '%asd%'# en menos de mi token o mas de max token
                <td>full text search<br>Usa fulltext Index sin checar si esta definido</td></tr>
            <tr><td>ew<td> LIKE '%asdf'<td>ends with</td></tr>
            <tr><td>en<td> NOT LIKE '%asdf'<td>not ends</td></tr>


        </tbody>
    </table>

    <script>
        var examples= [
            `f.OR(
                        ['col', 'eq', 1], ['col1', 'in', ['Si', 'No se']],
                        f.NOT( f.XOR(['col2', 'ge', 2], ['col3', 'gt', 3]) ),
                        f.AND(['col4', 'lt', 20], ['col5', 'le', 30])
                    )`,

            `// Haz un filtro para el where: tabla.col='Mc'Donalds' AND tabla2.col_a IN (3,5,7)
            f.AND(
                {field:'tabla1.col', op:'eq', data:"Mc'Donalds"},
                ["\`tab\`la2\`.col_a", 'in', ['3', 5, 7] ]
            )`,

            `// Haz un filtro: (tableSqlComands.update=1 OR a.cola=2) AND (col2 >= 2 AND col3 <>> 3)
            f.OR(
                ['tableSqlComands.update', 'eq', 1], {field:'a.cola', op:'eq', data:2},
                f.AND(['col2', 'ge', 2], ['col3', 'ne', 3])
            )`,

            `// Haz un filtro: (col = 1 OR cola=2) OR NOT(col2 >=2 XOR col3=3)
            f.OR(
                ['col', 'eq', 1], ['cola', 'eq', ['Si', 'No se']],
                f.NOT( f.XOR(['col2', 'ge', 2], ['col3', 'eq', 3]) )
            )`,


            `// filtro de Not
            f.NOT( f.XOR(['col2', 'ge', 2], ['col3', 'eq', 3]) ) `,

            `// fullText search
                f.OR(['fullTextCol','cn','buscado'], ['nonFullTextCol','cn','buscado']) `,

            `// fullText search
                f.OR(['fullTextCol','ma','tv Samsunger red']) `,

            `// junta 2 filtros con OR
            f.filterOrFilter(
                f.AND(['colAnd1','eq',1],['colAnd2','eq',2]),
                f.AND(['colAnd3','eq',3],['colAnd4','eq',4]),
            ) `,

            `// junta 2 filtros con AND
            f.filterAndFilter(
                f.OR(['colOr1','eq',1],['colOr2','eq',2]),
                f.OR(['colOr3','eq',3],['colOr4','eq',4]),
            ) `,

        ];
    </script>

    <script>
        ta(examples);
        function ta(a) {
            let f = jqGridFilterBuilder;
            console.log("jqGridFilterBuilder examples ____________________");
            for(let i=0, len=a.length; i < len; ++i) {
                let filter = examples[i],
                    result = eval(filter),
                    jsonResult = JSON.stringify(result, null, 8);
                $("#examples").append(
                    `<tr><td>${filter}<td>${jsonResult}<td id='Filter2Where${i}'>`
                );
                filter2Where(filter, result, '#Filter2Where'+i);
            }
            function filter2Where(fText, fi, id) {
                $.ajax({
                    url:'jqGridFilterBuilderAjax.php',
                    method:'POST',
                    data:{filter:fi},
                })
                .done(function(response) {
                    $(id).html(response);
                    console.log("  "+fText, fi);
                    console.log("   where: ", response);
                })
                .fail(function(){
                    $(id).text('¡Error!');
                    console.log("  "+fText, fi);
                    console.log("   where: ", "Error!!");
                })
                ;
            }
        }

    </script>

</body>
</html>