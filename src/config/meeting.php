<?php 

return [

    /**
     * 預設視訊服務
     * 
     * Supported: "zoom", "webex", "gotomeeting"
     */

    'default' => env('MEETING_DRIVER', null),

    'timezone' => 'UTC',

    'guzzle_verify' => true,

    'connections' => [
        'zoom' => [
            'driver' => 'zoom',
            'access_key' => 'your-zoom-access-key',
            'access_secret' => 'your-zoom-access-secret',
            'request_url' => 'http://timer.91veo.com/v1/meeting/',
        ],

        'webex' => [
            'driver' => 'webex',
            'account_name' => 'your-webex-account-name',
            'account_password' => 'your-webex-account-password',
            'site_name' => 'your-webex-site-name',
            'request_url' => 'https://meetingsapac3.webex.com/WBXService/XMLService',
        ],

        'gotomeeting' => [
            'driver' => 'gotomeeting',
            'consumer_key' => 'your-gotomeeting-consumer-key',
            'consumer_secret' => 'your-gotomeeting-consumer-secret',
            'account_name' => 'your-gotomeeting-account-name',
            'account_password' => 'your-gotomeeting-account-password',
            'request_url' => 'https://api.getgo.com/G2M/rest/meetings',
            'auth_url' => 'https://api.getgo.com/oauth/v2/token',
        ],
    ],
];