<?php

function request($keyDefault) {
    $post = $_REQUEST ?? json_decode(file_get_contents("php://input"), TRUE);
    foreach($keyDefault as $key => &$value)
        if(array_key_exists($key, $post))
            $value = strim($post[$key]);
    return $keyDefault;
}



function strit($str):string|array {
    if($str === null) {
        return 'NULL';
    }
    if(is_array($str)) {
        foreach($str as &$d)
            $d = strit($d);
        return $str;
    }
    return "'".str_replace(
      array("\\",chr(8),chr(0),chr(26),chr(27)),
      array("\\\\",'','','',''),
      str_replace("'","''", "$str")
      )."'";
}

/**
 * " AND color <> {$gStrIt($last_color)}" en vez de " AND color <> " . strit($last_color);
 */
global $gStrIt; $gStrIt  = function($s) {return strit($s); };
