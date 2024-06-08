<?php /** @noinspection SqlNoDataSourceInspection */
/** @noinspection PhpRedundantOptionalArgumentInspection */
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpUnused */

/**
 * Justification
 *
 * @noinspection PhpMissingParamTypeInspection: flexible parameters vs strict return
 * @noinspection PhpUnused: it is a library
 * @noinspection PhpRedundantOptionalArgumentInspection: show default parameters
 */

namespace coso\sql;

use DateTimeInterface;
use Exception;
use Stringable;


class SqlIt {
    /** @var array<int|string> $sqlIdentifier */
    protected array $sqlIdentifier = [
        'DATABASE()', 'SCHEMA()',
        'CURRENT_USER()', 'SESSION_USER()', 'SYSTEM_USER()', 'USER()', 'CURRENT_USER',

        'CURDATE()', 'CURRENT_DATE()', 'CURRENT_DATE', 'SYSDATE()', 'UTC_DATE()',
        'CURRENT_DATETIME', 'NOW()',
        'CURRENT_TIME()', 'CURRENT_TIME', 'CURTIME()', 'UTC_TIME()',
        'CURRENT_TIMESTAMP()', 'CURRENT_TIMESTAMP', 'LOCALTIMESTAMP()', 'LOCALTIMESTAMP',
        'UNIX_TIMESTAMP()', 'UTC_TIMESTAMP()',

        'FOUND_ROWS()', 'ROW_COUNT()',
        'VERSION()',

        'PI()',  'RAND()', 'EXP(1)', 'SQRT(2)',
        'RANK()',

        'UUID()',
        'UUID_TO_BIN(UUID())', 'UUID_TO_BIN(UUID(),0)', 'UUID_TO_BIN(UUID(),1)',
        'HEX(UUID_TO_BIN(UUID()))', 'HEX(UUID_TO_BIN(UUID(),0))', 'HEX(UUID_TO_BIN(UUID(),1))',
        'BIN_TO_UUID(UUID_TO_BIN(UUID()))', 'BIN_TO_UUID(UUID_TO_BIN(UUID(),0))', 'BIN_TO_UUID(UUID_TO_BIN(UUID(),1))',
        'UUID_SHORT()',
    ];

    /** @var array<int|string> $blobColumn */
    protected array $blobColumn = [
        'REMARK', 'REMARKS', 'NOTA', 'NOTAS'
    ];

    protected bool $is_PreparedStatement;

    protected string $onEmptyIn;
    /** @var array<int,string> $paramsType */
    protected array $paramsType;
    /** @var array<string,array> $paramsKey */
    protected array $paramsKey;
    

    /**
     * @param bool $is_PreparedStatement
     * @param string $onEmptyIn
     */
    public function __construct(bool $is_PreparedStatement, string $onEmptyIn = "\t") {
        $this->is_PreparedStatement = $is_PreparedStatement;
        $this->onEmptyIn = $onEmptyIn;
        $this->blobColumn = array_flip($this->blobColumn);
        $this->sqlIdentifier = array_flip($this->sqlIdentifier);
        $this->paramsType = [];
        $this->paramsKey = [];

      // (new Counter())->inc(4);

    }

    /** Utility methods */
    /**
     * Protect with ` sql column, table, schema, ... name
     * Quotes a: column/table/db name to `column name` respecting . table.column to `table`.`column`
     *
     * @param string|\Stringable|array<int|string,string|\Stringable> $fieldName ie columnName, [columnName,..]
     * @return string|array<int|string,string>
     */
    public function fieldIt($fieldName):string|array {
        if(is_array($fieldName)) {
            foreach($fieldName as &$f)
                $f = $this->fieldIt($f);
            return $fieldName;
        }
        $protected = [];
        $num = explode('.',(string)$fieldName);
        foreach($num as $field)
            $protected[]= '`' . (
                    preg_replace('/\\|[`\p{Cc}]/', '', $field) ??
                    str_replace(['`',"\r","\n","\t","\0","\8","\\"], '', $field)
                ) . '`';
        return implode('.', $protected);
    }

    /**
     * Protect _ and % so they will have no meaning in LIKE clauses
     *
     * @param string|Stringable|null|int|float|bool|array<int|string,string|Stringable> $s
     * @return string|array<int|string,string>
     */
    public function likeIt($s):string|array {
        if($s === null)
            return '';
        if(is_array($s)) {
            foreach($s as &$v)
                $v = $this->likeIt($v);
            return $s;
        }
        return str_replace(['_', '%'], ["\\_", "\\%"], (string)$s);
    }

    /**
     * Protect, when not in $this->sqlIdentifier, a value from sql Injection: ¡You should use stored procedures!, but here it is
     *
     * @param string|Stringable|null|bool|int|float|DateTimeInterface $value
     * @return string
     */
    public function valueProtect($value):string {
        if($value instanceof DateTimeInterface)
            return $value->format("'Y-m-d'");
        return match ($value) {
            NULL => "NULL",
            true => "TRUE",
            false => "FALSE",
            '' => "''",
            '0', 0, 0.00 => '0',
            '1', 1,  => '1',
            default => $this->isSqlFunction($value) ? (string)$value : $this->strit($value)
        };
    }

    /**
     * Protect a value from sql Injection: ¡You should use stored procedures!, but here it is
     *
     * @param string|bool|int|float|Stringable|null|array<int|string,string|Stringable|int|float|bool|null> $s
     * @return string|array<int|string,string>
     */
    public function strIt($s):string|array  {
        if($s === null)
            return 'NULL';
        if(is_array($s)) {
            foreach($s as &$v)
                $v = $this->strIt($v);
            return $s;
        }
        // pex 1 para varchar, otra para textarea, y finalmente blob
        // $pat = '[\b\00\01]'; $pat = '/\p{Cc}/';
        return "'" .
            str_replace (
                ["\\", "'"],
                ["\\\\", "''"],
                preg_replace('/\p{Cc}/', ' ', (string)$s) ?? (string)$s
            ) .
            "'";
    }

    /**
     * Change unsafe keys for safe keys
     *
     * remapKeys(['id'1, 'n'=>'Susan', 'm'=>3],  ['id'=>'user_id', 'n'=>'user_name', 'm'=>'friend']) =
     *  [user_id1, user_name=>'Susan', 'friend'=>['user_id'=>3]
     *
     * @param array<int|string, mixed> $inputArray
     * @param array<int|string, string> $remapRules
     * @return array<int|string, mixed>
     * @require php >= 8.0
     *
     */
    public function remapKeys(array $inputArray, array $remapRules):array {
        $remapped = [];
        foreach($inputArray as $key => $value)
            if(array_key_exists($key, $remapRules) || is_numeric($key))
                $remapped[$remapRules[$key]] = is_array($value) ?
                    $this->remapKeys($value, $remapRules) :
                    $value;
        return $remapped;
    }

    /** Advanced complex function for Prepared Statements */

    /**
     * Danger, use with care
     *
     * @param string|Stringable $columnName
     * @param string|Stringable|null|bool|int|float|DateTimeInterface $value
     * @param string $isA #[ExpectedValues('primitive', 'in', 'min-max')]
     * @param string|null $type #[ExpectedValues(null,'b','i','s')]
     * @return string
     */
    public function valueAdd(string|Stringable $columnName, $value, string $isA = 'primitive', string|null $type = null):string {
        try {
            switch (str_replace(' ', '', (string)$value)) {
                case $columnName.'+1':
                case '+1':
                case '++':
                case $columnName . '++':
                    return $this->fieldIt($columnName) . " + 1";
                case $columnName.'-1':
                case '-1':
                case '--':
                case $columnName . '--':
                    return $this->fieldIt($columnName) . " - 1";
            }
        } catch(Exception) { /* ignore */}

        if($this->is_PreparedStatement) {
            if($type === null && array_key_exists(strtoupper((string)$columnName), $this->blobColumn))
                $type = 'b';
            $this->paramsKey[$columnName][] =['position' => count($this->paramsType), 'isA' => $isA];
            $this->paramsType[] = $type ?? 's';
            return '?';
        }
        return $this->valueProtect($value);
    }

    /** Prepared Statement parameters */

    public function types():string { return implode('', $this->paramsType); }

    /**
     *
     *
     * @param array<string,int|float|bool|null|string|Stringable|DateTimeInterface|array> $values
     * @return array<string,int|float|bool|null|string|Stringable|DateTimeInterface>
     * @throws Exception
     */
    public function fit_ToParams(array $values):array {
        $fitted = [];
        foreach($this->paramsKey as $key => $expected) {
            if(!array_key_exists($key, $values))
                throw new Exception("$key parameter not found");
            $expectedValues = count($expected);
            $val = $values[$key];
            if(!is_array($val)) {
                if($expectedValues === 1) {
                    $fitted[] = $val;
                    continue;
                }
                $val = [$val];
            }
            $valuesHave = count($val);
            if($valuesHave <= $expectedValues ) {
                foreach($val as $v)
                    $fitted[] = $v;
                $repeatValue = reset($val);
                for($i = $valuesHave; $i < $expectedValues; ++$i)
                    $fitted[] = $repeatValue;
                continue;
            }
            $isA = reset($expected)['isA'];
            throw new Exception("$key parameter expected $expectedValues values, got $valuesHave values for a $isA");
        }
        return $fitted;
    }

    /** Sql builders */

    /**
     * @param string|Stringable|array<string,string|Stringable|int|float|bool|null|DateTimeInterface|array> $keyValue [column: equalsValue, col2 => [or,ed,values], col3 =>['min'=>'v', 'max=>'v]
     * @param string|Stringable $clauseOperator #[Expected('AND', 'OR', 'XOR')]
     * @return string
     */
    public function where(string|Stringable|array $keyValue, string|Stringable $clauseOperator = 'AND'):string {
        if($keyValue instanceof  Stringable) {
            $kv = trim((string)$keyValue);
            return $kv === '' ? ' 1=1 ' : $kv;
        }
        if(is_string($keyValue))
            return trim($keyValue) === '' ? ' 1=1 ' : $keyValue;

        $where = [];
        foreach($keyValue as $columnName => $value)
            if(is_array($value))
                if(array_is_list($value))
                    $where[] = $this->fieldIt($columnName) . ' IN ' . $this->in($columnName,$value);
                else
                    $where[] = $this->whereBetweenIn($columnName, $value);
            else
                $where[] = $this->fieldIt($columnName) . ' <=> ' . $this->valueAdd($columnName, $value);

        return empty($where) ? ' 1=1 ' : ' (' . implode(" $clauseOperator ", $where) . ') ';
    }

    protected function whereBetweenIn(string|Stringable $columnName, array $value): string {
        if(count($value) === 2) {
            $min = $value['min'] ?? null;
            $max = $value['max'] ?? null;
            if($min !== null && $max !== null)
                if($this->is_PreparedStatement || $min < $max)
                    return '(' . $this->fieldIt($columnName) . ' >= ' . $this->valueAdd($columnName,$min, 'min-max') .
                        ' AND ' . $this->fieldIt($columnName) . ' <= ' . $this->valueAdd($columnName,$max, 'min-max') . ')';
                else
                    return '(' . $this->fieldIt($columnName) . ' >= ' . $this->valueAdd($columnName,$max) .
                        ' AND ' . $this->fieldIt($columnName) . ' <= ' . $this->valueAdd($columnName,$min) . ')';
            if($min !== null)
                return $this->fieldIt($columnName) . ' >= ' . $this->valueAdd($columnName,$min);
            if($max !== null)
                return $this->fieldIt($columnName) . ' <= ' . $this->valueAdd($columnName,$min);
        }
        return $this->fieldIt($columnName) . ' IN ' . $this->in($columnName,$value);
    }

    /**
     * Returns ('value1',...)
     *
     * @param string|Stringable $columnName
     * @param array $values [value1, value2, ...]
     * @param string|Stringable|int|float|bool|null $onValueEmpty
     * @return string ('value1',..) or (?,?,..) or ('$onValueEmpty)
     */
    public function in(string|Stringable $columnName, $values, string|Stringable|int|float|bool|null|DateTimeInterface $onValueEmpty = "\t"):string {
        if(!is_array($values))
            $values = [$values];
        $in = [];
        foreach($values as $v)
            $in[] = $this->valueAdd($columnName, $v, 'in');
        return empty($in) ? " (" . $this->valueProtect($onValueEmpty). ") " : ' (' . implode(', ', $in) . ') ';
    }

    public function match(string|Stringable $columnName, string|Stringable $value, string|Stringable $inBoooleanMode = ' IN BOOLEAN MODE'):string {
        return "@TODO $columnName $value $inBoooleanMode";
    }

    /**
     * Form an insert statement
     *
     * @param string|Stringable $table
     * @param array<string,string|Stringable|int|float|bool|null> $keyValue [columnName:Value,..]
     * @param bool $doOnDuplicateField true generates ON DUPLICATE KEY clause
     * @param array<int|string,string|Stringable> $excludeOnUpdate [columnName,...]
     * @param array<int|string,string|Stringable> $onlyIncludeOnUpdate  [columnName,...] empty includes all columns
     * @param string|Stringable $comment comment to add to built sql
     * @return string
     */
    public function insert($table, $keyValue, $doOnDuplicateField = false, $excludeOnUpdate = [], $onlyIncludeOnUpdate = [], $comment = __METHOD__):string {
        $columns = [];
        $values = [];
        $onDuplicate = [];
        $exclude = array_flip($excludeOnUpdate);
        if($doOnDuplicateField && count($onlyIncludeOnUpdate) === 0)
            $onlyIncludeOnUpdate = array_flip(array_keys($keyValue) );
        else
            $onlyIncludeOnUpdate = array_flip($onlyIncludeOnUpdate);

        $tableAlias = array_key_exists('alias', $keyValue) ?  '_' . $table . '_alias_' : 'alias';
        foreach($keyValue as $columnName => $val) {
            $columns[] = $column = $this->fieldIt($columnName);
            $values[] = $this->valueAdd($columnName, $val);
            if(($doOnDuplicateField || array_key_exists($columnName, $onlyIncludeOnUpdate)) &&
                !array_key_exists($columnName, $exclude)
            )
                $onDuplicate[] = "$column=$tableAlias.$column";
        }

        return "INSERT /*$comment*/ INTO " . $this->fieldIt($table) .
            '(' . implode(',', $columns) . ') VALUES (' . implode(',',$values) . ')' .
            (empty($onDuplicate) ? '' : " $tableAlias ON DUPLICATE KEY UPDATE " . implode(',', $onDuplicate));
    }

    /**
     * Forms an update statement
     *
     * @param string|Stringable $table
     * @param array $colmnNameValue
     * @param string|Stringable|array $where
     * @param string|Stringable $comment comment to add to built sql
     * @param string|Stringable $clauseOperator #[Expected('AND', 'OR', 'XOR')]
     * @return string
     */
    public function update($table, $colmnNameValue, $where, $comment = __METHOD__, $clauseOperator = 'AND'):string {
        // @TODO cuales no quotear?
        $setClause = [];
        foreach($colmnNameValue as $columnName => $value)
            $setClause[] =
                $this->fieldIt($columnName) . '=' . $this->valueAdd($columnName,$value);
        return "UPDATE /*$comment*/ " . $this->fieldIt($table) . " SET " .
            implode(", ", $setClause) .
            ' WHERE ' . $this->where($where, $clauseOperator);
    }

    /**
     * Forms a delete statement
     *
     * @param string|Stringable $table
     * @param string|Stringable|array $where
     * @param string|Stringable $comment comment to add to built sql
     * @param string|Stringable $clauseOperator #[Expected('AND', 'OR', 'XOR')]
     * @return string
     */
    public function delete($table, $where, $comment = __METHOD__, $clauseOperator = 'AND'):string {
        return "DELETE /*$comment*/ FROM " . $this->fieldIt($table) .
            ' WHERE ' . $this->where($where, $clauseOperator);
    }

    /** Helpers */
    /**
     * @param string|Stringable|int|float|bool|null|DateTimeInterface $name
     * @return bool
     */
    protected function isSqlFunction($name):bool {
        if($name instanceof DateTimeInterface || $name === null)
            return false;
        return array_key_exists(
            strtoupper(str_replace(' ', '', (string)$name)),
            $this->sqlIdentifier
        );
    }

    /**
     * @param string|Stringable|int|float|bool|null|DateTimeInterface $name
     * @return string|Stringable|int|float|bool|DateTimeInterface|null
     */
    protected function protectDefaultClause($name):string|Stringable|int|float|bool|null|DateTimeInterface {
        if( !(is_string($name) || $name instanceof Stringable) )
            return $name;
        /** @noinspection RegExpSimplifiable */
        if(empty(preg_match_all(
            '/(DEFAULT\(\s*([`A-Z_][^()\s]*)\s*\))|([=+\-\/*\\\\])|(\d+\.{0,1}\d*)/miS',
            (string)$name,
            $matches,
            PREG_SET_ORDER, 0
        )))
            return $name;
        $return = [];
        foreach($matches as $m)
            if(strlen($m[0]) <= 2 || is_numeric($m[0]))
                $return[] = $m[0];
            else
                $return[] = 'DEFAULT(' . $this->fieldIt($m[2]) . ')';
        return implode('', $return);
    }

    protected function forceString(mixed $s, $onNotPossibleUse = ''):string {
        if(is_string($s))
            return $s;
        if(is_int($s) || is_float($s) || is_bool($s) || $s === NULL || $s instanceof Stringable)
            return (string)$s;
        return $onNotPossibleUse;
    }
}
