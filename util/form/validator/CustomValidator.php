<?php
namespace util\form\validator;

class CustomValidator extends \util\form\validator\ValidatorBase {

    public function __construct($rule, $ruleOption) {
        $this->rule = $rule;
        $this->ruleOption = $ruleOption;
    }

    public function validate($value) {
        $module = $this->rule[0];                 //类名or对象
        $function = $this->rule[1];               //回调函数
        $paramList = (array)$this->rule[2];       //回调函数传参
        array_unshift($paramList, $value);        //将value补充到第一个，传递给回调函数

        //回调
        list($this->valid, $this->errorMessage) = call_user_func_array(array($module, $function), $paramList);
        return $this->valid;
    }
}