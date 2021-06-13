<?php
/**
 * @author Chuwen <wenzhouchan@gmail.com>
 * @date   2021/6/12 23:40
 */

namespace app\library;

use Exception;

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

            $expire = time() + (86400 * 7);
            $token = $this->AESInstance::encrypt([
                'userId' => $res['data']['userId'],
                'userName' => $res['data']['userName'],
                'token' => $res['data']['token'],
                'expire' => $expire,
            ]);
            setcookie('token', $token, $expire);

            returnJson(0, '登录成功', [
                'token' => $token,
            ]);
        } catch (Exception $e) {
            returnJson($e->getCode(), $e->getMessage());
        }

        returnJson(405, '错误：无下文');
    }

    /**
     * 获取房间列表
     *
     * @author: Chuwen <wenzhouchan@gmail.com>
     * @date  : 2021/6/14 00:56
     */
    public function getRoomList()
    {
        try {
            $res = $this->JiayuInstance->getRoomList();
            returnJson(0, 'ok', $res['data']);
        } catch (Exception $e) {
            returnJson($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取水费账单<以1天的维度>
     *
     * @author: Chuwen <wenzhouchan@gmail.com>
     * @date  : 2021/6/14 01:01
     */
    public function getWaterBill(int $roomId, int $page = 1, int $pageSize = 20)
    {
        if ($pageSize == 0 || $pageSize % 5 !== 0) returnJson(201, '请传入5的倍数');

        try {
            $res = $this->JiayuInstance->getWaterBill($roomId, $page, $pageSize);

            //TODO 计算一天的用水量

            returnJson(0, 'ok', $res['data']);
        } catch (Exception $e) {
            returnJson($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取电费账单<以1天的维度>
     *
     * @author: Chuwen <wenzhouchan@gmail.com>
     * @date  : 2021/6/14 01:01
     */
    public function getElectricBill(int $roomId, int $page = 1, int $pageSize = 20)
    {
        if ($pageSize == 0 || $pageSize % 5 !== 0) returnJson(201, '请传入5的倍数');

        try {
            $res = $this->JiayuInstance->getElectricBill($roomId, $page, $pageSize);

            //TODO 计算一天的用电量

            returnJson(0, 'ok', $res['data']);
        } catch (Exception $e) {
            returnJson($e->getCode(), $e->getMessage());
        }
    }
}