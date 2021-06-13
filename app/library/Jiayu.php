<?php
/**
 * @author Chuwen <wenzhouchan@gmail.com>
 * @date   2021/6/12 23:40
 */

namespace app\library;

require_once __DIR__ . "/AES.php";
require_once __DIR__ . "/JiayuAPI.php";

class Jiayu
{
    private $AESInstance;

    // 登录成功后才会有的这些参数
    private $userId = '';
    private $userName = '';
    private $token = '';

    private $JiayuInstance;

    /**
     * Jiayu constructor.
     *
     * @param AES   $AESInstance
     * @param array $loginData
     */
    public function __construct($AESInstance, array $loginData = [])
    {
        $this->AESInstance = $AESInstance;

        if (!empty($loginData)) {
            $this->userId = $loginData['userId'] ?? '';
            $this->userName = $loginData['userName'] ?? '';
            $this->token = $loginData['token'] ?? '';

            $this->JiayuInstance = new JiayuAPI($this->token);
        } else {
            $this->JiayuInstance = new JiayuAPI('');
        }
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
        try {
            $res = $this->JiayuInstance->userLogin($userPhone, $password);

            if (empty($res['data'])) returnJson(404, '相关字段不存在，登录失败，请稍后再试');
            if (empty($res['data']['userId']) || empty($res['data']['userName']) || empty($res['data']['token'])) {
                returnJson(404, '相关字段不存在，登录失败，请稍后再试');
            }

            $token = $this->AESInstance::encrypt([
                'userId' => $res['data']['userId'],
                'userName' => $res['data']['userName'],
                'token' => $res['data']['token'],
            ]);
            setcookie('token', $token, time() + (86400 * 7));

            returnJson(0, '登录成功', [
                'token' => $token,
            ]);
        } catch (exception\RequestResultErrorException $e) {
            returnJson($e->getCode(), $e->getMessage());
        } catch (exception\RequestResultIsEmptyException $e) {
            returnJson($e->getCode(), $e->getMessage());
        }

        returnJson(405, '错误：无下文');
    }

    public function getRoomList()
    {

    }
}