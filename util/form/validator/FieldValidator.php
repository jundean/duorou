<?php
namespace util\form\validator;

class FieldValidator {

    /**
     * 字段校验错误信息
     * @var string
     */
    private $errorMessage = "";

    /**
     * 字段规则选项
     * @var array
     */
    private $ruleOption = array(
        'enableHtmlTag' => false, // 是否允许html标签，默认会使用strip_tags过滤
        'strLenType' => 'SYMBOL', // SYMBOL按字计算，BYTE按照字节计算
    );

    /**
     * rules生成的所有校验器
     * @var array
     */
    private $validators = array();

    private $form = null;

    public function __construct($fieldConfig, $form) {
        $rules = $fieldConfig['rules'];

        if (isset($fieldConfig['option'])) {
            $this->ruleOption = array_merge($this->ruleOption, $fieldConfig['option']);
        }

        $this->form = $form;
        foreach($rules as $rule) {
            if(count($rule) < 2) {
                throw new \CException('arguments length at least 2');
            }

            $validator = $this->createValidator($rule);
            if($validator) {
                $this->validators[] = $validator;
            }
        }
    }

    protected function createValidator($rule) {
        $mode = array_shift($rule);
        $ruleOption = $this->ruleOption;

        switch ($mode) {
            case 'required':
                $validator = new \util\form\validator\RequiredValidator($rule, $ruleOption);
                break;

            case 'maxLength':
                $validator = new \util\form\validator\MaxLengthValidator($rule, $ruleOption);
                break;

            case 'minLength':
                $validator = new \util\form\validator\MinLengthValidator($rule, $ruleOption);
                break;

            case 'min':
                $validator = new \util\form\validator\MinValueValidator($rule, $ruleOption);
                break;

            case 'max':
                $validator = new \util\form\validator\MaxValueValidator($rule, $ruleOption);
                break;

            case 'regexp':
                $validator = new \util\form\validator\RegexpValidator($rule, $ruleOption);
                break;

            case 'format':
                $validator = new \util\form\validator\FormatValidator($rule, $ruleOption);
                break;

            case 'compare':
                $validator = new \util\form\validator\CompareValidator($rule, $ruleOption, $this->form);
                break;

            case 'php_custom':
                $validator = new \util\form\validator\CustomValidator($rule, $ruleOption);
                break;

            default:
                $validator = null;
                break;
        }

        return $validator;
    }

    /**
     * 校验字段值
     * @param $value
     * @return bool
     */
    public function validate($value)  {
        //去除html标签
        if(!$this->ruleOption['enableHtmlTag']) {
            $value = $this->removeHtmlTags($value);
        }

        $this->errorMessage = "";
        foreach($this->validators as $validator) {
            if(!$validator->validate($value)) {
                $this->errorMessage = $validator->getErrorMessage();
                return false;
            }
        }

        return true;
    }

    /**
     * 去除html 标签
     * @param $value
     * @return string
     */
    private function removeHtmlTags($value) {
        if(is_array($value)) {
            foreach($value as &$val) {
                $val = strip_tags($val);
            }
        } else {
            $value = strip_tags($value);
        }

        return $value;
    }

    public function getErrorMessage() {
        return $this->errorMessage;
    }

}