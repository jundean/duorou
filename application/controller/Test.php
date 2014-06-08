<?php
class Test extends CController
{
    public function index()
    {
        $this->assign('site_info', array('version' => 2.0));
        $model = new application\model\TestModel();
        $this->assign('user', $model->getUser());
        $this->display('test/test.html');
    }
}
