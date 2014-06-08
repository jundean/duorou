<?php
/**
 * CDbCommandBuilder class file.
 */

/**
 * CDbCommandBuilder provides basic methods to create query commands.
 */
class CDbCommandBuilder extends CComponent {
    /**
     * @var CDbSchema The schema for this command builder.
     */
    private $_schema;

    /**
     * @var CDbConnection Database connection.
     */
    private $_connection;

    /**
     * @param CDbSchema $schema the schema for this command builder
     */
    public function __construct($schema) {
        $this->_schema = $schema;
        $this->_connection = $schema->getDbConnection();
    }

    /**
     * Alters the SQL to apply LIMIT and OFFSET.
     * Default implementation is applicable for PostgreSQL, MySQL and SQLite.
     * @param string $sql SQL query string without LIMIT and OFFSET.
     * @param integer $limit maximum number of rows, -1 to ignore limit.
     * @param integer $offset row offset, -1 to ignore offset.
     * @return string SQL with LIMIT and OFFSET
     */
    public function applyLimit($sql, $limit, $offset) {
        if ($limit >= 0)
            $sql .= ' LIMIT ' . (int)$limit;
        if ($offset > 0)
            $sql .= ' OFFSET ' . (int)$offset;
        return $sql;
    }
}
