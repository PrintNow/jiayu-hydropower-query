<?php
/**
 * @author Chuwen <wenzhouchan@gmail.com>
 * @date   2021/6/13 22:54
 */

namespace app\library\exception;


use Exception;

class RequestResultIsEmptyException extends Exception
{
    private $result;

    public function __construct($message = '', $code = 0, $result = null)
    {
        parent::__construct($message, $code);
        $this->result = $result;
    }

    /**
     * 获取错误的结果
     *
     * @return mixed
     *
     * @author: Chuwen <wenzhouchan@gmail.com>
     * @date  : 2021/6/13 22:42
     */
    public function getErrorRequestResult()
    {
        return $this->result;
    }
}