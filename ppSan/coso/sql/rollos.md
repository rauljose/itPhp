/** Rollos */
/** On Protection */
/**
* On SQL Injection in values for sql Queries
*
* 1 Use real stored procedures
* 2 Use real stored procedures
* 3 If not at least try something like
*     see below: function strIt($value) for Mysql
*/

        /**
         * On SQL Injection in SQL Identifiers User driven Dynamic Queries avoid
         *
         *  You may need form the front end some db names (column, table, database, procedures, functions,...)
         *  they must be protected against
         *      sql injection
         *      information leaking it is better if db names are secret.
         *
         *   1 Best remap your column/table/db/... names (keys) to safe ones
         *        $inFakeNames = ['id', 'n'=>'Susan', 'm'=>['id'=>3]];
         *       $renamedColumns = remapKeys($in, ['id'=>'user_id', 'n'=>'user_name', 'm'=>'friend']);
         *         => [user_id, user_name=>'Susan', 'friend'=>['user_id'=>3] ]
         *       see function remapKeys() below
         *
         *   2 Second best validate your  column/table/db names, something like
         *      $in = ['user_id', 'user_name'=>'Susan', 'friend'=>['user_id'=>3], 'injection'=>'evil'];
         *      $onlyValidNames =  array_intersect_key($in, ['user_id'=>'int', 'user_name'=>'string', 'friend'=>'array']);
         *
         *   3 Third, at least protect db Names. For mysql protect db names with `
         *        $sql =
         *        "SELECT " . $this->fieldit($_REQUEST['column_name']) .
         *        " FROM " . $this->fieldit($_REQUEST['table_name']) .
         *        " WHERE " . $this->fieldit($_REQUEST['filter_column_name']) . "=?";
         *       see function $this->fieldit($name)
         */
