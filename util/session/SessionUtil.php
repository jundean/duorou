<?php
/**
 * @brief session 相关操作封装，利用单例模式保证只有一次session_start
 * @author wujun
 * @date 2014-05-31
 */

namespace util\session;

class SessionUtil {
    private static $_instance = null;

    private function __construct() {
        session_start();
    }

    private function __clone() {}

    public static function getInstance() {
        if (self::$_instance === null) {
            return self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function setValue($key, $value) {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return $_SESSION[$key] = $value;
    }

    public function getValue($key, $default = null) {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return $default;
    }

    public function delete($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function clear() {
        session_unset();
        session_destroy();
        $_SESSION = array();
    }
}