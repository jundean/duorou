<?php
namespace util\form\validator;

class FormatValidator extends ValidatorBase {
    public function validate($value) {
        $this->valid = false;

        //获取正则表达式
        $formatType = strtoupper($this->rule[0]);

        $regexp = constant('\util\form\validator\RegexpConfig::' . $formatType);
        if (empty($regexp)) {
            return $this->valid;
        }

        if(preg_match($regexp, $value)) {
            $this->valid = true;
        }

        return $this->valid;
    }
}