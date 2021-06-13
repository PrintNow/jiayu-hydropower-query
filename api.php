<?php
/**
 * @author Chuwen <wenzhouchan@gmail.com>
 * @date   2021/6/13 23:06
 */

const APP_PATH = __DIR__ . "/app/";
const APP_CONFIG_PATH = APP_PATH . "/config/";
const DEFAULT_APP_KEY = "KBhxqLqV8Z4ti9xgYB3UnCqtdpKM";

include APP_PATH . "functions.php";

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