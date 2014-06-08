<?php
/**
 * CDbMysqlSchema class file.
 */

/**
 * CDbMysqlSchema is the class for retrieving metadata information from a MySQL database (version 4.1.x and 5.x).
 */
class CDbMysqlSchema extends CDbSchema {
    /**
     * Quotes a table name for use in a query.
     * A simple table name does not schema prefix.
     * @param string $name table name
     * @return string the properly quoted table name
     */
    public function quoteSimpleTableName($name) {
        return '`' . $name . '`';
    }

    /**
     * Quotes a column name for use in a query.
     * A simple column name does not contain prefix.
     * @param string $name column name
     * @return string the properly quoted column name
     */
    public function quoteSimpleColumnName($name) {
        return '`' . $name . '`';
    }
}
