<?php
namespace util\form\validator;

class RequiredValidator extends \util\form\validator\ValidatorBase {

    /**
     * 校验value是否有值
     * @param $value
     * @return bool
     */
    public function validate($value) {
        $this->valid = true;

        //非必须
        if (!$this->validValue) {
            return $this->valid;
        }

        if($value === '' || $value === null) {
            $this->valid = false;
        }

        return $this->valid;
    }
}