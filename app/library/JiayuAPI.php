<?php
/**
 * @author Chuwen <wenzhouchan@gmail.com>
 * @date   2021/6/12 23:48
 */

namespace app\library;

use app\library\exception\RequestResultErrorException;
use app\library\exception\RequestResultIsEmptyException;

/**
 * Class JiayuAPI
 *
 * @package app\library
 */
class JiayuAPI extends JiayuAPIConst
{
    /**
     * 请求 API 需要携带上的东西《用以验证》
     *
     * @var string
     */
    private $authToken = '';

    public function __construct(string $authToken)
    {
        $this->authToken = $authToken;
    }

    /**
     * 用户登录，返回如 authToken、UID 等相关信息
     *
     * @param string $userPhone 用户手机号
     * @param string $password  对应的密码
     *
     * @throws RequestResultIsEmptyException
     * @throws RequestResultErrorException
     * @author: Chuwen <wenzhouchan@gmail.com>
     * @date  : 2021/6/12 23:59
     */
    public function userLogin(string $userPhone, string $password): array
    {
        return $this->requestAPI(self::LOGIN_API, [
                'userPhone' => $userPhone,
                'password' => $password,
            ]
        );
    }

    /**
     * 获取用户信息
     *
     * @author: Chuwen <wenzhouchan@gmail.com>
     * @date  : 2021/6/12 23:59
     */
    public function getUserInfo()
    {
    }

    /**
     * 获取房间<合同>列表
     *
     * @return array
     * @throws RequestResultErrorException
     * @throws RequestResultIsEmptyException
     * @author: Chuwen <wenzhouchan@gmail.com>
     * @date  : 2021/6/12 23:59
     */
    public function getRoomList(): array
    {
        return $this->requestAPI(self::GET_ROOM_LIST);
    }

    /**
     * 获取水费账单
     *
     * @param int $roomId   房间号
     * @param int $page     第几页
     * @param int $pageSize 一页获取多少条数据
     *
     * @return array
     * @throws RequestResultErrorException
     * @throws RequestResultIsEmptyException
     * @author: Chuwen <wenzhouchan@gmail.com>
     * @date  : 2021/6/12 23:59
     */
    public function getWaterBill(int $roomId, int $page = 1, int $pageSize = 20): array
    {
        return $this->requestAPI(sprintf(self::GET_WATER_BILL_API, $page, $pageSize, $roomId));
    }

    /**
     * 获取电费账单
     *
     * @param int $roomId   房间号
     * @param int $page     第几页
     * @param int $pageSize 一页获取多少条数据
     *
     * @return array
     * @throws RequestResultErrorException
     * @throws RequestResultIsEmptyException
     * @author: Chuwen <wenzhouchan@gmail.com>
     * @date  : 2021/6/12 23:59
     */
    public function getElectricBill(int $roomId, int $page = 1, int $pageSize = 20): array
    {
        return $this->requestAPI(sprintf(self::GET_ELECTRIC_API, $page, $pageSize, $roomId));
    }

    /**
     * 请求 API
     *
     * @param string     $url       API 地址
     * @param array|null $postParam POST 参数，非必选
     *
     * @return array
     * @throws RequestResultIsEmptyException
     * @throws RequestResultErrorException
     */
    private function requestAPI(string $url, array $postParam = null): array
    {
        $isPost = !empty(current($postParam));

        $curl = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->authToken,
            ],
        ];

        // 如果是 POST 请求
        if ($isPost) {
            $options[CURLOPT_CUSTOMREQUEST] = 'POST';
            $options[CURLOPT_POSTFIELDS] = json_encode($postParam);
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);

        if (empty($response)) {
            throw new RequestResultIsEmptyException('请求佳寓 API 失败，请重试！', 501, $response);
        }

        $response = json_decode($response, true);
        if (!is_array($response)) {
            throw new RequestResultIsEmptyException('请求佳寓 API 成功，但是返回的内容解析失败', 502, $response);
        }

        if ($response['code'] !== 0) {
            throw new RequestResultErrorException($response['message'] ?? '请求失败，无具体原因', $response['code'] ?? 5000);
        }

        return (array)$response;
    }
}