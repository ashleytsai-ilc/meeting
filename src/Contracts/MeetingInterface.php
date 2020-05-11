<?php

namespace MeetingService\Contracts;

/**
 * 定義Meeting需要實作的方法
 * 
 * @author Ashley Tsai <ashley.tsai@language-center.com.tw>
 * @date   2020-04-17
 */
interface MeetingInterface
{
    /**
     * 依參數建立會議
     *
     * @param array $options 會議相關參數
     * 
     * @return mixed
     */
    public function create($options);

    /**
     * 依參數更新會議
     *
     * @param array $options 會議相關參數
     * 
     * @return mixed
     */
    public function update($options);

    /**
     * 刪除會議
     *
     * @param array $accessData 會議參數
     * 
     * @return mixed
     */
    public function delete($accessData);

    /**
     * 取得指定會議資料
     *
     * @param array $accessData 會議參數
     * 
     * @return mixed
     */
    public function get($accessData);

    /**
     * 發送參數至第三方API
     *
     * @param mixed  $parameters API所需參數
     * @param string $url        API URL
     * @param string $method     請求方法
     * @param string $sendWay    發送方式
     * @param array  $headers    請求header
     * 
     * @return array
     */
    public function sendRequest(
        $parameters, $url, $method = 'POST', $sendWay = 'form_param', $headers = []
    );
}

