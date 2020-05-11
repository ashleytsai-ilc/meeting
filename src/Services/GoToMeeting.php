<?php

namespace MeetingService\Services;

use MeetingService\Contracts\Meeting;
use Exception;

/**
 * 串接go to meeting API
 * 
 * @author Ashley Tsai <ashley.tsai@language-center.com.tw>
 * @date   2020-04-21
 */
class GoToMeeting extends Meeting
{
    /**
     * 會議動作
     *
     * @var string
     */
    protected $action = '';

    /**
     * 取得密鑰
     *
     * @return array
     */
    public function getAccessToken()
    {
        $authUrl = $this->config['auth_url'];
        $consumerKey = $this->config['consumer_key'];
        $consumerSecret = $this->config['consumer_secret'];

        if (!isset($authUrl) || $authUrl == '') {
            throw new Exception('Need [auth_url]');
        }

        if (!isset($consumerKey) || $consumerKey == '') {
            throw new Exception('Need config: [consumer_key]');
        }

        if (!isset($consumerSecret) || $consumerSecret == '') {
            throw new Exception('Need config: [consumer_secret]');
        }

        $parameters = http_build_query(
            ['grant_type' => 'password',
            'username' => $this->config['account_name'],
            'password' => $this->config['account_password']]
        );

        $authorization = base64_encode($consumerKey . ':' . $consumerSecret);
        $headers = ['Authorization' => 'Basic ' . $authorization];

        return parent::sendRequest($parameters, $authUrl, 'POST', 'body', $headers);
    }

    /**
     * 格式化特定參數
     *
     * @param array $parameters 會議參數
     * 
     * @return array $parameters
     */
    public function formatter($parameters)
    {
        if (isset($parameters['start_time'])) {
            $startTime = $parameters['start_time'];
        }

        foreach ($parameters as $key => &$value) {
            switch ($key) {
            case 'meeting_name':
                $parameters['subject'] = $value;
                break;

            case 'start_time':
                $parameters['starttime'] = date(DATE_ISO8601, $value);
                break;

            case 'duration':
                if (!isset($startTime)) {
                    throw new Exception('need [start_time]');
                }

                $endTime = strtotime("+ $value minutes", $startTime);
                $parameters['endtime'] = date(DATE_ISO8601, $endTime);
                break;

            case 'meeting_password':
                $parameters['passwordrequired'] = true;
                break;

            default:
                continue 2;
            }

            unset($parameters[$key]);
        }

        return $parameters;
    }

    /**
     * 補上必要參數
     *
     * @param array $parameters 會議參數
     * 
     * @return array $parameters
     */
    public function parametersFormat($parameters)
    {
        if ($this->action != 'create' && $this->action != 'update') {
            return $parameters;
        }

        if (!isset($parameters['passwordrequired'])) {
            $parameters['passwordrequired'] = false;
        }

        if (!isset($parameters['conferencecallinfo'])) {
            $parameters['conferencecallinfo'] = 'hybrid';
        }

        if (!isset($parameters['meetingtype'])) {
            $parameters['meetingtype'] = 'scheduled';
        }

        return $parameters;
    }

    /**
     * 根據要執行的不同動作取得不同發送方式
     *
     * @param string $action 要執行的動作
     * 
     * @return array $result
     */
    public function getSendWay($action)
    {
        switch ($action) {
        case 'get':
            $result['method'] = 'GET';
            $result['sendWay'] = 'query';
            break;
        
        case 'update':
            $result['method'] = 'PUT';
            break;

        case 'delete':
            $result['method'] = 'DELETE';
            break;
        }

        return $result;
    }

    /**
     * 發送請求
     *
     * @param mixed  $parameters  送出參數
     * @param string $url         請求路徑
     * @param string $method      請求動詞(POST, GET, PUT, DELETE, PATCH)
     * @param string $sendWay     送出請求方式(form_params, query, body, json)
     * @param array  $headers     請求headers
     * @param bool   $isJsonParse 結果是否要json decode
     * 
     * @return mixed
     */
    public function sendRequest(
        $parameters, $url = '', $method = 'POST', 
        $sendWay = 'json', $headers = [], $isJsonParse = true
    ) {
        $accessToken = $this->getAccessToken()->access_token;
        
        $headers = ['Authorization' => $accessToken];

        $this->action = $url;

        /**
         * 除新增會議外，都必須帶有meeting_id參數
         */
        if ($url == 'create') {
            $url = '';
        } elseif (!isset($parameters['meeting_id'])) {
            throw new Exception('Parameter Error: need [meeting_id]');
        } else {
            $sendParams = $this->getSendWay($url);
            $method = data_get($sendParams, 'method', $method);
            $sendWay = data_get($sendParams, 'sendWay', $sendWay);

            $url = "/{$parameters['meeting_id']}";
        }

        $response = parent::sendRequest(
            $parameters, $url, $method, $sendWay, $headers
        );

        return $response[0];
    }
}