<?php
defined('HRR_PATH') or define('HRR_PATH', dirname(__FILE__));

class HRRBase {
    private static $_classMap = array();

    private static $_imports = array();

    private static $_aliases = array('system' => HRR_PATH);

    private static $_app;

    public static function autoload($className) {
        if (isset(self::$_classMap[$className])) {
            include(self::$_classMap[$className]);
        } elseif (isset(self::$_coreClasses[$className])) {
            include(HRR_PATH . self::$_coreClasses[$className]);
        } else {
            $namespace = str_replace('\\', '.', ltrim($className, '\\'));
            if (($path = self::getPathOfAlias($namespace)) !== false) {
                include($path . '.php');
            } else {
                return false;
            }
            return class_exists($className, false) || interface_exists($className, false);
        }
        return true;
    }

    public static function getPathOfAlias($alias) {
        if (isset(self::$_aliases[$alias])) {
            return self::$_aliases[$alias];
        } elseif (($pos = strpos($alias, '.')) !== false) {
            $rootAlias = substr($alias, 0, $pos);
            if (isset(self::$_aliases[$rootAlias])) {
                return self::$_aliases[$alias] = rtrim(self::$_aliases[$rootAlias] . '/' . str_replace('.', '/', substr($alias, $pos+1)), '/');
            }
        }
        return false;
    }

    public static function setPathOfAlias($alias, $path) {
        if (empty($path)) {
            unset(self::$_aliases[$alias]);
        } else {
            self::$_aliases[$alias] = rtrim($path, '/');
        }
    }

    public static function import($alias, $forceInclude = false) {
        if (isset(self::$_imports[$alias])) {
            return self::$_imports[$alias];
        }
        if (false === ($pos = strrpos($alias, '.'))) {
            if ($forceInclude && self::autoload($alias)) {
                return self::$_imports[$alias] = $alias;
            }
        }
        $className = (string)substr($alias, $pos+1);
        if (class_exists($className, false) || interface_exists($className, false)) {
            return self::$_imports[$alias] = $className;
        }
        if (($path = self::getPathOfAlias($alias)) !== false) {
            $filepath = $path . '.php';
            if (is_file($filepath)) {
                if ($forceInclude) {
                    require($filepath);
                    self::$_imports[$alias] = $className;
                } else {
                    self::$_classMap[$className] = $filepath;
                }
                return $className;
            } else {
                throw new CException('Alias ' . $alias . ' is invalid.');
            }
        } else {
            throw new CException('Alias ' . $alias . ' is invalid.');
        }
    }

    public static function createApplication($className, $config = null) {
        return new $className($config);
    }

    public static function createWebApplication($config = null) {
        return self::createApplication('CWebApplication', $config);
    }

    public static function setApplication($app) {
        if ($app instanceof CApplication) {
            self::$_app = $app;
        } else {
            throw new CException('The given app must be instance of CApplication');
        }
    }

    public static function app() {
        return self::$_app;
    }

    public static function createComponent($config) {
        if (is_string($config)) {
            $className = $config;
        } elseif (isset($config['class'])) {
            $className = $config['class'];
            unset($config['class']);
        } else {
            throw new CException('The given config ' . print_r($config, true) . ' must have "class".');
        }

        if (!class_exists($className, false)) {
            $className = self::import($className, true);
        }

        $object = new $className();
        foreach ($config as $key => $val) {
            $object->$key = $val;
        }
        return $object;
    }

    private static $_coreClasses = array(
            'CComponent'      => '/base/CComponent.php',
            'CException'      => '/base/CException.php',
            'CModule'         => '/base/CModule.php',
            'CApplication'    => '/base/CApplication.php',
            'CWebApplication' => '/web/CWebApplication.php',
            'CController'     => '/web/CController.php'
    );

}

spl_autoload_register(array('HRRBase', 'autoload'));
