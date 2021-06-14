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
        $data = $this->getCacheData('room_list');
        if (!empty($data)) {
            returnJson(0, 'ok.is cache', $data);
        }

        try {
            $res = $this->JiayuInstance->getRoomList();

            $this->setCacheData('room_list', $res['data']);

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

        $data = $this->getCacheData($roomId . '_water');
        if (!empty($data)) {
            returnJson(0, 'ok.is cache', $data);
        }

        try {
            $res = $this->JiayuInstance->getWaterBill($roomId, $page, $pageSize);

            //TODO 计算一天的用水量
            $this->setCacheData($roomId . '_water', $res['data']);

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

        $data = $this->getCacheData($roomId . '_electric');
        if (!empty($data)) {
            returnJson(0, 'ok.is cache', $data);
        }

        try {
            $res = $this->JiayuInstance->getElectricBill($roomId, $page, $pageSize);

            //TODO 计算一天的用电量
            $this->setCacheData($roomId . '_electric', $res['data']);

            returnJson(0, 'ok', $res['data']);
        } catch (Exception $e) {
            returnJson($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 设置缓存数据
     *
     * @param string $type 类型
     * @param array  $data 数据
     *
     * @return bool
     *
     * @author: Chuwen <wenzhouchan@gmail.com>
     * @date  : 2021/6/14 20:18
     */
    private function setCacheData(string $type, array $data): bool
    {
        $cachePath = APP_PATH . "/cache/{$this->userId}/";

        if (!is_dir($cachePath)) mkdir($cachePath, 0755, true);

        $head = <<<EOT
<?php
return
EOT;

        return !!file_put_contents($cachePath . $type . ".php", $head . " " . var_export($data, true) . ";");
    }

    /**
     * 获取缓存的数据
     *
     * @param string $type 类型
     *
     * @return array
     *
     * @author: Chuwen <wenzhouchan@gmail.com>
     * @date  : 2021/6/14 20:21
     */
    private function getCacheData(string $type): array
    {
        $cachePath = APP_PATH . "/cache/{$this->userId}/{$type}.php";
        if (!file_exists($cachePath)) return [];

        // 如果文件最后的创建时间+1小时 < 当前时间
        // 就删除文件，并返回空数组
        if (filectime($cachePath) + 3600 < time()) {
            unlink($cachePath);
            return [];
        }

        $data = include $cachePath;
        if (!is_array($data)) return [];

        return $data;
    }
}