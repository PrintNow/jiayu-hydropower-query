<?php
/**
 * @author Chuwen <wenzhouchan@gmail.com>
 * @date   2021/6/12 23:40
 */

namespace app\library;

include dirname(__DIR__) . "/functions.php";

class Jiayu
{
    private $AESInstance;

    public function __construct(AES $AESInstance)
    {
        $this->AESInstance = $AESInstance;
    }

    /**
     * 用户登录
     *
     * @param string $userPhone 用户手机号
     * @param string $password
     *
     * @author: Chuwen <wenzhouchan@gmail.com>
     * @date  : 2021/6/14 00:06
     */
    public function login(string $userPhone, string $password)
    {
        $instance = new JiayuAPI();

        try {
            $res = $instance->userLogin($userPhone, $password);

            if (empty($res['userId']) || empty($res['userName']) || empty($res['token'])) {
                returnJson(404, '相关字段不存在，登录失败，请稍后再试');
            }

            setcookie('token', $this->AESInstance::encrypt([
                'userId' => $res['userId'],
                'userName' => $res['userName'],
                'token' => $res['token'],
            ]), 86400 * 7);

            returnJson(0, '登录成功');
        } catch (exception\RequestResultErrorException $e) {
            returnJson($e->getCode(), $e->getMessage());
        } catch (exception\RequestResultIsEmptyException $e) {
            returnJson($e->getCode(), $e->getMessage());
        }

        returnJson(405, '错误：无下文');
    }
}