<?php
/** @noinspection PhpUnused */

namespace it\sql;

function getCallingFunctionName() {
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
    if(count($backtrace) === 1)
        return basename($backtrace[0]['file']) . " LINE: " . $backtrace[0]['line'];
    $callingFunction = end($backtrace);
   return isset($callingFunction['class']) ? $callingFunction['class'] . '::' . $callingFunction['function'] : $callingFunction['function'];
}

class ItSqlFormat {

    public static function format(string $sql):string {
        $sql = preg_replace_callback("/\\b(SELECT|FROM|WHERE|GROUP BY|HAVING|ORDER BY|ON\\s+DUPLICATE\\s+KEY\\s+UPDATE|VALUES)\\b(?![^']*')/imuS",
          function($matches) {return "\n".strtoupper($matches[1]);},
          trim($sql));
        $sql = preg_replace_callback("/\\s(STRAIGHT_JOIN|CROSS\\s+JOIN|INNER\\s+JOIN|LEFT\\s+OUTER|RIGHT\\s+OUTER|LEFT\\s+JOIN|RIGHT\\s+JOIN|FULL\\s+JOIN|JOIN|EXISTS)\\b(?![^']*')/imuS",
          function($matches) {return "\n\t".strtoupper($matches[1]);},
          $sql);
        return preg_replace('/\n{2,}/imuS', "\n",$sql);
    }

    /**
     * @param string $sql
     * @param array $parameters parameters to substitute ? in $sql
     * @return string
     */
    public function showWithParameters(string $sql, array $parameters):string {
        return preg_replace_callback("/(?<!')\\?(?!')/muS",
          function() use (&$parameters) { return $this->strit(array_shift($parameters)); },
          $sql
        );
    }

    public static function normalize(string $sql):string {
        $from = stripos($sql, 'FROM');
        if($from >= 0) {
            $left = substr($sql, 0, $from);
            $right = substr($sql, $from);
        } else {
            $left  = '';
            $right = $sql;
        }
        $right =  preg_replace("/'((?:[^'\\\]|'')*[^']|(?:[^'\\\]|'')*)'/muSs",
          '?',
          $right
        );
        return self::format( $left . preg_replace("/(\d+\.?\d*)/muSs",
            '?',
            $right
          ) );
    }

    public static function strIt($s):string|array  {
        if($s === null)
            return 'NULL';
        if(is_array($s)) {
            foreach($s as &$v)
                $v = self::strIt($v);
            return $s;
        }
        return "'" .
          str_replace (
            ["\\", "'"],
            ["\\\\", "''"],
            preg_replace('/\p{Cc}/', ' ', (string)$s) ?? (string)$s
          ) .
          "'";
    }
}
