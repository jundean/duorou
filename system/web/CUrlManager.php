<?php
class CUrlManager extends CComponent {
    public $routeKey = 'r';

    public function parseUrl($request) {
        return $request->getParameter($this->routeKey, '');
    }
}
