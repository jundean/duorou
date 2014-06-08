<?php
/**
 * 获取表单验证规则，给前端验证使用
 * @param $form
 * @param $fieldName
 * @return string
 */

function get_data_rules($params) {
    if (empty($params) || empty($params['form']) || empty($params['name'])) {
        return '';
    }

    $form = $params['form'];
    $fieldName = $params['name'];

    if (!is_object($form) || !($form instanceof \util\form\Form)) {
        return '';
    }

    $rules = $form->getField($fieldName)->getRules();
    foreach ($rules as $k => &$rule) {
        switch ($rule[0]) {
            case 'php_custom' :
                //php_custom属于后端校验逻辑，不必带给前端
                unset($rules[$k]);
                break;
            case 'regexp':
                //正则前端不接受前后斜杠
                $rule[1] = trim($rule[1], '/');
                break;
            case 'compare':
            case 'required':
                //前端不需要this对象
                unset($rule[3]);
                break;
        }
    }

    return json_encode($rules);
}