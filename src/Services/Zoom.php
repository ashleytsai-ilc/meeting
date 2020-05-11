<?php

namespace MeetingService\Services;

use MeetingService\Contracts\Meeting;

/**
 * 串接Zoom Api
 * 
 * @author Ashley Tsai <ashley.tsai@language-center.com.tw>
 * @date   2020-04-20
 */
class Zoom extends Meeting
{
    /**
     * 強制結束目前會議
     *
     * @param array $parameters 結束會議所需參數
     * 
     * @return mixed
     */
    public function end($parameters)
    {
        return $this->sendRequest($parameters, 'end');
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
        return $this->sendRequest($parameters, 'log');
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
        return $this->sendRequest($parameters, 'update/capacity');
    }

    /**
     * 參數名稱轉換
     *
     * @param array $parameters 會議參數
     * 
     * @return array $parameters
     */
    public function formatter($parameters)
    {
        foreach ($parameters as $key => $value) {
            switch ($key) {
            case 'meeting_name':
                $parameters['topic'] = $value;
                break;
            
            case 'duration':
                $parameters['minute'] = $value;
                break;

            case 'meeting_password':
                $parameters['password'] = $value;
                break;

            case 'max_user_num':
                $parameters['meeting_capacity'] = $value;
                break;

            case 'meeting_id':
                $parameters['id'] = $value['id'];
                $parameters['value'] = $value['value'];
                break;

            default:
                continue 2;
            }

            unset($parameters[$key]);
        }

        return $parameters;
    }

    /**
     * 送出請求前加入必要參數
     *
     * @param array $parameters 會議參數
     * 
     * @return array
     */
    public function parametersFormat($parameters)
    {
        $mustHave = [
            'key' => $this->config['access_key'],
            'secret' => $this->config['access_secret']
        ];

        return array_merge($mustHave, $parameters);
    }
}