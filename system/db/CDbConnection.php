<?php
/**
 * CDbConnection class file
 */

/**
 * CDbConnection represents a connection to a database.
 *
 * CDbConnection works together with {@link CDbCommand}, {@link CDbDataReader}
 * and {@link CDbTransaction} to provide data access to various DBMS
 * in a common set of APIs. They are a thin wrapper of the {@link http://www.php.net/manual/en/ref.pdo.php PDO}
 * PHP extension.
 *
 * To establish a connection, set {@link setActive active} to true after
 * specifying {@link connectionString}, {@link username} and {@link password}.
 *
 * The following example shows how to create a CDbConnection instance and establish
 * the actual connection:
 * <pre>
 * $connection=new CDbConnection($dsn,$username,$password);
 * $connection->active=true;
 * </pre>
 *
 * After the DB connection is established, one can execute an SQL statement like the following:
 * <pre>
 * $command=$connection->createCommand($sqlStatement);
 * $command->execute();   // a non-query SQL statement execution
 * // or execute an SQL query and fetch the result set
 * $reader=$command->query();
 *
 * // each $row is an array representing a row of data
 * foreach($reader as $row) ...
 * </pre>
 *
 * One can do prepared SQL execution and bind parameters to the prepared SQL:
 * <pre>
 * $command=$connection->createCommand($sqlStatement);
 * $command->bindParam($name1,$value1);
 * $command->bindParam($name2,$value2);
 * $command->execute();
 * </pre>
 *
 * To use transaction, do like the following:
 * <pre>
 * $transaction=$connection->beginTransaction();
 * try
 * {
 *    $connection->createCommand($sql1)->execute();
 *    $connection->createCommand($sql2)->execute();
 *    //.... other SQL executions
 *    $transaction->commit();
 * }
 * catch(Exception $e)
 * {
 *    $transaction->rollback();
 * }
 * </pre>
 *
 * CDbConnection also provides a set of methods to support setting and querying
 * of certain DBMS attributes, such as {@link getNullConversion nullConversion}.
 *
 * @property boolean $active Whether the DB connection is established.
 * @property PDO $pdoInstance The PDO instance, null if the connection is not established yet.
 * @property CDbTransaction $currentTransaction The currently active transaction. Null if no active transaction.
 * @property CDbSchema $schema The database schema for the current connection.
 * @property CDbCommandBuilder $commandBuilder The command builder.
 * @property string $lastInsertID The row ID of the last row inserted, or the last value retrieved from the sequence object.
 * @property mixed $columnCase The case of the column names.
 * @property mixed $nullConversion How the null and empty strings are converted.
 * @property boolean $autoCommit Whether creating or updating a DB record will be automatically committed.
 * @property boolean $persistent Whether the connection is persistent or not.
 * @property string $driverName Name of the DB driver.
 * @property string $clientVersion The version information of the DB driver.
 * @property string $connectionStatus The status of the connection.
 * @property boolean $prefetch Whether the connection performs data prefetching.
 * @property string $serverInfo The information of DBMS server.
 * @property string $serverVersion The version information of DBMS server.
 * @property integer $timeout Timeout settings for the connection.
 * @property array $attributes Attributes (name=>value) that are previously explicitly set for the DB connection.
 */
class CDbConnection extends CComponent {
    /**
     * @var string the path which the classes relative to.
     */
    private static $_relativePath = 'system.db.';

    /**
     * @var array the classes that this component needs.
     */
    private static $_dependentClasses = array(
        'required' => array(
            'CDbConnection'
        ),
        'optional' => array(
            'CDbException',
            'CDbCommand',
            'CDbTransaction',
            'CDbDataReader',
            'schema.CDbCommandBuilder',
            'schema.CDbSchema',
            'schema.mysql.CDbMysqlSchema'
        )
    );

    /**
     * @var string The Data Source Name, or DSN, contains the information required to connect to the database.
     * @see http://www.php.net/manual/en/function.PDO-construct.php
     */
    public $connectionString;

    /**
     * @var string the username for establishing DB connection. Defaults to empty string.
     */
    public $username = '';

    /**
     * @var string the password for establishing DB connection. Defaults to empty string.
     */
    public $password = '';

    /**
     * @var boolean whether the database connection should be automatically established.
     * This property is only effective when we use CDbConnection as components.
     * @see CComponent
     */
    public $autoConnect = true;

    /**
     * @var string the charset used for database connection. The property is only used
     * for MySQL and PostgreSQL databases. Defaults to null, meaning using default charset
     * as specified by the database.
     */
    public $charset;

    /**
     * @var boolean whether to turn on prepare emulation. Defaults to false, meaning PDO
     * will use the native prepare support if available. For some databases (such as MySQL),
     * this may need to be set true so that PDO can emulate the prepare support to bypass
     * the buggy native prepare support. Note, this property is only effective for PHP 5.1.3 or above.
     * The default value is null, which will not change the ATTR_EMULATE_PREPARES value of PDO.
     */
    public $emulatePrepare;

    /**
     * @var string the default prefix for table names. Defaults to null, meaning no table prefix.
     * By setting this property, any token like '{{tableName}}' in {@link CDbCommand::text} will
     * be replaced by 'prefixTableName', where 'prefix' refers to this property value.
     */
    public $tablePrefix;

    /**
     * @var array list of SQL statements that should be executed right after the DB connection is established.
     */
    public $initSQLs;

    /**
     * @var array mapping between PDO driver and schema class name.
     * A schema class can be specified using path alias.
     */
    public $driverMap = array(
        'pgsql' => 'CDbPgsqlSchema',    // PostgreSQL
        'mysqli' => 'CDbMysqlSchema',   // MySQL
        'mysql' => 'CDbMysqlSchema',    // MySQL
        'sqlite' => 'CDbSqliteSchema',  // sqlite 3
        'sqlite2' => 'CDbSqliteSchema', // sqlite 2
        'mssql' => 'CDbMssqlSchema',    // Mssql driver on windows hosts
        'dblib' => 'CDbMssqlSchema',    // dblib drivers on linux (and maybe others os) hosts
        'sqlsrv' => 'CDbMssqlSchema',   // Mssql
        'oci' => 'CDbOciSchema'         // Oracle driver
    );

    /**
     * @var string Custom PDO wrapper class.
     */
    public $pdoClass = 'PDO';

    /**
     * @var array Attributes (name=>value) that are previously explicitly set for the DB connection.
     */
    private $_attributes = array();

    /**
     * @var boolean Whether the DB connection is established.
     */
    private $_active = false;

    /**
     * @var PDO The PDO instance, null if the connection is not established yet.
     */
    private $_pdo;

    /**
     * @var CDbTransaction The currently active transaction. Null if no active transaction.
     */
    private $_transaction;

    /**
     * @var CDbSchema The database schema for the current connection.
     */
    private $_schema;

    /**
     * Constructor.
     * Note, the DB connection is not established when this connection
     * instance is created. Set {@link setActive active} property to true
     * to establish the connection.
     * @param string $dsn The Data Source Name, or DSN, contains the information required to connect to the database.
     * @param string $username The user name for the DSN string.
     * @param string $password The password for the DSN string.
     * @see http://www.php.net/manual/en/function.PDO-construct.php
     */
    public function __construct($dsn = '', $username = '', $password = '') {
        $this->connectionString = $dsn;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Close the connection when serializing.
     * @return array List of properties to be serialized.
     */
    public function __sleep() {
        $this->close();
        return array_keys(get_object_vars($this));
    }

    /**
     * Init this component.
     * @see CComponent
     */
    public function init() {
        parent::init();
        foreach (self::$_dependentClasses as $key => $classes) {
            $autoload = ($key === 'required') ? true : false;
            foreach ($classes as $class) {
                HRR::import(self::$_relativePath . $class, $autoload);
            }
        }
        if ($this->autoConnect) {
            $this->setActive(true);
        }
    }

    /**
     * Returns a list of available PDO drivers.
     * @return array List of available PDO drivers.
     * @see http://www.php.net/manual/en/function.PDO-getAvailableDrivers.php
     */
    public static function getAvailableDrivers() {
        return PDO::getAvailableDrivers();
    }

    /**
     * Returns whether the DB connection is established.
     * @return boolean Whether the DB connection is established.
     */
    public function getActive() {
        return $this->_active;
    }

    /**
     * Open or close the DB connection.
     * @param boolean $value whether to open or close DB connection
     * @throws CDbException if connection fails
     */
    public function setActive($value) {
        if ($value != $this->_active) {
            if ($value)
                $this->open();
            else
                $this->close();
        }
    }

    /**
     * Opens DB connection if it is currently not
     * @throws CDbException if connection fails
     */
    protected function open() {
        if ($this->_pdo === null) {
            if (empty($this->connectionString))
                throw new CDbException('CDbConnection.connectionString cannot be empty.');
            try {
                $this->_pdo = $this->createPdoInstance();
                $this->initConnection($this->_pdo);
                $this->_active = true;
            } catch(PDOException $e) {
                throw new CDbException(
                    'CDbConnection failed to open the DB connection: ' . $e->getMessage(),
                    $e->getCode(), $e->errorInfo);
            }
        }
    }

    /**
     * Closes the currently active DB connection.
     * It does nothing if the connection is already closed.
     */
    protected function close() {
        $this->_pdo = null;
        $this->_active = false;
        $this->_schema = null;
    }

    /**
     * Creates the PDO instance.
     * When some functionalities are missing in the pdo driver, we may use
     * an adapter class to provide them.
     * @return PDO PDO instance.
     * @throws CDbException when failed to open DB connection
     */
    protected function createPdoInstance() {
        $pdoClass = $this->pdoClass;
        if (($pos = strpos($this->connectionString, ':')) !== false) {
            $driver = strtolower(substr($this->connectionString, 0, $pos));
            if ($driver === 'mssql' || $driver === 'dblib')
                $pdoClass = 'CMssqlPdoAdapter';
            elseif ($driver === 'sqlsrv')
                $pdoClass = 'CMssqlSqlsrvPdoAdapter';
        }

        if (!class_exists($pdoClass))
            throw new CDbException(
                sprintf('CDbConnection is unable to find PDO class "%s". Make sure PDO is installed correctly.', $pdoClass));

        try {
            return new $pdoClass($this->connectionString, $this->username, $this->password, $this->_attributes);
        } catch (Exception $e) {
            throw new CDbException('CDbConnection failed to open the DB connection: ' . $e->getMessage(),
                $e->getCode(), ($e instanceof PDOException) ? $e->errorInfo : array());
        }
    }

    /**
     * Initializes the open db connection.
     * This method is invoked right after the db connection is established.
     * The default implementation is to set the charset for MySQL and PostgreSQL database connections.
     * @param PDO $pdo the PDO instance
     */
    protected function initConnection($pdo) {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($this->emulatePrepare !== null && constant('PDO::ATTR_EMULATE_PREPARES'))
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $this->emulatePrepare);
        if ($this->charset !== null) {
            $driver = strtolower($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
            if (in_array($driver, array('pgsql', 'mysql', 'mysqli')))
                $pdo->exec('SET NAMES ' . $pdo->quote($this->charset));
        }
        if ($this->initSQLs !== null) {
            foreach($this->initSQLs as $sql)
                $pdo->exec($sql);
        }
    }

    /**
     * Returns the PDO instance, null if the connection is not established yet
     * @return PDO the PDO instance.
     */
    public function getPdoInstance() {
        return $this->_pdo;
    }

    /**
     * Creates a command for execution.
     * @param mixed $query the DB query to be executed. This can be either a string representing a SQL statement,
     * or an array representing different fragments of a SQL statement. Please refer to {@link CDbCommand::__construct}
     * for more details about how to pass an array as the query. If this parameter is not given,
     * you will have to call query builder methods of {@link CDbCommand} to build the DB query.
     * @return CDbCommand the command for execution.
     */
    public function createCommand($query = null) {
        $this->setActive(true);
        return new CDbCommand($this, $query);
    }

    /**
     * Returns the currently active transaction. Null if no active transaction.
     * @return CDbTransaction the currently active transaction.
     */
    public function getCurrentTransaction() {
        if ($this->_transaction !== null) {
            if ($this->_transaction->getActive())
                return $this->_transaction;
        }
        return null;
    }

    /**
     * Starts a transaction.
     * @return CDbTransaction the transaction.
     */
    public function beginTransaction() {
        $this->setActive(true);
        $this->_pdo->beginTransaction();
        return $this->_transaction = new CDbTransaction($this);
    }

    /**
     * Returns the database schema for the current connection
     * @throws CDbException if CDbConnection does not support reading schema for specified database driver
     */
    public function getSchema() {
        if ($this->_schema !== null)
            return $this->_schema;
        else {
            $driver = $this->getDriverName();
            if (isset($this->driverMap[$driver]))
                return $this->_schema = new $this->driverMap[$driver]($this);
            else
                throw new CDbException(
                    sprintf('CDbConnection does not support reading schema for %s database.', $driver));
        }
    }

    /**
     * Returns the SQL command builder for the current DB connection.
     */
    public function getCommandBuilder() {
        return $this->getSchema()->getCommandBuilder();
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     * @param string $sequenceName name of the sequence object (required by some DBMS)
     * @return integer the ID of the last inserted row or sequence value.
     * @see http://www.php.net/manual/en/function.PDO-lastInsertId.php
     */
    public function getLastInsertID($sequenceName = '') {
        $this->setActive(true);
        return $this->_pdo->lastInsertId($sequenceName);
    }

    /**
     * Quotes a string value for use in a query.
     * @param string $str string to be quoted
     * @see http://www.php.net/manual/en/function.PDO-quote.php
     */
    public function quoteValue($str) {
        if (is_int($str) || is_float($str))
            return $str;

        $this->setActive(true);
        if (($value = $this->_pdo->quote($str)) !== false)
            return $value;
        else  // the driver doesn't support quote (e.g. oci)
            return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
    }

    /**
     * Quotes a table name for use in a query.
     * If the table name contains schema prefix, the prefix will also be properly quoted.
     * @param string $name table name
     */
    public function quoteTableName($name) {
        return $this->getSchema()->quoteTableName($name);
    }

    /**
     * Quotes a column name for use in a query.
     * If the column name contains prefix, the prefix will also be properly quoted.
     * @param string $name column name
     */
    public function quoteColumnName($name) {
        return $this->getSchema()->quoteColumnName($name);
    }

    /**
     * Determines the PDO type for the specified PHP type.
     * @param string $type The PHP type (obtained by gettype() call).
     */
    public function getPdoType($type) {
        static $map = array(
            'boolean' => PDO::PARAM_BOOL,
            'integer' => PDO::PARAM_INT,
            'string' => PDO::PARAM_STR,
            'resource' => PDO::PARAM_LOB,
            'NULL' => PDO::PARAM_NULL
        );
        return isset($map[$type]) ? $map[$type] : PDO::PARAM_STR;
    }

    /**
     * Returns the case of the column names
     * @see http://www.php.net/manual/en/pdo.setattribute.php
     */
    public function getColumnCase() {
        return $this->getAttribute(PDO::ATTR_CASE);
    }

    /**
     * Sets the case of the column names.
     * @param mixed $value the case of the column names
     * @see http://www.php.net/manual/en/pdo.setattribute.php
     */
    public function setColumnCase($value) {
        $this->setAttribute(PDO::ATTR_CASE, $value);
    }

    /**
     * Returns how the null and empty strings are converted.
     * @see http://www.php.net/manual/en/pdo.setattribute.php
     */
    public function getNullConversion() {
        return $this->getAttribute(PDO::ATTR_ORACLE_NULLS);
    }

    /**
     * Sets how the null and empty strings are converted.
     * @param mixed $value how the null and empty strings are converted
     * @see http://www.php.net/manual/en/pdo.setattribute.php
     */
    public function setNullConversion($value) {
        $this->setAttribute(PDO::ATTR_ORACLE_NULLS, $value);
    }

    /**
     * Returns whether creating or updating a DB record will be automatically committed.
     * Some DBMS (such as sqlite) may not support this feature.
     */
    public function getAutoCommit() {
        return $this->getAttribute(PDO::ATTR_AUTOCOMMIT);
    }

    /**
     * Sets whether creating or updating a DB record will be automatically committed.
     * Some DBMS (such as sqlite) may not support this feature.
     * @param boolean $value whether creating or updating a DB record will be automatically committed.
     */
    public function setAutoCommit($value) {
        $this->setAttribute(PDO::ATTR_AUTOCOMMIT, $value);
    }

    /**
     * Returns whether the connection is persistent or not.
     * Some DBMS (such as sqlite) may not support this feature.
     */
    public function getPersistent() {
        return $this->getAttribute(PDO::ATTR_PERSISTENT);
    }

    /**
     * Sets whether the connection is persistent or not.
     * Some DBMS (such as sqlite) may not support this feature.
     * @param boolean $value whether the connection is persistent or not
     */
    public function setPersistent($value) {
        return $this->setAttribute(PDO::ATTR_PERSISTENT, $value);
    }

    /**
     * Returns the name of the DB driver
     */
    public function getDriverName() {
        if (($pos=strpos($this->connectionString, ':')) !== false)
            return strtolower(substr($this->connectionString, 0, $pos));
    }

    /**
     * Returns the version information of the DB driver.
     */
    public function getClientVersion() {
        return $this->getAttribute(PDO::ATTR_CLIENT_VERSION);
    }

    /**
     * Returns the status of the connection.
     * Some DBMS (such as sqlite) may not support this feature.
     */
    public function getConnectionStatus() {
        return $this->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    }

    /**
     * Returns whether the connection performs data prefetching.
     */
    public function getPrefetch() {
        return $this->getAttribute(PDO::ATTR_PREFETCH);
    }

    /**
     * Returns the information of DBMS server.
     * This can be used to test whether the connection has been closed.
     * @see {mysqli::ping} http://cn2.php.net/manual/en/mysqli.ping.php
     */
    public function getServerInfo() {
        return $this->getAttribute(PDO::ATTR_SERVER_INFO);
    }

    /**
     * Returns the version information of DBMS server.
     */
    public function getServerVersion() {
        return $this->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * Returns the timeout settings for the connection.
     */
    public function getTimeout() {
        return $this->getAttribute(PDO::ATTR_TIMEOUT);
    }

    /**
     * Obtains a specific DB connection attribute information.
     * @param integer $name the attribute to be queried
     * @see http://www.php.net/manual/en/function.PDO-getAttribute.php
     */
    public function getAttribute($name) {
        $this->setActive(true);
        return $this->_pdo->getAttribute($name);
    }

    /**
     * Sets an attribute on the database connection.
     * @param integer $name the attribute to be set
     * @param mixed $value the attribute value
     * @see http://www.php.net/manual/en/function.PDO-setAttribute.php
     */
    public function setAttribute($name, $value) {
        if($this->_pdo instanceof PDO)
            $this->_pdo->setAttribute($name, $value);
        else
            $this->_attributes[$name] = $value;
    }

    /**
     * Returns the attributes that are previously explicitly set for the DB connection.
     * @see setAttributes
     */
    public function getAttributes() {
        return $this->_attributes;
    }

    /**
     * Sets a set of attributes on the database connection.
     * @param array $values attributes (name=>value) to be set.
     * @see setAttribute
     */
    public function setAttributes($values) {
        foreach($values as $name => $value)
            $this->_attributes[$name] = $value;
    }
}
