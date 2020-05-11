# meeting service
第三方視訊服務API封裝

Installation:

    Step1. Running command: composer require ashleytsai/meeting

    Step2. Add the service provider to the providers array in the config/app.php config file as follows:
        'providers' => [

            ...

            MeetingService\Providers\MeetingServiceProvider::class,
        ]

    Step 3. Running command: 
        php artisan vendor:publish --provider="MeetingService\Providers\MeetingServiceProvider"

        You should now have a config/meeting.php file that allows you to configure the basics of this package.

    Step 4. Update connection setting that you want to drive or anything you want to set in config/meeting.php file

    Step 5. Add MEETING_DRIVER to .env file to match default setting in config/meeting.php file. 
        This package support "zoom", "webex", "gotomeeting" service.

    Now you are ready to use!

How to use:

    