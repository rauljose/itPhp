<?php
$headers = [];
foreach ($_SERVER as $name => $value)
    if (str_starts_with($name, 'HTTP_')) {
        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
    }

echo "<hr>";
echo "<pre class='code'>";
echo "<li>request method: $_SERVER[REQUEST_METHOD]";
echo "<li>query string" . str_replace('?' . ($_SERVER['QUERY_STRING'] ?? ''), '', $_SERVER['REQUEST_URI']);
echo "<li>" . $_SERVER['QUERY_STRING'];
echo "<li>" . ToCode::variable("http_headers", $headers);
echo "<li>" . ToCode::variable("server", $_SERVER);

echo "<hr><li>" . ToCode::variable("_GET", $_GET ?? []);
echo "<li>" . ToCode::variable("GET", getRealInput('GET'));

echo "<hr><li>" . ToCode::variable("_POST", $_POST ?? []);
echo "<li>" . ToCode::variable("POST", getRealInput('POST'));


function getRealInput(
    #[\JetBrains\PhpStorm\ExpectedValues([ 'POST', 'GET'])]
    string $source
):array {
    $parameters = [];
    if($source === 'GET') {
        parse_str($_SERVER['QUERY_STRING'] ?? '', $parameters);
        return $parameters;
    }
    $from = file_get_contents("php://input");
    if(empty($from))
        return [];
    $contentType = $_SERVER['CONTENT_TYPE'] ?? ''; // application/x-www-form-urlencoded (as long as it's not multipart/form-data-encoded).
    //$urlEncoded = str_contains($contentType, 'urlencoded');
    if(str_contains($contentType, 'json')) {
        return json_decode(file_get_contents("php://input"), true);
    }
    // application/xml
    if(str_contains($contentType, 'urlencoded')) {
        parse_str($from ?? '', $parameters);
        return $parameters;
    }

    return [];

}

