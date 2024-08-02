<?php
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpUnused */

namespace It\Sql;

interface BuildSql {
    public function insert($table, $fieldValues):string|array;
    public function onDuplicateKeyUpdate($fieldValues):string|array;
    public function update(string $table, array $fieldValues, string|array $where):string|array;
    public function where(array $array, $join = 'AND', $columnPrefix = ''):string|array;
    public function fieldIt(string|array $fieldName):string|array;
    public function strIt(mixed $value):string|null|array;
}

class Builder {
    protected bool $useNewOnDuplicate = true; // Beginning with MySQL 8.0.19,

    public array $dontQuoteValue = [
        'IA_UUID()' => 1,
        'CURDATE()'=>1,'CURRENT_DATE()'=>1,'CURRENT_DATE'=>1,'SYSDATE()'=>1,'UTC_DATE()'=>1,
        'CURRENT_DATETIME'=>1,'NOW()'=>1, 'NOW(6)' => 1,
        'CURRENT_TIME()'=>1,'CURRENT_TIME'=>1,'CURTIME()'=>1,'UTC_TIME()'=>1,
        'CURRENT_TIMESTAMP()'=>1,
        'CURRENT_TIMESTAMP'=>1,'LOCALTIMESTAMP()'=>1,
        'LOCALTIMESTAMP'=>1,
        'UNIX_TIMESTAMP()'=>1,'UTC_TIMESTAMP()'=>1
    ];

    public array $dontOnUpdateFieldName = [
        'alta_db' => 1, 'alta_por' => 1,
        'registered' => 1, 'registered_by' => 1
    ];

    public function fieldIt($fieldName):string|array {
        if($fieldName[0] === '(')
            return $fieldName;
        if(is_array($fieldName)) {
            foreach($fieldName as &$d)
                $d = $this->fieldIt($d);
            return $fieldName;
        }
        $protected = [];
        $n = explode('.',$fieldName);
        foreach($n as $field) {
            $protected[]= '`'.
                str_replace(['`',"\r","\n","\t","\0", "\\",
                    chr(8),chr(0),chr(26),chr(27)],
        '',
        trim($field) ).'`';
        }
        return implode('.', $protected);
    }

    public function insert($table, array $fieldValues, bool $onDuplicateKeyUpdate = false,
                           array $onDuplicateKeyDontUpdate = [], array $onDuplicateKeyOverride = [], string $comment = ''
    ):array {
        $new = $this->useNewOnDuplicate;
        $columns = [];
        $values = [];
        $parameters = [];
        $onDuplicateKey = [];
        foreach($fieldValues as $columnName => $value) {
            $col = $this->fieldIt($columnName);
            $columns[] = $col;
            if(array_key_exists($value, $this->dontQuoteValue)) {
                $values[] = $value;
            } else {
                $values[] = "?";
                $parameters[] = $value;
            }
            if($new && $onDuplicateKeyUpdate && !array_key_exists($columnName, $onDuplicateKeyDontUpdate) && !array_key_exists($columnName, $this->dontOnUpdateFieldName))
                if(array_key_exists($columnName, $onDuplicateKeyOverride)) {
                    $onDuplicateKey[] = "$col=" . $onDuplicateKeyUpdate[$columnName];
                }
                else
                    $onDuplicateKey[] = "$col=new.$col";
        }
        if(empty($comment))
            $comment = __METHOD__ . " $table";

        $insert = "INSERT /*$comment*/ " .
            " INTO " . $this->fieldIt($table) .
            "(" . implode(",", $columns) . ") " .
            " VALUES(" . implode(",", $values) . ")";
        if(!empty($onDuplicateKey)) {
            $insert .= "  as new ON DUPLICATE KEY UPDATE " . implode(",", $onDuplicateKey);
        }
        return ["query" => $insert, "parameters" => $parameters];
    }

    // update
    public function where(array $array, $join = 'AND', $columnPrefix = ''):string {
        $where = [];
        foreach($array as $fieldName => $value) {
            $where = $this->fieldIt($columnPrefix . $fieldName) . '=' ;
        }
        return implode(" $join ", $where);
    }
}