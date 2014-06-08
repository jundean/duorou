<?php
/**
 * CDbException class file.
 */

/**
 * CDbException represents an exception that is caused by some DB-related operations.
 */
class CDbException extends CException {
    /**
     * @var array the error info provided by a PDO exception. This is the same as returned
     * by {@link http://www.php.net/manual/en/pdo.errorinfo.php PDO::errorInfo}.
     */
    public $errorInfo;

    /**
     * Constructor.
     * @param string $message PDO error message
     * @param integer $code PDO error code
     * @param array $errorInfo PDO error info
     */
    public function __construct($message, $code = 0, $errorInfo = array()) {
        $this->errorInfo = $errorInfo;
        parent::__construct($message, $code);
    }

    /**
     * Returns a CDbException, reporting the query sql that throw an exception during preparing.
     * @param Exception $driverEx driver exception
     * @param string $sql the query sql
     */
    public static function driverExceptionDuringPrepare(Exception $driverEx, $sql) {
        $msg = "An exception occurred while preparing '" . $sql . "'"
             . ":\n" . $driverEx->getMessage();

        return new self($msg);
    }

    /**
     * Returns a CDbException, reporting the query sql and parameters that throw an exception during querying.
     * @param Exception $driverEx driver exception
     * @param string $sql the query sql
     * @param array $params the query parameters
     * @param array $errorInfo PDO error info
     */
    public static function driverExceptionDuringQuery(Exception $driverEx, $sql, $params = array(), $errorInfo = array()) {
        $msg = "An exception occurred while executing '" . $sql . "'";
        if ($params) {
            $msg .= " with params " . self::_formatParameters($params);
        }
        $msg .= ":\n" . $driverEx->getMessage();

        return new self($msg, 0, $errorInfo);
    }

    /**
     * Returns a human-readable representation of an array of parameters.
     * @param array $params
     */
    private static function _formatParameters($params) {
        return '[' . implode(', ', array_map(array(get_class(), "_handleParameter"), $params)) . ']';
    }

    /**
     * This properly handles binary data by returning a hex representation.
     * @param mixed $param
     */
    private static function _handleParameter($param) {
        $json = @json_encode($param);

        if (! is_string($json) || $json == 'null' && is_string($param)) {
            // JSON encoding failed, this is not a UTF-8 string.
            return '"\x' . implode('\x', str_split(bin2hex($param), 2)) . '"';
        }

        return $json;
    }
}
