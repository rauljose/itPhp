<?php
/** @noinspection PhpMissingParamTypeInspection */

/** @noinspection PhpRedundantOptionalArgumentInspection */

namespace It\Xtra;

class itString {

    public static function titleCase($str):string {
        $str = ucwords($str);
        $notCapitalized = [
          "a", "al", "de", "del", "el", "ella", "la", "las", "los", "un", "una", "uno", "unos", "unas",
          "y", "e", "ni", "o", "u", "pero", "mas", "sino", "aunque", "ó", "más", "ante", "bajo", "cabe",
          "con", "contra", "desde", "en", "entre", "hacia", "hasta", "para", "por", "según", "segun", "sin",
          "sobre", "tras", "me", "te", "se", "nos", "os", "lo", "le", "les",
          "an", "the", "and", "but", "for", "nor", "or", "so", "yet", "at", "by", "in", "of", "on", "to", "up",
          "with", "as", "from", "into", "near", "over", "past", "than", "via"
        ];
        foreach($notCapitalized as $w) {
            $str = preg_replace_callback('/\b' . preg_quote($w, '/') . '\b/iu', function($matches) {
                return mb_strtolower($matches[0]);
            }, $str);
        }
        return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
    }

}