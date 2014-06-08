<?php
class CHttpRequest extends CComponent {
    public function getParameter($queryKey, $defaultValue = null) {
        return isset($_GET[$queryKey]) ? $_GET[$queryKey] : (isset($_POST[$queryKey]) ? $_POST[$queryKey] : $defaultValue);
    }

    public function getParameters($queryKeys) {
        $parameters = array();
        foreach ($queryKeys as $queryKey) {
            $parameters[$queryKey] = $this->getParemeter($queryKey);
        }
        return $parameters;
    }

    public function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
}
