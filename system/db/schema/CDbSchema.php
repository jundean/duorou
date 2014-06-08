<?php
/**
 * CDbSchema class file.
 */

/**
 * CDbSchema is the base class for retrieving metadata information.
 *
 * @property CDbConnection $dbConnection Database connection. The connection is active.
 * @property CDbCommandBuilder $commandBuilder The SQL command builder for this connection.
 */
abstract class CDbSchema extends CComponent {
    /**
     * @var CDbConnection Database connection. The connection is active.
     */
    private $_connection;

    /**
     * @var CDbCommandBuilder The SQL command builder for this connection.
     */
    private $_builder;

    /**
     * Constructor.
     * @param CDbConnection $conn database connection.
     */
    public function __construct($conn) {
        $this->_connection = $conn;
    }

    /**
     * @return CDbConnection database connection. The connection is active.
     */
    public function getDbConnection() {
        return $this->_connection;
    }

    /**
     * @return CDbCommandBuilder the SQL command builder for this connection.
     */
    public function getCommandBuilder() {
        if ($this->_builder !== null)
            return $this->_builder;
        else
            return $this->_builder = $this->createCommandBuilder();
    }

    /**
     * Creates a command builder for the database.
     * This method may be overridden by child classes to create a DBMS-specific command builder.
     * @return CDbCommandBuilder command builder instance
     */
    protected function createCommandBuilder() {
        return new CDbCommandBuilder($this);
    }

    /**
     * Quotes a table name for use in a query.
     * If the table name contains schema prefix, the prefix will also be properly quoted.
     * @param string $name table name
     * @return string the properly quoted table name
     * @see quoteSimpleTableName
     */
    public function quoteTableName($name) {
        if (strpos($name, '.') === false)
            return $this->quoteSimpleTableName($name);
        $parts = explode('.', $name);
        foreach ($parts as $i => $part)
            $parts[$i] = $this->quoteSimpleTableName($part);
        return implode('.', $parts);

    }

    /**
     * Quotes a simple table name for use in a query.
     * A simple table name does not schema prefix.
     * @param string $name table name
     * @return string the properly quoted table name
     */
    public function quoteSimpleTableName($name) {
        return "'" . $name . "'";
    }

    /**
     * Quotes a column name for use in a query.
     * If the column name contains prefix, the prefix will also be properly quoted.
     * @param string $name column name
     * @return string the properly quoted column name
     * @see quoteSimpleColumnName
     */
    public function quoteColumnName($name) {
        if (($pos = strrpos($name, '.')) !== false) {
            $prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
            $name = substr($name, $pos + 1);
        } else
            $prefix = '';
        return $prefix . ($name === '*' ? $name : $this->quoteSimpleColumnName($name));
    }

    /**
     * Quotes a simple column name for use in a query.
     * A simple column name does not contain prefix.
     * @param string $name column name
     * @return string the properly quoted column name
     */
    public function quoteSimpleColumnName($name) {
        return '"' . $name . '"';
    }
}
