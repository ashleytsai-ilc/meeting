<?php

namespace MeetingService\Services;

use MeetingService\Contracts\Meeting;
use SimpleXMLElement;
use Exception;

/**
 * 串接webex api
 * 
 * @author Ashley Tsai <ashley.tsai@language-center.com.tw>
 * @date   2020-04-20
 */
class Webex extends Meeting
{
    /**
     * 對應不同會議動作(CreateMeeting, SetMeeting, GetMeeting, DelMeeting)
     *
     * @var string
     */
    protected $action = '';

    /**
     * 初始化參數
     *
     * @param array $config 會議設定
     */
    public function __construct()
    {
        $this->createUri = 'CreateMeeting';
        $this->updateUri = 'SetMeeting';
        $this->getUri = 'GetMeeting';
        $this->deleteUri = 'DelMeeting';
    }

    /**
     * 串出可轉換為服務要求的xml的array
     *
     * @param array $parameters 會議參數
     * 
     * @return mixed
     */
    public function getParameters($parameters)
    {
        foreach ($parameters as $key => $parameter) {
            switch ($key) {
            case 'meetingKey':
                if ($this->action == 'SetMeeting') {
                    $key = 'meetingkey';
                }
                
                $results[$key] = $parameter;
                break;

            case 'meetingPassword':
                $results['accessControl'] = [
                    'meetingPassword' => $parameter
                ];
                break;
            
            case 'confName':
            case 'agenda':
                $results['metaData'][$key] = $parameter;
                break;

            case 'maxUserNumber':
                $results['participants'][$key] = $parameter;
                break;

            case 'attendees':
                $results['participants']['attendees']['attendee'] = $parameter;
                break;

            case 'chat':
            case 'poll':
            case 'audioVideo':
            case 'supportE2E':
            case 'autoRecord':
            case 'autoDeleteAfterMeetingEnd':
                $results['enableOptions'][$key] = $parameter ? 'true' : 'false';
                break;

            case 'startDate':
            case 'openTime':
            case 'duration':
                $results['schedule'][$key] = $parameter;
                break;

            case 'joinTeleconfBeforeHost':
                $results['schedule'][$key] = $parameter ? 'TURE' : 'FALSE';
                break;
            }
        }
        
        return $results;
    }

    /**
     * 將陣列轉換為xml
     *
     * @param array  $data    要轉換的陣列
     * @param object $xmlData SimpleXMLElement物件
     * 
     * @return void
     */
    public function convertToXml($data, &$xmlData)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'person'; //dealing with <0/>..<n/> issues
                }
                $subnode = $xmlData->addChild($key);
                $this->convertToXml($value, $subnode);
            } else {
                $xmlData->addChild("$key", htmlspecialchars("$value"));
            }
        }
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
        foreach ($parameters as $key => $value) {
            switch ($key) {
            case 'start_time':
                $parameters['startDate'] = date("m/d/Y H:i:s", $value);
                break;

            case 'joinTeleconfBeforeHost':
                $parameters[$key] = strtoupper($value);
                continue 2;

            case 'meeting_name':
                $parameters['confName'] = $value;
                break;

            case 'meeting_password':
                $parameters['meetingPassword'] = $value;
                break;

            case 'max_user_num':
                $parameters['maxUserNumber'] = $value;
                break;

            case 'meeting_id':
                $parameters['meetingKey'] = $value;
                break;

            default:
                continue 2;
            }

            unset($parameters[$key]);
        }

        return $parameters;
    }

    /**
     * 解析API返回的xml
     *
     * @param string $content 回應的xml
     * 
     * @return array
     */
    protected function parseXml($content)
    {
        $xml = simplexml_load_string($content);

        $responseHeader = $xml->children('serv', true)->header->response;
        $bodyContent = $xml->children('serv', true)->body->bodyContent
            ->children('meet', true);

        if (reset($responseHeader->result) == 'SUCCESS') {
            return $bodyContent;
        } else {
            return $responseHeader;
        }
    }

    /**
     * 串出完整xml
     *
     * @param string $parameters 所有參數
     * 
     * @return json string
     */
    public function parametersFormat($parameters)
    {
        $parameters = $this->getParameters($parameters);

        /**
         * 將參數陣列做轉換，轉換後由於需與驗證資料結合，因此需替換掉不需要的標籤
         */
        $xmlData = new SimpleXMLElement('<data/>');

        $this->convertToXml($parameters, $xmlData);
        $requestXml = str_replace(
            ['<data>', '</data>', '<?xml version="1.0"?>'], '', $xmlData->asXML()
        );
        
        $paramsXml = '<?xml version="1.0" encoding="UTF-8"?>
                <serv:message xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <header><securityContext>
                    <webExID>' . $this->config['account_name'] . '</webExID>
                    <password>' . $this->config['account_password'] . '</password>
                    <siteName>' . $this->config['site_name'] . '</siteName>
                </securityContext></header>
                <body>
                <bodyContent xsi:type="java:com.webex.service.binding.meeting.' . $this->action . '">
                ' . $requestXml . '
                </bodyContent></body></serv:message>';

        return $paramsXml;
    }

    /**
     * 送出請求
     *
     * @param mixed  $parameters  送出參數
     * @param string $action      請求動作
     * @param string $method      請求動詞(POST, GET, PUT, DELETE, PATCH)
     * @param string $sendWay     送出請求方式(form_params, query, body, json)
     * @param array  $headers     請求headers
     * @param bool   $isJsonParse 結果是否要json decode
     * 
     * @return object
     */
    public function sendRequest(
        $parameters, $action = '', $method = 'POST', 
        $sendWay = 'body', $headers = [], $isJsonParse = false
    ) {
        if (!isset($this->config['site_name']) || $this->config['site_name'] == '') {
            throw new Exception('Need [site_name]');
        }

        $this->action = $action;

        $headers = ['Content-Type' => 'text/xml'];

        $response = parent::sendRequest(
            $parameters, '', $method, $sendWay, $headers, false
        );

        return json_decode(json_encode($this->parseXml($response)));
    }
}