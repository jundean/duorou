<?php
namespace util\form\validator;

class MaxLengthValidator extends \util\form\validator\ValidatorBase {
    public function validate($value) {
        $this->valid = true;

        $len = $this->getLength($value);

        if($this->validValue > 0 && $len > $this->validValue) {
            $this->valid = false;
        }

        return $this->valid;
    }
}