<?php
/**
 * 模板类适配器，配合框架的模板组件使用
 */
class CTemplateAdapter extends CComponent {
    /**
     * @var string the path which the classes relative to.
     */
    private static $_relativePath = 'system.web.template.';

    /**
     * @var array all classes this component needs.
     */
    private static $_dependentClasses = array(
        'required' => array(
            'PhpTemplate',
        ),
    );

    /**
     * @var CTemplate the real template instance.
     */
    private $_template = null;

    /**
     * Constructor:
     * load classes necessary;
     * create a CTemplate instance;
     */
    public function __construct() {
        foreach (self::$_dependentClasses as $key => $classes) {
            $autoload = ($key === 'required') ? true : false;
            foreach ($classes as $class) {
                HRR::import(self::$_relativePath . $class, $autoload);
            }
        }
        if (!($this->_template instanceof PhpTemplate)) {
            $this->_template = new PhpTemplate();
        }
    }

    /**
     * Get properties of CComponent instead.
     */
    public function __get($propertyName) {
        return $this->_template->$propertyName;
    }

    /**
     * Set properties of CComponent instead.
     */
    public function __set($propertyName, $value) {
        $this->_template->$propertyName = $value;
    }

    /**
     * Call CComponent's methods instead.
     */
    public function __call($method, $arguments) {
        $callback = array($this->_template, $method);
        if (is_callable($callback)) {
            return call_user_func_array($callback, $arguments);
        } else {
            // trigger an error
            throw new CException('call undefine function!' . $method);
        }
     }
}
