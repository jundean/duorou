<?php
abstract class CModule extends CComponent {
    private $_basePath = null;

    private $_components = array();

    private $_componentsConfig = array();

    public $preloadComponents = array();

    public function __construct($config) {
        if (is_string($config)) {
            $config = require($config);
        }
        if (isset($config['basePath'])) {
            $this->setBasePath($config['basePath']);
            unset($config['basePath']);
        }

        $this->configure($config);
        $this->preloadComponents();

        $this->init();
    }

    public function __get($key) {
        if ($this->hasComponent($key)) {
            return $this->getComponent($key);
        } else {
            return parent::__get($key);
        }
    }

    public function __isset($key) {
        if ($this->hasComponent($key)) {
            return $this->getComponent($key) !== null;
        } else {
            return parent::__isset($key);
        }
    }

    public function getBasePath() {
        if ($this->_basePath === null) {
            $class = new ReflectionObject(get_class($this));
            return $this->_basePath = dirname($class->getFileName());
        } else {
            return $this->_basePath;
        }
    }

    public function setBasePath($basePath) {
        if (false === ($this->_basePath = realpath($basePath)) || !is_dir($this->_basePath)) {
            throw new CException('The given base path ' . $basePath . ' is invalid.');
        }
    }

    public function configure($config) {
        if (is_array($config)) {
            foreach ($config as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function hasComponent($key) {
        return isset($this->_components[$key]) || isset($this->_componentsConfig[$key]);
    }

    public function getComponent($key) {
        if (isset($this->_components[$key])) {
            return $this->_components[$key];
        } else if (isset($this->_componentsConfig[$key])) {
            $config = $this->_componentsConfig[$key];
            $component = HRR::createComponent($config);
            $component->init();
            return $this->_components[$key] = $component;
        } else {
            throw new CException('The component ' . $key . ' doesn\'t exist.');
        }
    }

    public function setComponent($key, $config, $merge = true) {
        if ($config === null) {
            unset($this->_components[$key]);
            return;
        } elseif (isset($this->_components[$key])) {
            if (isset($config['class']) && get_class($this->_components[$key]) !== $config['class']) {
                unset($this->_components[$key]);
                $this->_componentsConfig[$key] = $config;
                return;
            }
            foreach ($config as $k => $v) {
                if ($k !== 'class') {
                    $this->_components[$key]->$k = $v;
                }
            }
        } elseif (isset($this->_componentsConfig[$key], $this->_componentsConfig[$key]['class'], $config['class']) && $this->_componentsConfig[$key]['class'] !== $config['class']) {
            $this->_componentsConfig[$key] = $config;
            return;
        }
        if (isset($this->_componentsConfig[$key]['class']) && $merge) {
            $this->_componentsConfig[$key] = array_merge($this->_componentsConfig[$key], $config);
        } else {
            $this->_componentsConfig[$key] = $config;
        }
    }

    public function setComponents($configs) {
        foreach ($configs as $key => $config) {
            $this->setComponent($key, $config);
        }
    }

    public function setAlias($mappings) {
        foreach ($mappings as $name => $alias) {
            if (false !== ($path = HRR::getPathOfAlias($alias))) {
                HRR::setPathOfAlias($name, $path);
            } else {
                HRR::setPathOfAlias($name, $alias);
            }
        }
    }

    public function setImport($aliases) {
        foreach ($aliases as $alias) {
            HRR::import($alias);
        }
    }

    public function preloadComponents() {
        if (is_array($this->preloadComponents)) {
            foreach ($this->preloadComponents as $componentId) {
                $this->getComponent($componentId);
            }
        }
    }
}
