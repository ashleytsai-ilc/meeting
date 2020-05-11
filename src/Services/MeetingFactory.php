<?php

namespace MeetingService\Services;

use Exception;
use MeetingService\Services\GoToMeeting;
use MeetingService\Services\Webex;
use MeetingService\Services\Zoom;

/**
 * 產出會議服務
 * 
 * @author Ashley Tsai <ashley.tsai@language-center.com.tw>
 * @date   2020-05-04
 */
class MeetingFactory
{
    /**
     * 會議實例
     *
     * @var instance
     */
    protected $meeting;

    /**
     * 允許呼叫的function
     *
     * @var array
     */
    protected $allowMethods = [
        'create', 'get', 'update', 'delete', 'end', 'log', 'updateCapacity'
    ];

    /**
     * 啟動指定會議服務
     *
     * @param array $config 會議服務設定
     */
    public function __construct($config)
    {
        $this->make($config);
    }

    /**
     * 若呼叫此類不存在的function時，php會自動執行此function
     *
     * @param string $method     欲呼叫的函式名稱
     * @param array  $parameters 函式所需參數
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        /**
         * 如果呼叫的函式名稱符合所有定義的函式名稱，就執行該function並返回
         */
        if (in_array($method, $this->allowMethods)) {
            return $this->meeting->$method(...$parameters);
        }
    }

    /**
     * 綁定指定服務
     * 
     * @param array $config 會議參數設定
     *
     * @return new instance
     *
     * @throws Exception
     */
    public function make($config)
    {
        $driver = data_get($config, 'default');

        if (!$driver) {
            throw new Exception('A default driver must be specified.');
        }

        if (!data_get($config, "connections.$driver")) {
            throw new Exception('Error meeting config: need connections driver');
        }
        
        switch ($driver) {
        case 'zoom':
            $service = new Zoom();
            break;
        
        case 'webex':
            $service = new Webex();
            break;

        case 'gotomeeting':
            $service = new GoToMeeting();
            break;

        default:
            throw new Exception("Unsupport driver [{$driver}]");
        }

        $this->meeting = $service->setConfig($config, $driver);        
    }
}
