<?php
/** @noinspection PhpUnused */

//@TODO tableSorter
//@TODO tableSorter needs unique id o no poner id a la tabla?
//@TODO test
//@TODO doc
//@TODO css
//@TODO enum.dataTypes
//@TODO modo.deduce each type in each cell
//@TODO modo.tableSorter plugin
//@TODO footer totals

namespace coso\report;
use DateTimeInterface;

class TableIt {

    public static function toTable(
        array $tableData, string $caption = '', array $headers = [], bool $columnsOnlyInHeaders = false,
        string|null $tableId = null, string|null $tableCssClass = null
    ):string {
        return new (static())->table($tableData, $caption, $headers, $columnsOnlyInHeaders, $tableCssClass, $tableId);
    }

    // Define
    
    protected string $tableCssClass = 'tableIt';
    protected string $tableId = 'tableIt';

    protected string|null $zeroFormatted = '0';
    protected string|null $numberPrefix = '';
    protected string|null $numberSuffix = '';

    protected string $dateFormat = 'd/M/y';
    protected string $dateTimeFormat = 'd/M/y H:i';

    protected string $trueFormatted = '';
    protected string $falseFormatted = '';

    protected int $rowsToAnalyzeToDeduceType = 10;

    protected const REGX_DATE = '/^\d\d\d\d-(0\d|1[012])-([012]\d|3[01])$/S';
    protected const REGX_DATETIME = '/^\d\d\d\d-(0\d|1[012])-([012]\d|3[01])[ T][012]\d:[0-6]\d:[0-6]\d/S';



    /**
     * @param string $tableCssClass
     * @param string|null $zeroFormatted
     * @param string $dateFormat
     * @param string $dateTimeFormat
     * @param string $trueFormatted
     * @param string $falseFormatted
     * @param string $tableId
     * @param int $rowsToAnalyzeToDeduceType
     */
    public function __construct(string $tableCssClass = 'tableIt', ?string $zeroFormatted = '0',
            string $dateFormat = 'd/M/y', string $dateTimeFormat = 'd/M/y H:i',
            string $trueFormatted = '', string $falseFormatted = '', string $tableId = 'tableIt',
            int $rowsToAnalyzeToDeduceType = 10) {
        $this->tableCssClass = $tableCssClass;
        $this->zeroFormatted = $zeroFormatted;
        $this->dateFormat = $dateFormat;
        $this->dateTimeFormat = $dateTimeFormat;
        $this->trueFormatted = $trueFormatted;
        $this->falseFormatted = $falseFormatted;
        $this->tableId = $tableId;
        $this->rowsToAnalyzeToDeduceType = $rowsToAnalyzeToDeduceType;
    }

    /**
     * {key:{label:string,
     *              type:string,
     *              trueFormatted:string, falseFormatted:string,
     *              zeroFormatted:string, thousands:string, decimalPoint:string, numberPrefix:string, numberSuffix:string,
     *              dateFormat:'string, dateTimeFormat:string,
     *              align:string,
     *              maxChars:int,
     *           }
     *      }

     */
    /**
     * @param array $tableData
     * @param string $caption
     * @param array $headers optional the array en each entry's key, defaults are deduced:
     *      {key:{  label:string,
     *              type:bool|int|float|currency|date|dateTime|string,
     *              trueFormatted:string, falseFormatted:string,
     *              zeroFormatted:string, thousands:',', decimalPoint:'.', numberPrefix:'', numberSuffix:'',
     *              dateFormat:'d/M/y', dateTimeFormat:'d/M/y H:i',
     *              align:'left|right|center',
     *              maxChars:int,
     *           }
     *      }
     * @param string|null $tableId
     * @param string|null $tableCssClass
     * @param bool $columnsOnlyInHeaders
     * @return string
     */
    public function table(array $tableData, string $caption = '', array $headers = [],
        string|null $tableId = null, string|null $tableCssClass = null, bool $columnsOnlyInHeaders = false
    ):string {
        $headers = $this->headersDeduce($tableData, $headers);

        $table = [];
        $th = [];
        foreach($headers as $key => $option)
            $th[$key] = "<th>$option[label]";

        foreach($tableData as $row) {
            $td = [];
            foreach($headers as $key => $option) {
                if(array_key_exists($key, $row)) {
                    $class = " class='" . trim(($option['align'] ?? '') . ' ' , ($option['class'] ?? '') ) . "'";
                    $tdTag = $option['tdTag'] ?: '';
                    $td[$key] = "<td $class $tdTag>" . $this->format($row[$key], $option);
                } else
                    $td[$key] = "<td>";
            }
            if($columnsOnlyInHeaders)
                continue;
            foreach($this->newKeys($headers, $row) as $key => $value) {
                if(!array_key_exists($key, $th)) {
                    $headers[$key] = $this->dataTypeDeduce([ [$key => $value] ], [])[$key] ?? [];
                    $headers[$key]['label'] =  $this->toLabel($key);
                    $th[$key] = "<th>" . $headers[$key]['label'];
                }
                $option = $headers[$key];
                $class = " class='" . trim(($option['align'] ?? '') . ' ' , ($option['class'] ?? '') ) . "'";
                $tdTag = $option['tdTag'] ?: '';
                $td[$key] = "<td $class $tdTag>" . $this->format($value, $option);
            }
            $table[] = implode('', $td);
        }
        $gFn = function ($callable) {return $callable;};
        $tableCssClass = $tableCssClass ?: $this->tableCssClass;
        $tableId = $tableId ?: $this->tableId;
        return <<<HTML
            <table id='$tableId' class='$tableCssClass'><caption>$caption</caption>
            <thead><tr>{$gFn(implode('', $th))}</tr></thead>
            <tbody><tr>{$gFn(implode('<tr>', $table))}</tr></tbody>
            </table>        
        HTML;
    }

    protected function headersDeduce(array $tableData, array $headers):array {
        foreach(reset($tableData) as $key => $_)
            $headers[$key]['label'] = $headers[$key]['label'] ?? $this->toLabel($key);
        return $this->dataTypeDeduce($tableData, $headers);
    }
    /////////////////////////////////
    /// Foramatters
    ////////////////////////////////

    /**
     * @param mixed $value
     * @param array{type:string,dateFormat:string, dateTimeFormat:string,numberPrefix:string, numberSuffix:string, commas:bool, decimals:int, zeroFormatted:string, falseFormatted: string, trueFormatted: string} $option
     * @return string
     */
    protected function format(mixed $value, array $option):string {
        if(is_array($value)) {
            $ret = [];
            foreach($value as $v)
                $ret[] = $this->format($v, $option);
            return implode(", ", $ret);
        }
        if(is_object($value)) {
            if(method_exists($value, '__toString'))
                $value = $value->__toString();
            elseif(!($value instanceof DateTimeInterface))
                return '[Object]';
        }
        if($value === null)
            $value = '';
        return match ($option['type']) {
            'int', 'float'     => $this->formatNumber($value, $option),
            'date', 'dateTime' => $this->formatDate($value, $option),
            'bool'             => $this->formatBool($value, $option),
            default            => is_object($value) ? '[Object]' : $value,
        };
    }

    /**
     * @param string|int|float|bool|null $value
     * @param array{numberPrefix:string,numberSuffix:string,commas:bool,decimals:int,zeroFormatted:string} $option
     * @return string
     */
    protected function formatNumber(mixed $value, array $option):string {
        if($value === null || $value === '' )
            return '';
        if($value === true || $value === false)
            return $this->formatBool($value, $option);
        if(!is_numeric($value))
            return $value;

        $prefix = $option['numberPrefix'] ?? $this->numberPrefix;
        $suffix = $option['numberSuffix'] ?? $this->numberSuffix;
        if((float)$value === 0.00) {
            $zeroFormat = $option['zeroFormatted'] ?? $this->zeroFormatted;
            if( $zeroFormat === '0' )
                return $prefix . $this->bcformat('0', $option['decimals'] ?? 2) . $suffix;
            else
                return $prefix . $zeroFormat . $suffix;
        }
        return  $prefix .
                (empty($option['commas']) ? $value : $this->bcformat($value, $option['decimals'] ?? 2)) . $suffix;
    }

    /**
     * Redondea y pone comas a un numero en string, number_format para strings
     *
     * @param string|int|float|null|bool $num
     * @param int $decimals
     * @return string el $num con comas y redondeado al $decimals decimales
     */
    protected function bcformat(string|int|float|null|bool $num, int $decimals = 2):string {
        if($num === null || $num === '')
            return '';
        if(!is_numeric($num))
            return "$num";
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

    /**
     * @param $value
     * @param array{type:string, dateFormat:string, dateTimeFormat:string} $option
     * @return string
     */
    protected function formatDate($value, array $option):string {
        if($option['type'] === 'date')
            $formatString = $option['dateFormat'] ?? $this->dateFormat;
        else
            $formatString = $option['dateTimeFormat'] ?? $this->dateTimeFormat;
        if($value instanceof DateTimeInterface)
            return $value->format($formatString);
        if($option['type'] === 'date') {
            if(preg_match(self::REGX_DATE, $value))
                return Date($formatString, strtotime($value));
            return $value;
        }
        if(preg_match(self::REGX_DATETIME, $value))
            return Date($formatString, strtotime($value));
        return $value;
    }


    /**
     * @param string|int|float|bool|null $value
     * @param array{falseFormatted: string, trueFormatted: string} $option
     * @return string
     */
    protected function formatBool(mixed $value, array $option):string {
        if(empty($value) || $value === 'false')
            return $option['falseFormatted'] ?: $this->falseFormatted;
        return $option['trueFormatted'] ?: $this->trueFormatted;
    }

    /////////////////////////////////
    /// Deduce
    ////////////////////////////////

    /**
     * @param string|int $string
     * @return string
     */
    protected function toLabel(string|int $string):string {
        if(is_numeric($string))
            return "$string";
        return ucwords(
            str_replace('_', ' ',
                strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', $string ))
            )
        );
    }

    /**
     * @param array $base
     * @param array $enEmpateGana
     * @return array
     */
    protected function newKeys(array $base, array $enEmpateGana):array {
        return array_diff_key($enEmpateGana, array_intersect_key($base, $enEmpateGana));
    }

    /**
     * @param array $tableData
     * @param array $headers
     * @return array
     */
    protected function dataTypeDeduce(array $tableData, array $headers):array {
        $types = [];
        $commas = [];
        $decimals = [];
        $align = [];
        $maxLen = [];
        $analyzedRows = 0;
        foreach($tableData as $row) {
            foreach($row as $key => $value) {
                if($value === null || $value === '')
                    continue;
                if(is_object($value) && method_exists($value, '__toString'))
                    $value = $value->__toString();
                if(is_string($value)) {
                    $strlen = strlen($value);
                    if(empty($maxLen[$key]) || $strlen > $maxLen[$key])
                        $maxLen[$key] = $strlen;
                }
                $keyLower = strtolower($key);
                $headers[$key]['type'] = '';
                $t = $this->deduceType($value);
                if($t === 'object')
                    continue;
                switch($t) {
                    case 'bool':
                        if(empty($types[$key])) {
                            $types[$key] = 'bool';
                            $align[$key] = 'center';
                        }
                        break;
                    case 'int':
                        if(empty($types[$key]) || $types[$key] === 'bool') {
                            $types[$key] = 'int';
                            $align[$key] = 'right';
                            $decimals[$key] = 0;
                            if(!array_key_exists($key, $commas))
                                $commas[$key] = $this->deduceCommas($keyLower);
                        }
                        break;
                    case 'float':
                        if(empty($types[$key]) || $types[$key] === 'int' || $types[$key] === 'bool') {
                            $types[$key] = 'float';
                            $align[$key] = 'right';
                            $decimals[$key] = 2;
                            if(!array_key_exists($key, $commas))
                                $commas[$key] = $this->deduceCommas($keyLower);
                        }
                        break;
                    case 'date':
                        if(empty($types[$key])) {
                            $types[$key] = 'date';
                            $align[$key] = 'center';
                        }
                        break;
                    case 'dateTime':
                        if(empty($types[$key]) || $types[$key] === 'date') {
                            $types[$key] = 'dateTime';
                            $align[$key] = 'center';
                        }
                        break;
                    case 'array':
                        break;
                    default:
                        if(empty($types[$key])) {
                            $types[$key] = 'string';
                            $align[$key] = 'left';
                        }
                }
            }
            if($analyzedRows++ >= $this->rowsToAnalyzeToDeduceType)
                break;
        }
        foreach($types as $key => $t) {
            if(empty($headers[$key]['type']))
                $headers[$key]['type'] = $t;
            else
                $t = $headers[$key]['type'];
            if(!array_key_exists('maxChars', $headers[$key]))
                $headers[$key]['maxChars'] = $maxLen[$key] ?? 0;
            if(!array_key_exists('thousands', $headers[$key]))
                $headers[$key]['thousands'] = $commas[$key] ?? '';
            if(!array_key_exists('decimalPoint', $headers[$key]))
                $headers[$key]['decimalPoint'] =  $t === 'float' ? '.' : '';
            if(!array_key_exists('decimals', $headers[$key]))
                $headers[$key]['decimals'] =  $decimals[$key] ?? 0;
            if(!array_key_exists('align', $headers[$key]))
                $headers[$key]['align'] = $align[$key] ?? 'left';
        }
        return $headers;
    }

    /**
     * @param string|int|float|bool|object $value
     * @return string
     */
    protected function deduceType(string|int|float|bool|object $value):string {
        if($value instanceof DateTimeInterface)
            return 'dateTime';
        if(is_object($value))
            if(method_exists($value, '__toString'))
                $value = $value->__toString();
            else
                return 'object';

        if(is_bool($value))
            return 'bool';
        if(is_numeric($value))
            return strpos("$value", '.') ? 'float' : 'int';
        if(is_string($value)) {
            $strlen = strlen($value);
            if($strlen === 10)
                if(preg_match(self::REGX_DATE, $value))
                    return 'date';
                else
                    return 'string';
            if($strlen > 18 && $strlen < 30 && preg_match(self::REGX_DATETIME, $value))
                return 'dateTime';
            return 'string';
        }
        return gettype($value);
    }

    /**
     * @param string $keyLower
     * @return bool
     */
    protected function deduceCommas(string $keyLower):bool {
        if(strcasecmp($keyLower, 'id') === 0 || str_ends_with($keyLower, '_id'))
            return false;
        return !($keyLower === 'zipcode' || $keyLower === 'zip_code' || $keyLower === 'cp');
    }

    /////////////////////////////////
    /// Getters && Setters
    ////////////////////////////////


    /**
     * @return string
     */
    public function get_table_css(): string {
        return $this->tableCssClass;
    }

    /**
     * @param string $tableCssClass
     * @return TableIt
     */
    public function set_table_css(string $tableCssClass): TableIt {
        $this->tableCssClass = $tableCssClass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function get_zero_formatted(): ?string {
        return $this->zeroFormatted;
    }

    /**
     * @param string|null $zeroFormatted
     * @return TableIt
     */
    public function set_zero_formatted(?string $zeroFormatted): TableIt {
        $this->zeroFormatted = $zeroFormatted;
        return $this;
    }

    /**
     * @return string
     */
    public function get_date_format(): string {
        return $this->dateFormat;
    }

    /**
     * @param string $dateFormat
     * @return TableIt
     */
    public function set_date_format(string $dateFormat): TableIt {
        $this->dateFormat = $dateFormat;
        return $this;
    }

    /**
     * @return string
     */
    public function get_date_time_format(): string {
        return $this->dateTimeFormat;
    }

    /**
     * @param string $dateTimeFormat
     * @return TableIt
     */
    public function set_date_time_format(string $dateTimeFormat): TableIt {
        $this->dateTimeFormat = $dateTimeFormat;
        return $this;
    }

    /**
     * @return string
     */
    public function get_true_formatted(): string {
        return $this->trueFormatted;
    }

    /**
     * @param string $trueFormatted
     * @return TableIt
     */
    public function set_true_formatted(string $trueFormatted): TableIt {
        $this->trueFormatted = $trueFormatted;
        return $this;
    }

    /**
     * @return string
     */
    public function get_false_formatted(): string {
        return $this->falseFormatted;
    }

    /**
     * @param string $falseFormatted
     * @return TableIt
     */
    public function set_false_formatted(string $falseFormatted): TableIt {
        $this->falseFormatted = $falseFormatted;
        return $this;
    }

    /**
     * @return string
     */
    public function get_table_id(): string {
        return $this->tableId;
    }

    /**
     * @param string $tableId
     * @return TableIt
     */
    public function set_table_id(string $tableId): TableIt {
        $this->tableId = $tableId;
        return $this;
    }

    /**
     * @return int
     */
    public function get_rows_to_analyze_to_deduce_type(): int {
        return $this->rowsToAnalyzeToDeduceType;
    }

    /**
     * @param int $rowsToAnalyzeToDeduceType
     * @return TableIt
     */
    public function set_rows_to_analyze_to_deduce_type(int $rowsToAnalyzeToDeduceType): TableIt {
        $this->rowsToAnalyzeToDeduceType = $rowsToAnalyzeToDeduceType;
        return $this;
    }

}
