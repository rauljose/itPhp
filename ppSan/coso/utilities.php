<?php
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpUnused */
declare(strict_types = 1);

use JetBrains\PhpStorm\ExpectedValues;



// STRINGS
/**
 * Multibyte String Pad
 *
 * Functionally, the equivalent of the standard str_pad function, but is capable of successfully padding multibyte strings.
 *
 * @param string|Stringable|null $input The string to be padded.
 * @param int $length The length of the resultant padded string.
 * @param string $padding The string to use as padding. Defaults to space.
 * @param int $padType The type of padding. Defaults to STR_PAD_RIGHT.
 * @param string $encoding The encoding to use, defaults to UTF-8.
 *
 * @return string A padded multibyte string.
 *
 * @author Richard A Quadling https://stackoverflow.com/questions/14773072/php-str-pad-unicode-issue
 */
    function mb_str_pad($input, $length, $padding = ' ',
            #[ExpectedVALUES(STR_PAD_LEFT, STR_PAD_BOTH, STR_PAD_RIGHT)] int $padType = STR_PAD_RIGHT,
            $encoding = 'UTF-8'):string
    {
        if($input instanceof Stringable || $input === null) {
            $input = (string)$input;
        }
        if (($paddingRequired = $length - mb_strlen($input, $encoding)) > 0) {
            switch($padType) {
                case STR_PAD_LEFT:
                    return
                        mb_substr(str_repeat($padding, $paddingRequired), 0, $paddingRequired, $encoding).
                        $input;
                case STR_PAD_RIGHT:
                    return
                        $input.
                        mb_substr(str_repeat($padding, $paddingRequired), 0, $paddingRequired, $encoding);
                case STR_PAD_BOTH:
                    $leftPaddingLength = floor($paddingRequired / 2);
                    $rightPaddingLength = $paddingRequired - $leftPaddingLength;
                    return
                        mb_substr(str_repeat($padding, $leftPaddingLength), 0, $leftPaddingLength, $encoding).
                        $input.
                        mb_substr(str_repeat($padding, $rightPaddingLength), 0, $rightPaddingLength, $encoding);
            }
        }
        return $input;
    }

/// BCMATH
    /**
     * Abs para bc math
     *
     * @param string $num
     * @param int $decimals
     * @return string
     */
    function bcabs($num, $decimals):string {
        return bccomp("0", $num, $decimals) >= 0 ?
            bcmul("-1", $num, $decimals) :
            $num;
    }

    /**
     * Redondea y pone comas a un numero en string, number_format para strings
     *
     * @param string|int|float|null|bool $num
     * @param int $decimals
     * @return string el $num con comas y redondeado al $decimals decimales
     */
    function bcformat(string|int|float|null|bool $num, int $decimals=2):string {
        if($num === null || $num === '')
            return '';
        if(!is_numeric($num))
            return $num;
        $num = bcadd("0", (string)$num, $decimals);
        $int = strstr($num, '.', true);
        if($int === false) {
            $int = $num;
            $frac = '';
        } else {
            $frac = strstr($num, '.');
        }
        return preg_replace('/(\d)(?=(\d{3})+(?!\d))/mS', '$1,', $int) . $frac;
    }

// ARRAYS

    function compareCaseInsensitive($a, $b):int {
        if(is_array($a) || is_array($b)) {
            return $a <=> $b;
        }
        if($a instanceof Stringable || $a === null) {
            $a = (string)$a;
        }
        if($b instanceof Stringable || $b === null) {
            $b = (string)$b;
        }
        if(is_string($a) || is_string($b)) {
            return strcasecmp((string)$a, (string)$b);
        }
        return $a <=> $b;
    }

    /**
     * Cambia keys de $array ['producto_id'=>3, 'color'=>4], $newKeys=['color'=>'color.color'] regresa ['producto_id'=>3, 'color.color'=>4]
     *
     * @param array $array ie ['producto_id'=>3, 'color'=>4]
     * @param array $newKeys ie ['color'=>'color.color']
     * @return array ie ['producto_id'=>3, 'color.color'=>4]
     */
    function keyRemap(array $array, array $newKeys):array {
        $reMapped = [];
        foreach($array as $k => $v)
            $reMapped[ $newKeys[$k] ?? $k] = $v;
        return $reMapped;
    }

    /**
     * Regresa $data ordenada por los keys de $keyOrder, de existir, en ese orden y luego el resto de keys de $data
     *
     * @param array<string, mixed> $data
     * @param array<int|string, int|string> $keyOrder
     * @return array<int|string, mixed>
     *
     * @example
     *      keyOrder(['a' => 'la A', 'b' => 'la b', 'm' => 'la m', 'l'=>'la l', 'k'=>'la k'], ['k', 'm', 'l']);
     *          ['k'=>'la k', 'm' => 'la m', 'l'=>'la l','a' => 'la A', 'b' => 'la b']
     */
    function keyOrder(array $data, array $keyOrder):array {
        $order = [];
        foreach($keyOrder as $key)
            if(array_key_exists($key, $data))
                $order[$key] = $data[$key];
        foreach($data as $key => $v)
            if(!array_key_exists($key,$order))
                $order[$key] = $v;
        return $order;
    }

    /**
     * Regresa $data ordenada stable por los keys que no están en $keyOrder y luego los keys de $keyOrder, de existir, en ese orden
     *
     * @param array<int|string, mixed> $data
     * @param array<int|string, int|string> $keyOrder
     * @return array<int|string, mixed>
     * @example
     *      keyOrderEnd(['a' => 'la A', 'b' => 'la b', 'm' => 'la m', 'l'=>'la l', 'k'=>'la k'], ['k', 'm', 'l']);
     *          ['a' => 'la A', 'b' => 'la b', 'k'=>'la k', 'm' => 'la m', 'l'=>'la l']
     */
    function keyOrderEnd(array $data, array $keyOrder):array {
        $order = [];
        $by = array_flip($keyOrder);
        foreach($data as $key => $v)
            if(!array_key_exists($key,$by))
                $order[$key] = $v;
        foreach($keyOrder as $key)
            if(array_key_exists($key, $data))
                $order[$key] = $data[$key];
        return $order;
    }

// SQL
    function getEnums(string $table):array { return ['enumFieldName'.$table=>['enumValue1', 'enumValue2']];}

// FileName
    /**
     * filename_safe()
     *
     * @param string $fileName
     * @return string
     */
    function filename_safe($fileName):string {
        if(str_starts_with($fileName, '.'))
            $fileName='_'.substr($fileName,1);
        return str_replace(
            [
                '|', '>', '<', '&', ' ', '-', '*', '?', '!', '`', '´', '"', "'",
                DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR,"\\","/",'[',']','{','}','(' ,')'
            ]
            ,'_',$fileName); // removeAccents($fileName)
    }

    /**
     * filename_extension()
     *
     * @param mixed $fileName
     * @return string
     */
    function filename_extension($fileName):string  {
        $pos=strrchr($fileName,'.');
        if($pos===FALSE || $pos==$fileName)
            return '';
        return substr($pos,1);
    }

/**
 * usage: global $gFn; echo <<<HEREDOC Hello, {$gFn(ucfirst('world'))} HEREDOC;
 */
global $gFn; $gFn = function ($callable) {return $callable;};

global $gStrIt; $gStrIt  = function($s) {return strit($s); };

/**
 * Regresa la diferencia en frase bonita de las 2 fechas
 *
 * @param string|int $dateTime1 mysqlDate o mysqlDateTime o timestamp
 * @param string|int $dateTime2 mysqlDate o mysqlDateTime o timestamp
 * @param int $maxParts
 * @return string
 */
function fechaDiff($dateTime1, $dateTime2, $maxParts = 2):string {
    $to = [
        'año' => 60*60*24*365,
        'mes' => 60*60*24*30,
        'día' => 60*60*24,
        'hr' => 60*60,
        'min' => 60,
        'seg' => 1,
    ];
    $plural = [
        'año' => 'años',
        'mes' => 'meses',
        'día' => 'dias',
        'hr' => 'hrs',
        'min' => 'min',
        'seg' => 'seg',
    ];
    $last = 'seg';
    $format = [];
    if($dateTime1 instanceof Stringable) {
        $dateTime1 = (string)$dateTime1;
    }
    if(!is_numeric($dateTime1)) {
        $dateTime1 = strtotime($dateTime1);
    }
    if($dateTime2 instanceof Stringable) {
        $dateTime2 = (string)$dateTime2;
    }
    if(!is_numeric($dateTime2)) {
        $dateTime2 = strtotime($dateTime2);
    }
    $diff = abs( (float)$dateTime1 - (float)$dateTime2);
    $breakNow = 0;
    foreach($to as $label => $u) {
        if($breakNow >= $maxParts || ($breakNow > 0 && $label === $last)) {
            break;
        }
        $period = floor($diff/$u);
        if( $period >= 1 ) {
            $diff -= $period * $u;
            $format[] =  $period . ' ' . ($period === 1.00 ? $label : $plural[$label]);
            $breakNow++;
        } elseif($breakNow === 1) {
            $breakNow++;
        }
    }
    return implode(", ", $format);
}

