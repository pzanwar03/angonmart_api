<?php

$newsletterApiKey = env('NEWSLETTER_API_KEY');
$hasMailchimpApiKey = is_string($newsletterApiKey) && str_contains($newsletterApiKey, '-');

return [

    /*
     * The driver to use to interact with MailChimp API.
     * MailChimp is used only when NEWSLETTER_API_KEY looks valid
     * (keys include a datacenter suffix, e.g. xxxx-us21).
     */
    'driver' => env(
        'NEWSLETTER_DRIVER',
        $hasMailchimpApiKey
            ? Spatie\Newsletter\Drivers\MailChimpDriver::class
            : Marvel\Newsletter\NullDriver::class
    ),

    /**
     * These arguments will be given to the driver.
     */
    'driver_arguments' => [
        'api_key' => $newsletterApiKey,

        'endpoint' => env('NEWSLETTER_ENDPOINT'),
    ],

    /*
     * The list name to use when no list name is specified in a method.
     */
    'default_list_name' => 'subscribers',

    'lists' => [

        /*
         * This key is used to identify this list. It can be used
         * as the listName parameter provided in the various methods.
         *
         * You can set it to any string you want and you can add
         * as many lists as you want.
         */
        'subscribers' => [

            /*
             * When using the Mailcoach driver, this should be Email list UUID
             * which is displayed in the Mailcoach UI
             *
             * When using the MailChimp driver, this should be a MailChimp list id.
             * http://kb.mailchimp.com/lists/managing-subscribers/find-your-list-id.
             */
            'id' => env('NEWSLETTER_LIST_ID'),
        ],
    ],
];
