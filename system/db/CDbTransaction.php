<?php
/**
 * CDbTransaction class file
 */

/**
 * CDbTransaction represents a DB transaction.
 *
 * It is usually created by calling {@link CDbConnection::beginTransaction}.
 *
 * The following code is a common scenario of using transactions:
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
 * @property CDbConnection $connection The DB connection for this transaction.
 * @property boolean $active Whether this transaction is active.
 */
class CDbTransaction extends CComponent {
    /**
     * @var CDbConnection The DB connection for this transaction.
     */
    private $_connection = null;

    /**
     * @var boolean Whether this transaction is active.
     */
    private $_active;

    /**
     * Constructor.
     * @param CDbConnection $connection the connection associated with this transaction
     * @see CDbConnection::beginTransaction
     */
    public function __construct(CDbConnection $connection) {
        $this->_connection = $connection;
        $this->_active = true;
    }

    /**
     * Commits a transaction.
     * @throws CDbException if the transaction or the DB connection is not active.
     */
    public function commit() {
        if ($this->_active && $this->_connection->getActive()) {
            $this->_connection->getPdoInstance()->commit();
            $this->_active = false;
        } else
            throw new CDbException('CDbTransaction is inactive and cannot perform commit or roll back operations.');
    }

    /**
     * Rolls back a transaction.
     * @throws CDbException if the transaction or the DB connection is not active.
     */
    public function rollback() {
        if ($this->_active && $this->_connection->getActive()) {
            $this->_connection->getPdoInstance()->rollBack();
            $this->_active = false;
        } else
            throw new CDbException('CDbTransaction is inactive and cannot perform commit or roll back operations.');
    }

    /**
     * @return CDbConnection the DB connection for this transaction
     */
    public function getConnection() {
        return $this->_connection;
    }

    /**
     * @return boolean whether this transaction is active
     */
    public function getActive() {
        return $this->_active;
    }

    /**
     * @param boolean $value whether this transaction is active
     */
    protected function setActive($value) {
        $this->_active = $value;
    }
}
