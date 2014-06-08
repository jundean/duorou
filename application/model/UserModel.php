<?php
namespace application\model;

class UserModel extends \application\model\ModelBase {
    public $tableName = 'user';
    private static $_instance = null;

    /**
     * 获取操作实例，单例模式
     * @return UserModel|null
     */
    public static function getIns() {
        if (self::$_instance === null) {
            return self::$_instance = new self('db', 'db');
        }

        return self::$_instance;
    }

    /**
     * 根据用户id获取用户信息
     * @param $userId
     * @return array
     */
    public function getUserInfoById($userId) {
        $userId = intval($userId);

        if ($userId <= 0) {
            return array();
        }

        return $this->getRow('id=:id', array(':id' => $userId));
    }

    /**
     * 根据用户名获取用户信息
     * @param $userName
     * @return array
     */
    public function getUserInfoByName($userName) {
        $userName = trim($userName);

        if (empty($userName)) {
            return array();
        }

        return $this->getRow('username=:username', array(':username' => $userName));
    }

    /**
     * 根据邮箱获取用户信息
     * @param $mail
     * @return array
     */
    public function getUserInfoByMail($mail) {
        $mail = trim($mail);

        if (empty($mail)) {
            return array();
        }

        return $this->getRow('email=:email', array(':email' => $mail));
    }

    /**
     * 增加一个用户
     * @param $userInfo
     * @return bool
     */
    public function addUser($userInfo) {
        if (!is_array($userInfo) || empty($userInfo)) {
            return false;
        }

        $userInfo['create_at'] = time();
        $userInfo['email_auth_status'] = 0;

        return $this->insert($userInfo);
    }

}