<?php
class Index extends CController
{
    public function testAction()
    {
//        $xhprof_on = true;
//        $model = new application\model\UserModel();
//        $userInfo = application\model\UserModel::getUserInfoById(1);
//        $userInfo = application\model\UserModel::getUserInfoByName('wjdean');
//        $userInfo = $model->getUser();
//        $userInfo = util\DBUtil::getRow('select * from user');
//        var_dump($userInfo);


//        $userInfo = application\util\UserNamespace::loginIn('dean', '123456');
////        $userInfo = application\model\UserModel::getIns()->addUser(array('username' => 'sdf', 'passwd' => 'sdsdf', 'email' => 'sdf'));
//var_dump($userInfo);
////
//        application\util\UserNamespace::loginOut();
//        var_dump(application\util\UserNamespace::getUserInfo());
//        $courseModel = new application\model\CourseModel();
//        $courseList = $courseModel->getShowCourse(1, 1, 1);
//        var_dump(application\util\UserNamespace::register('asdasd', '234234', 'sdfsfdsf'));
//
//        $coursewareModel = new application\model\CoursewareModel();

//        $this->assign('courseList', $courseList);
//        $this->assign('userInfo', $userInfo);
//        $this->assign('test', 'sdfdf');
        $this->display('/../../util/form/demo/form.php');


    }

}
