<?php
/**
 * @author Chuwen <wenzhouchan@gmail.com>
 * @date   2021/6/13 23:06
 */

use app\library\AES;
use app\library\Jiayu;

const APP_PATH = __DIR__ . "/app/";
const APP_CONFIG_PATH = APP_PATH . "/config/";
const APP_LIB_PATH = APP_PATH . "/library/";
const DEFAULT_APP_KEY = "KBhxqLqV8Z4ti9xgYB3UnCqtdpKM";

require_once APP_PATH . "functions.php";
require_once APP_LIB_PATH . "AES.php";
require_once APP_LIB_PATH . "Jiayu.php";

$app_config = include APP_CONFIG_PATH . "/app.php";

// 如果存在默认 KEY
if ($app_config['key'] === DEFAULT_APP_KEY) {
    $_guid = create_guid();
    $file_app_config = file_get_contents(APP_CONFIG_PATH . "/app.php");
    $file_app_config = str_replace(DEFAULT_APP_KEY, $_guid, $file_app_config);

    if ($file_app_config !== false) {
        file_put_contents(APP_CONFIG_PATH . "/app.php", $file_app_config);
    }

    define("APP_KEY", $_guid);
} else {
    define("APP_KEY", $app_config['key']);
}

$AESInstance = new AES(APP_KEY);

//GET 参数
$type = $_GET['type'] ?? '';

// 账号登录
if ($type === 'login') {
    $JiayuInstance = new Jiayu($AESInstance);

    $userPhone = $_POST['userPhone'] ?? '';
    $password = $_POST['password'] ?? '';


    if (empty($userPhone) || empty($password)) {
        returnJson(401, '账号或密码不能为空哦！');
    }

    $JiayuInstance->login($userPhone, $password);
}


$loginData = $AESInstance::decrypt($_COOKIE['token'] ?? '');

if (empty($loginData)) {
    returnJson(403, '未登录，请先登录');
}

$loginData = json_decode($loginData, true);
if ($loginData['expire'] < time()) {
    returnJson(401, 'Token 过期，请重新登录');
}

$JiayuInstance = new Jiayu($AESInstance, $loginData);
$roomId = $_GET['roomId'] ?? '';
$page = $_GET['page'] ?? 1;
$pageSize = $_GET['pageSize'] ?? 35;

switch ($type) {
    case 'get-room-list':
        $JiayuInstance->getRoomList();
        break;
    case 'get-water-bill':
        if (empty($roomId)) returnJson(201, '房间号不能为空！');
        $JiayuInstance->getWaterBill(intval($roomId), intval($page), intval($pageSize));
        break;
    case 'get-electric-bill':
        if (empty($roomId)) returnJson(201, '房间号不能为空！');
        $JiayuInstance->getElectricBill(intval($roomId), intval($page), intval($pageSize));
        break;
    default:
        returnJson(404, '此接口不存在，请检查');
}