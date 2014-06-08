<?php
abstract class CApplication extends CModule {
    private $_basePath;

    private $_runtimePath;

    public function __construct($config = null) {
        HRR::setApplication($this);
        if (is_string($config)) {
            $config = require($config);
        }

        if (isset($config['basePath'])) {
            $this->setBasePath($config['basePath']);
            unset($config['basePath']);
        } else {
            $this->setBasePath('application');
        }
        HRR::setPathOfAlias('application', $this->getBasePath());
        HRR::setPathOfAlias('webroot', dirname($_SERVER['SCRIPT_FILENAME']));

        if (isset($config['aliases'])) {
            $this->setAlias($config['aliases']);
            unset($config['aliases']);
        }

        $this->initSystemHandle();
        $this->registerCoreComponent();

        $this->configure($config);
        $this->preloadComponents();

        $this->init();
    }

    public function initSystemHandle() {
        if (HRR_DEBUG) {
            error_reporting(E_ALL);
        } else {
            error_reporting(0);
        }
        set_error_handler(array($this, 'handleError'));
        set_exception_handler(array($this, 'handleException'));
    }

    public function handleException($e) {
        echo '<pre>';
        echo $e->getMessage();
        echo '</pre>';
//        exit(1);
    }

    public function handleError($code, $message, $file, $line) {
        if ($code & error_reporting()) {
            echo '<pre>';
            echo $message . "\n" . $file . "\n" . $line;
            echo '</pre>';
//            exit(1);
        }
    }

    protected function registerCoreComponent() {
        $coreComponents = array(
            'db' => array(
                'class' => 'system.db.CDbConnection'
            )
        );
        $this->setComponents($coreComponents);
    }

    public function getBasePath() {
        return $this->_basePath;
    }

    public function setBasePath($path) {
        if (false === ($this->_basePath = realpath($path)) || !is_dir($this->_basePath)) {
            throw new CException('The given base path ' . $path . ' is invalid.');
        }
    }

    public function setTimeZone($timeZone) {
        date_default_timezone_set($timeZone);
    }

    public function getTimeZone() {
        return date_default_timezone_get();
    }

    public function getDb($dbConfig = 'db') {
        return $this->getComponent($dbConfig);
    }

    public function run() {
        $this->processRequest();
    }

    abstract public function processRequest();
}
