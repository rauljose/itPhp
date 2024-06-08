
// missing table
    ERROR 1146 (42S02): Table 'test.no_such_table' doesn't exist

        1051	42S02	ER_BAD_TABLE_ERROR	Unknown table '%s'
        1103	42000	ER_WRONG_TABLE_NAME	Incorrect table name '%s'
        1109	42S02	ER_UNKNOWN_TABLE	Unknown table '%s' in %s

// update/insert
    Error Code: 1062. Duplicate entry '(408)-908-2476' for key 'phone'
        DECLARE EXIT HANDLER FOR 1062 /* Duplicate key*/ SET duplicate_key=1;

        1022 ER_DUP_KEY	Can't write; duplicate key in table '%s'

    ERROR 1048 Column 'b' cannot be null

    ERROR 1452: 1452: Cannot add or update a child row: a foreign key constraint fails (`arnold_test`.`Address`, CONSTRAINT `fk_Address_Customer` FOREIGN KEY (`CustomerId`) REFERENCES `Customer` (`CustomerId`) ON DELETE NO ACTION ON UPDATE NO ACTION)

            ERROR INSERT REFERENCE NOT EXISTS
            ERROR UPDATE REFERENCE NOT EXISTS
            ERRO DELETE CONSTRAINT PREVENTS
            ERRO UPDATE CONSTRAINT PREVENTS

            Error number: 1451; Symbol: ER_ROW_IS_REFERENCED_2; SQLSTATE: 23000

             Message: Cannot delete or update a parent row: a foreign key constraint fails (%s)

             InnoDB reports this error when you try to delete a parent row that has children, and a foreign key constraint fails. Delete the children first.

            Error number: 1216; Symbol: ER_NO_REFERENCED_ROW; SQLSTATE: 23000

            Message: Cannot add or update a child row: a foreign key constraint fails

            InnoDB reports this error when you try to add a row but there is no parent row, and a foreign key constraint fails. Add the parent row first.

            Error number: 1217; Symbol: ER_ROW_IS_REFERENCED; SQLSTATE: 23000

            Message: Cannot delete or update a parent row: a foreign key constraint fails

            InnoDB reports this error when you try to delete a parent row that has children, and a foreign key constraint fails. Delete the children first.


    ERROR 1366 Incorrect integer value
        ERROR 1366 Incorrect decimal value: 'caramelo' for column 'a' at row 1
    ERROR 1264 Data truncation: Out of range value for column 'b' at row 1
        ERROR 1264 Out of range value for column 'b' at row 1
    ERROR 1292 Data truncation: Incorrect date value: '-3' for column 'b' at row 1
        1292 Incorrect date value: '-3' for column 'b' at row 1
    ERROR 1406 Data truncation: Data too long for column 'b' at row 1
    ERROR  1406 Data too long for column 'b' at row 1
