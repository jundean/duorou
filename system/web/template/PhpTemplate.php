<?php
/**
 * @brief php模板类
 * @author wujun
 * @date 2014-05-31
 */

class PhpTemplate {

    public function fetch($fileName) {
        $filePath = $this->templateDir . '/' . $fileName;

        if (!file_exists($filePath)) {
            return '';
        }

        ob_start();
        include $filePath;
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;

    }

    public function helper($funcName, $params) {
        $filePath = $this->helperDir . '/' . $funcName . '_helper.php';

        if (!file_exists($filePath)) {
            throw new CException($filePath . 'is not exist');
        }

        include_once $filePath;
        if (function_exists($funcName)) {
            return $funcName($params);
        }

        return null;
    }

    public function load($fileName, $params) {
        $filePath = $this->templateDir . '/' . $fileName;

        if (!file_exists($filePath)) {
            throw new CException($filePath . 'is not exist');
        }

        if (is_array($params) && !empty($params)) {
            extract($params);
        }

        include $filePath;
    }

    public function display($fileName) {
        echo $this->fetch($fileName);
    }

    public function assign($key, $value = null) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->$k = $v;
            }
        } elseif (!empty($key)) {
            $this->$key = $value;
        }
    }
}