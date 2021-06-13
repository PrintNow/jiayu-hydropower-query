<?php
/**
 * @author Chuwen <wenzhouchan@gmail.com>
 * @date   2021/6/13 23:13
 */

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