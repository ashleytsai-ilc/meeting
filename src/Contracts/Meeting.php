<?php

namespace MeetingService\Contracts;

use MeetingService\Contracts\MeetingInterface;
use GuzzleHttp\Client;
use Exception;
use GuzzleHttp\Exception\ClientException;

/**
 * 定義子類需要的方法
 * 
 * @author Ashley Tsai <ashley.tsai@language-center.com.tw>
 * @date   2020-04-17
 */
abstract class Meeting implements MeetingInterface
{
    /**
     * 會議設定參數
     *
     * @var array
     */
    protected $config;

    /**
     * 打第三方服務API時是否要開啟SSL認證
     *
     * @var bool
     */
    protected $guzzleVerify;

    /**
     * 時區
     *
     * @var string
     */
    protected $timezone;

    /**
     * 建立會議特定的uri
     *
     * @var string
     */
    protected $createUri = 'create';

    /**
     * 更新會議特定的uri
     *
     * @var string
     */
    protected $updateUri = 'update';

    /**
     * 取得會議特定的uri
     *
     * @var string
     */
    protected $getUri = 'get';

    /**
     * 刪除會議特定的uri
     *
     * @var string
     */
    protected $deleteUri = 'delete';

    /**
     * 初始化設定
     *
     * @param array  $config 會議設定
     * @param string $driver 要驅動的服務
     * 
     * @return this
     */
    public function setConfig($config, $driver)
    {
        $this->config = $config['connections'][$driver];
        $this->guzzleVerify = $config['guzzle_verify'];
        $this->timezone = $config['timezone'];

        return $this;
    }

    /**
     * 建立會議
     *
     * @param array $parameters 建立所需參數
     * 
     * @return mixed
     */
    public function create($parameters)
    {
        return static::sendRequest($parameters, $this->createUri);
    }

    /**
     * 取得會議資訊
     *
     * @param array $parameters 取得會議所需參數
     * 
     * @return mixed
     */
    public function get($parameters)
    {
        return static::sendRequest($parameters, $this->getUri);
    }

    /**
     * 更新會議資訊
     *
     * @param array $parameters 更新會議資訊所需參數
     * 
     * @return mixed
     */
    public function update($parameters)
    {
        return static::sendRequest($parameters, $this->updateUri);
    }

    /**
     * 刪除會議
     *
     * @param array $parameters 刪除會議所需資訊
     * 
     * @return mixed
     */
    public function delete($parameters)
    {
        return static::sendRequest($parameters, $this->deleteUri);
    }

    /**
     * 強制結束目前會議
     *
     * @param array $parameters 結束會議所需參數
     * 
     * @return mixed
     */
    public function end($parameters)
    {
        throw new Exception('This service does not support end meeting.');
    }
    
    /**
     * 取得會議詳細資訊(結束後)
     *
     * @param array $parameters 取得會議所需參數
     * 
     * @return mixed
     */
    public function log($parameters)
    {
        throw new Exception('This service does not support get meeting log.');
    }

    /**
     * 變更會議人數(會議中)
     *
     * @param array $parameters 變更會議所需參數
     * 
     * @return mixed
     */
    public function updateCapacity($parameters)
    {
        throw new Exception(
            'This service does not support update meeting capacity.'
        );
    }

    /**
     * 如果呼叫此類別不存在或private的屬性或方法時，php會自動呼叫此函式
     *
     * @param string $method     要呼叫的函示
     * @param array  $parameters 函式要傳的參數
     * 
     * @return subClass
     */
    public static function __callStatic($method, $parameters)
    {
        /**
         * 返回實際呼叫該方法的類別並執行該方法
         */
        return (new static)->$method(...$parameters);
    }

    /**
     * 可由子類複寫，用於在送出前轉換參數成服務指定的格式
     *
     * @param array $parameters 要轉換的參數
     * 
     * @return mixed
     */
    public function parametersFormat($parameters)
    {
        return $parameters;
    }

    /**
     * 可由子類複寫，用於參數特殊格式轉換
     *
     * @param array $parameters 要轉換的參數
     * 
     * @return mixed
     */
    public function formatter($parameters)
    {
        return $parameters;
    }

    /**
     * 送出請求
     *
     * @param mixed  $parameters  送出參數
     * @param string $url         請求路徑
     * @param string $method      請求動詞(POST, GET, PUT, DELETE, PATCH)
     * @param string $sendWay     送出請求方式(form_params, query, body, json)
     * @param array  $headers     請求headers
     * @param bool   $isJsonParse 結果是否要json decode
     * 
     * @return object or string
     */
    public function sendRequest(
        $parameters, $url, $method = 'POST', $sendWay = 'form_params', 
        $headers = [], $isJsonParse = true
    ) {
        /**
         * 如果url不是完整路徑，就須串出完整路徑
         */
        if (strpos($url, 'https://') === false) {
            $url = $this->config['request_url'] . $url;
        }

        if (is_array($parameters)) {
            $parameters = static::parametersFormat(static::formatter($parameters));
        }

        $params = ['headers' => $headers, $sendWay => $parameters];

        $client = new Client(['verify' => $this->guzzleVerify]);

        try {
            $response = $client->request($method, $url, $params)
                ->getBody()->getContents();
        } catch (ClientException $e) {
            throw new Exception($e->getMessage());
        }
        
        return $isJsonParse ? json_decode($response) : $response;
    }
}