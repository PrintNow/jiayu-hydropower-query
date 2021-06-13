<?php
/**
 * @author Chuwen <wenzhouchan@gmail.com>
 * @date   2021/6/13 00:01
 */

namespace app\library;

class JiayuAPIConst
{
    /**
     * API 域名
     */
    const HOST = 'kc.kaisaspace.com';

    /**
     * 请求 API 常规端口
     */
    const NORMAL_PORT = 80;

    /**
     * 请求查询 API 的端口
     */
    const QUERY_PORT = 88;

    /**
     * 登录 API
     */
    const LOGIN_API = 'http://kc.kaisaspace.com/wewa-sso/login/appLogin';

    /**
     * 检查 authToken 是否过期
     */
    const CHECK_IS_EXPIRE_API = 'http://kc.kaisaspace.com/wewa-tenant/community/survey/interaction/termination';

    /**
     * 获取房间<合同>列表
     */
    const GET_ROOM_LIST = 'http://kc.kaisaspace.com:88/wewa-tenant/apps/contractApp/list?contractState=4&pageCurrent=1&pageSize=10';

    /**
     * 获取水费 API
     */
    const GET_WATER_BILL_API = 'http://kc.kaisaspace.com:88/wewa-intelligence/apps/device/communityWaterElectricityRealTime?pageCurrent=%d&pageSize=%d&roomId=%d&type=1';

    /**
     * 获取电费 API
     */
    const GET_ELECTRIC_API = 'http://kc.kaisaspace.com:88/wewa-intelligence/apps/device/communityWaterElectricityRealTime?pageCurrent=%d&pageSize=%d&roomId=%d&type=2';
}