<?php

namespace Delz\Ys7;

use Delz\Cache\Contract\ICache;
use Delz\Common\Util\Http;
use Delz\Ys7\Model\AccessToken;

/**
 * 客户端类，封装了萤石开放平台Api的操作
 *
 * 具体的接口规则可参考官方文档：https://open.ys7.com/doc/zh/
 *
 * @package Delz\Ys7
 */
class Client
{
    /**
     * @var string
     */
    private $appKey;

    /**
     * @var string
     */
    private $appSecret;

    /**
     * @var ICache
     */
    private $cache;

    /**
     * accessToken缓存名称前缀
     */
    const ACCESS_TOKEN_CACHE_PREFIX = 'Ys7AccessToken_';

    /**
     * 接口入口网址
     */
    const API_ENDPOINT = 'https://open.ys7.com/api/lapp';

    /**
     * @param string $appKey
     * @param string $appSecret
     * @param ICache $cache
     *
     * @throws Ys7Exception
     */
    public function __construct($appKey, $appSecret, ICache $cache)
    {
        $appKey = trim($appKey);
        $appSecret = trim($appSecret);

        if (empty($appKey)) {
            throw new Ys7Exception('app id is empty');
        }

        if (empty($appSecret)) {
            throw new Ys7Exception('app secret is empty');
        }

        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->cache = $cache;
    }

    /**
     * 获取accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        //从缓存去读取
        $cacheKey = $this->getAccessTokenCacheKey($this->appKey);
        /** @var AccessToken $accessToken */
        $accessToken = $this->cache->get($cacheKey);
        if ($accessToken && $accessToken->isAvailable()) {
            return $accessToken->getAccessToken();
        }

        $params = [
            'appKey' => $this->appKey,
            'appSecret' => $this->appSecret
        ];

        $result = $this->post(self::API_ENDPOINT . '/token/get', $params, false);

        $accessToken = new AccessToken($result['accessToken'], $result['expireTime'] - 10000);

        //缓存永久存储，lifetime设为0
        $this->cache->set($cacheKey, $accessToken);

        return $accessToken->getAccessToken();
    }

    /**
     * 获取摄像头列表
     *
     * @param int $pageStart 分页起始页，从0开始
     * @param int $pageSize 分页大小，默认为10，最大为50
     * @return mixed
     * @throws Ys7Exception
     */
    public function getCameraList($pageStart = 0, $pageSize = 10)
    {
        if ($pageSize > 50) {
            throw new Ys7Exception('pageSize can\'t be greater than 50.');
        }

        $params = [
            'pageStart' => (int)$pageStart,
            'pageSize' => (int)$pageSize
        ];

        return $this->post(self::API_ENDPOINT . '/camera/list', $params);
    }

    /**
     * 获取EZOpen协议网址
     *
     * @param string $deviceSn 设备序列号
     * @param int $channelNo 通道号
     * @param int $videoLevel 视频质量 0-流畅，1-均衡，2-高清，3-超清
     * @param string $type 类型 支持live(实时视频)和rec(录像播放)
     * @param string $password 视频加密密码，即设备标签上的6位字母验证码，支持明文/密文两种格式,
     * @return string
     * @throws Ys7Exception
     */
    public function getEzUrl($deviceSn, $channelNo, $videoLevel, $type = 'live', $password = '')
    {
        $type = strtolower($type);
        if (!in_array($type, ['live', 'rec'])) {
            throw new Ys7Exception('Invalid type.');
        }
        if ($videoLevel == 0 || $videoLevel == 1) {
            $videoLevelStr = '';
        } else {
            $videoLevelStr = '.hd';
        }
        return 'ezopen://' . ($password ? $password . '@' : '') . 'open.ys7.com/' . $deviceSn . '/' . $channelNo . $videoLevelStr . '.' . $type;
    }

    /**
     * 获取accessToken保存在缓存中的键值
     *
     * @param string $appKey
     * @return string
     */
    private function getAccessTokenCacheKey($appKey)
    {
        return self::ACCESS_TOKEN_CACHE_PREFIX . $appKey;
    }

    /**
     * 萤石云接口post请求
     *
     * @param string $url 请求网址
     * @param array $params 参数
     * @param bool $auth 是否需要授权
     * @return mixed
     *
     * @throws Ys7Exception
     */
    private function post($url, $params = [], $auth = true)
    {
        if ($auth) {
            $params['accessToken'] = $this->getAccessToken();
        }
        $response = Http::post($url, ['form_params' => $params]);
        $result = json_decode($response->getBody(), true);
        if ($result['code'] !== '200') {
            throw new Ys7Exception((int)$result['msg'], $result['code']);
        }
        return isset($result['data']) ? $result['data'] : true;
    }

}