<?php

namespace Delz\Ys7\Model;

/**
 * AccessToken模型类
 *
 * @package Delz\Ys7\Model
 */
class AccessToken
{
    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var float
     */
    private $expiredAt;

    /**
     * @param string $accessToken 获取的accessToken
     * @param float $expiredAt 具体过期时间，精确到毫秒
     */
    public function __construct($accessToken, $expiredAt)
    {
        $this->accessToken = $accessToken;
        $this->expiredAt = $expiredAt;
    }

    /**
     * 获取的accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * 具体过期时间，精确到毫秒
     *
     * @return int
     */
    public function getExpiredAt()
    {
        return $this->expiredAt;
    }

    /**
     * 是否有效，过期就是无效
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->expiredAt > $this->getUnixTimeStamp();
    }

    /**
     * 获取unix时间戳，毫秒
     */
    public function getUnixTimeStamp()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }

}