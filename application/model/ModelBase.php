<?php
/**
 * @brief 数据库基类，单例模式
 * @author wujun
 * @date 2014-06-01
 */
namespace application\model;

class ModelBase {
    protected $_masterHandle = null;
    protected $_slaveHandle = null;

    /**
     * @param $master 主库配置key
     * @param $slave 从库配置key
     */
    protected function __construct($master, $slave) {
        if (empty($this->tableName)) {
            trigger_error('table name can not be empty!');
        }

        $this->_masterHandle = \HRR::app()->getDb($master);
        $this->_slaveHandle = \HRR::app()->getDb($slave);
    }

    protected function __clone() {}

    /**
     * @brief 返回主库连接handle
     */
    public function getMasterHandle() {
        if (empty($this->_masterHandle)) {
            trigger_error('master handle is empty!');
        }

        return $this->_masterHandle;
    }

    /**
     * @brief 返回从库连接handle
     */
    public function getSlaveHandle() {
        if (empty($this->_slaveHandle)) {
            trigger_error('slave handle is empty!');
        }

        return $this->_slaveHandle;
    }

    /**
     * @brief 插入数据
     * @param $data array('key1' => 'value1', 'key2' => 'value2')
     * @return bool
     */
    public function insert($data) {
        return $this->_masterHandle->createCommand()
            ->insert($this->tableName, $data);
    }

    /**
     * @brief 删除数据记录
     * @param $conditions array('id=:id') or array('and', 'id=:id', 'name=:id') or array('and', 'type=1', array('or', 'id=1', 'id=2'))
     * @param $params array(':id' => 1, ':name' => 'name')
     * @return bool
     */
    public function delete($conditions, $params) {
        if (!is_array($conditions) || empty($conditions)) {
            return false;
        }

        return $this->_masterHandle->createCommand()
            ->delete($this->tableName, $conditions, $params);
    }

    /**
     * @brief 获取一条记录
     * @param $conditions array('id=:id') or array('and', 'id=:id', 'name=:id') or array('and', 'type=1', array('or', 'id=1', 'id=2'))
     * @param $params array(':id' => 1, ':name' => 'name')
     * @param string $fields 'id, name'
     * @return array
     */
    public function getRow($conditions, $params, $fields = '*') {
        $ret = $this->getAll($conditions, $params, $fields);

        if (is_array($ret) && !empty($ret)) {
            return $ret[0];
        }

        return array();
    }

    /**
     * @brief 获取所有记录
     * @param $conditions array('id=:id') or array('and', 'id=:id', 'name=:id') or array('and', 'type=1', array('or', 'id=1', 'id=2'))
     * @param $params array(':id' => 1, ':name' => 'name')
     * @param string $fields 'id, name'
     * @param string $orderBy 'id asc, name desc' or array('id asc', 'name desc')
     * @param int $limit
     * @param int $offset
     * @return bool
     */
    public function getAll($conditions, $params, $fields = '*', $orderBy = '', $limit = null, $offset = null) {
        if (empty($conditions)) {
            return false;
        }

        $ret = $this->_slaveHandle->createCommand()
            ->select($fields)
            ->from($this->tableName)
            ->where($conditions, $params);

        if (!empty($orderBy)) {
            $ret = $ret->order($orderBy);
        }

        if ($limit !== null) {
            $ret = $ret->limit($limit, $offset);
        }

        return $ret->queryAll();
    }

    /**
     * @brief 获取单个字段值
     * @param $conditions array('id=:id') or array('and', 'id=:id', 'name=:id') or array('and', 'type=1', array('or', 'id=1', 'id=2'))
     * @param $params array(':id' => 1, ':name' => 'name')
     * @param $fields 'name'
     * @return string
     */
    public function getOne($conditions, $params, $fields) {
        $ret = $this->getRow($conditions, $params, $fields);

        if (is_array($ret) && !empty($ret)) {
            return $ret[0];
        }

        return null;
    }

    /**
     * @brief 更新记录
     * @param $colums array('name' => 'new')
     * @param $conditions array('id=:id') or array('and', 'id=:id', 'name=:id') or array('and', 'type=1', array('or', 'id=1', 'id=2'))
     * @param array $params array(':id' => 1, ':name' => 'name')
     * @return bool
     */
    public function update($colums, $conditions, $params = array()) {
        //不允许全局更新
        if (empty($conditions)) {
            return false;
        }

        return $this->_masterHandle->createCommand()
            ->update($this->tableName, $colums, $conditions, $params);
    }

}