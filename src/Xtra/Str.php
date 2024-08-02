<?php
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpRedundantOptionalArgumentInspection */
/** @noinspection PhpUnused */

namespace It\Xtra;

use Stringable;

final class str {

    public static function sTrim($str):string|array {
        if($str === null)
            return '';
        if(is_array($str)) {
            foreach($str as &$d)
                $d = self::sTrim($d);
            return $str;
        }
        if(!is_string($str))
            $str = "$str";
        $s1 = preg_replace('/[\pZ\pC]/muS',' ',$str);
        if(preg_last_error()) {
            $s1 = preg_replace('/[\pZ\pC]/muS',' ',  iconv("UTF-8","UTF-8//IGNORE",$str));
            if(preg_last_error())
                return trim(preg_replace('/ {2,}/mS',' ',$str));
        }
        return trim(preg_replace('/ {2,}/muS',' ',$s1));
    }

    /**
     * @param string|\Stringable $str
     * @return string
     */
    public static function titleCase($str):string {
        $notCapitalized = [
          'a', 'al', 'de', 'del', 'el', 'ella', 'la', 'las', 'los', 'un', 'una', 'uno', 'unos', 'unas',
          'y', 'e', 'ni', 'o', 'u', 'pero', 'mas', 'sino', 'aunque', 'ó', 'más', 'ante', 'bajo', 'cabe',
          'con', 'contra', 'desde', 'en', 'entre', 'hacia', 'hasta', 'para', 'por', 'según', 'segun', 'sin',
          'sobre', 'tras', 'me', 'te', 'se', 'nos', 'os', 'lo', 'le', 'les',
          'an', 'the', 'and', 'but', 'for', 'nor', 'or', 'so', 'yet', 'at', 'by', 'in', 'of', 'on', 'to', 'up',
          'with', 'as', 'from', 'into', 'near', 'over', 'past', 'than', 'via'
        ];
        $string = '';
        foreach($notCapitalized as $w) {
            $string = preg_replace_callback('/\b' . preg_quote($w, '/') . '\b/iu', function($matches) {
                return mb_strtolower($matches[0]);
            }, ucwords((string)$str));
        }
        return mb_strtoupper(mb_substr($string ?? '', 0, 1)) . mb_substr($string ?? '', 1);
    }

}
