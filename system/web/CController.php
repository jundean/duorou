<?php
class CController extends CComponent {
    public $defaultAction = 'default';

    public function run($action) {
        if (($action = $this->createAction($action)) !== null) {
            $this->runAction($action);
        } else {
            throw new CException(get_class($this) . '::' . $action . 'doesn\'t exist.');
        }
    }

    protected function createAction($action) {
        if ($action === '') {
            $action = $this->defaultAction;
        }
        $action .= 'Action';

        if (method_exists($this, $action)) {
            return $action;
        }
        return null;
    }

    public function runAction($action) {
        $this->$action();
    }

    public function display($tpl) {
        HRR::app()->getTemplate()->display($tpl);
    }

    public function assign($key, $value) {
        HRR::app()->getTemplate()->assign($key, $value);
    }
}
