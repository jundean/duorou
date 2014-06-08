<?php
require_once dirname(__FILE__) . '/../../config/config.inc.php';
require SYSTEM . '/HRR.php';
HRR::createWebApplication(array(
    'basePath' => dirname(dirname(__FILE__)),
    'components' => array(
        'db' => array(
            'connectionString' => 'mysql:host=localhost;port=3306;dbname=duorou',
            'charset'          => 'utf8',
            'username'         => 'root',
            'password'         => '900912'
        ),
        'db2' => array(
            'class'            => 'system.db.CDbConnection',
            'connectionString' => 'mysql:host=localhost;port=3306;dbname=duorou',
            'charset'          => 'utf8',
            'username'         => 'root',
            'password'         => '900912'
        )
    ),
    'aliases' => array(
        'util' => APP . '/../util',
    ),
))->run();
