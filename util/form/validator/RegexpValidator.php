<?php
namespace util\form\validator;

class RegexpValidator extends \util\form\validator\ValidatorBase {

    public function validate($value) {
        $regexp = $this->validValue;

        $exclude = isset($this->rule[2]) && $this->rule[2] ? true : false;

        if ($exclude) {
            //符合正则表达式则异常
            $this->valid = !preg_match($regexp, $value);
        } else {
            //不符合正则表达式则异常
            $this->valid = preg_match($regexp, $value);
        }

        return $this->valid;
    }
}