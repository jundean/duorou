<?php
namespace application\util;

/**
 * brief 用户注册登录等处理
 * @package application\util
 * @author wujun
 * @date 2014-05-25
 */
class UserNamespace {
    public static $USER_SESSION_KEY = 'member';

    /**
     * @brief 获取当前session里已登陆用户信息
     * @return userInfo or array
     */
    public function getUserInfo() {
        return \util\session\SessionUtil::getInstance()->getValue(self::$USER_SESSION_KEY, array());
    }

    /**
     * @brief 用户登陆操作，验证密码，写session
     * @param $userName
     * @param $passwd
     * @return userInfo or false
     */
    public static function loginIn($userName, $passwd) {
        $userInfo = self::validUser($userName, $passwd);

        //验证失败
        if ($userInfo === false) {
            return false;
        }

        return \util\session\SessionUtil::getInstance()->setValue(self::$USER_SESSION_KEY, array(
            'user_id' => $userInfo['id'],
            'username' => $userInfo['username'],
            'email' => $userInfo['email']
        ));
    }


    /**
     * @brief 验证用户密码
     * @param $userName (输入用户名，可以是用户名或邮箱)
     * @param $passwd
     * @return false or userInfo
     */
    public static function validUser($userName, $passwd) {
        if (empty($userName) || empty($passwd)) {
            return false;
        }

        //先按用户名查找
        $userInfo = \application\model\UserModel::getIns()->getUserInfoByName($userName);

        //再查邮箱
        if (empty($userInfo)) {
            $userInfo = \application\model\UserModel::getIns()->getUserInfoByMail(($userName));
        }

        if (empty($userInfo)) {
            return false;
        }

        //验证密码是否正确
        return ($userInfo['passwd'] == self::_encodePassword($passwd)) ?  $userInfo : false;
    }

    public static function loginOut() {
        \util\session\SessionUtil::getInstance()->delete(self::$USER_SESSION_KEY);
    }

    /**
     * @brief 用户注册操作
     * @param $userName 用户名
     * @param $passwd 密码
     * @param $email 用户邮箱
     * @return bool
     */
    public static function register($userName, $passwd, $email) {
        if (empty($userName) || empty($passwd) || empty($email)) {
            return false;
        }

        $userData = array(
            'username' => $userName,
            'passwd' => self::_encodePassword($passwd),
            'email' => $email
        );

        return \application\model\UserModel::getIns()->addUser($userData);
    }

    /**
     * @brief 密码转换
     * @param $passwd
     * @return md5之后的密码串
     */
    private static function _encodePassword($passwd) {
        $passwd .= '*&&^%';

        return md5($passwd);
    }
}