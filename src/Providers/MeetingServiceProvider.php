<?php

namespace MeetingService\Providers;

use Illuminate\Support\ServiceProvider;
use MeetingService\Services\MeetingFactory;

/**
 * 會議服務提供者
 * 
 * @author Ashley Tsai <ashley.tsai@language-center.com.tw>
 * @date   2020-05-08
 */
class MeetingServiceProvider extends ServiceProvider
{
    /**
     * 啟動服務
     * 
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/meeting.php';

        /**
         * 推送設定檔至config資料夾
         */
        $this->publishes([$configPath => config_path('meeting.php')], 'config');
    }

    /**
     * 註冊服務
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            MeetingFactory::class, function ($app) {
                $config = $app['config']['meeting'];

                return new MeetingFactory($config);
            }
        ); 
    }
}
