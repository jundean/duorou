<?php
class CWebApplication extends CApplication {
    public $defaultController = 'index';

    public function processRequest() {
        $this->runController($this->getUrlManager()->parseUrl($this->getRequest()));
    }

    public function runController($route) {
        if (null !== ($ca = $this->createController($route))) {
            list($c, $a) = $ca;
            $c->init();
            $c->run($a);
        } else {
            throw new CException('The route ' . $route .' is invalid.');
        }
    }

    public function createController($route) {
        if ('' === ($route = trim($route, '/'))) {
            $route = $this->defaultController;
        }
        $route .= '/';
        $basePath = null;
        while (false !== ($pos = strpos($route, '/'))) {
            $id = substr($route, 0, $pos);
            $route = (string)substr($route, $pos+1);
            if (!preg_match('/^\w+$/', $id)) {
                return null;
            }
            if (!isset($basePath)) {
                $basePath = $this->getControllerPath();
            }
            $className = ucfirst($id);
            $classFile = $basePath . '/' . $className . '.php';
            if (is_file($classFile)) {
                if (!class_exists($className)) {
                    require($classFile);
                }
                if (class_exists($className) && is_subclass_of($className, 'CController')) {
                    return array(
                        new $className(),
                        $this->parseActionParams($route)
                    );
                }
                return null;
            }
            $basePath .= '/' . $id;
        }
    }

    public function getControllerPath() {
        return $this->getBasePath() . '/controller';
    }

    public function parseActionParams($route) {
        if (false !== ($pos = strpos($route, '/'))) {
            return substr($route, 0, $pos);
        } else {
            return $route;
        }
    }

    public function getSystemViewPath() {
        return $this->getBasePath() . '/view';
    }

    public function getHelperPath() {
        return $this->getBasePath() . '/helper';
    }

    protected function registerCoreComponent() {
        parent::registerCoreComponent();
        $components = array(
            'request' => array(
                'class' => 'system.web.CHttpRequest'
            ),
            'urlManager' => array(
                'class' => 'system.web.CUrlManager'
            ),
            'template' => array(
                'class' => 'system.web.template.CTemplateAdapter',
                'templateDir' => $this->getSystemViewPath(),
                'helperDir' => $this->getHelperPath(),
            )
        );
        $this->setComponents($components);
    }

    public function getRequest() {
        return $this->getComponent('request');
    }

    public function getUrlManager() {
        return $this->getComponent('urlManager');
    }

    public function getTemplate() {
        return $this->getComponent('template');
    }
}
