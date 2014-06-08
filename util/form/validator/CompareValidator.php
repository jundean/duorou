<?php
namespace util\form\validator;

/**
 *
 * Class CompareValidator
 *
 * array('compare', 'this <= maxValueField', '价格必须小于哦', $this),
 * array('compare', 'this > 0', '价格必须大于0哦'),
 */
class CompareValidator extends \util\form\validator\ValidatorBase {
    private $form = null;

    public function __construct($rule, $ruleOption, $form) {
        parent::__construct($rule, $ruleOption);

        $this->form = $form;
    }

    /**
     * @param $value
     * @return mixed
     * @throws Exception
     */
    public function validate($value) {
        $expression = new \util\form\expression\Expression($this->rule[0], $this->form, $value);
        $this->valid = $expression->execute();
        return $this->valid;
    }
}
