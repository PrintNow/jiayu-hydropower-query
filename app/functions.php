<?php
/**
 * @author Chuwen <wenzhouchan@gmail.com>
 * @date   2021/6/13 23:13
 */


/**
 * 终止程序运行，并输出 JSON 数据
 *
 * @param int    $code 状态吗
 * @param string $msg  消息
 * @param array  $data 额外数据
 *
 * @author: Chuwen <wenzhouchan@gmail.com>
 * @date  : 2021/6/13 23:48
 */
function returnJson(int $code = 0, string $msg = 'ok', array $data = [])
{
    header("Content-type: application/json; charset=UTF-8");
    die(json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE));
}


/**
 * 生成GUID
 *
 * @return string
 *
 * @author: Chuwen <wenzhouchan@gmail.com>
 * @date  : 2021/6/13 23:17
 */
function create_guid(): string
{
    if (function_exists('com_create_guid') === true) {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}