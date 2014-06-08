<?php
namespace util\form\validator;

class MaxValueValidator extends \util\form\validator\ValidatorBase {
    public function validate($value) {
        $this->valid = true;

        if($this->validValue > 0 && $value > $this->validValue) {
            $this->valid = false;
        }

        return $this->valid;
    }
}