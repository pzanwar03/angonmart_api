<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI')
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI')
    ],

    'twilio' => [
        'account_sid'      => env('TWILIO_ACCOUNT_SID'),
        'auth_token'       => env('TWILIO_AUTH_TOKEN'),
        'verification_sid' => env('TWILIO_VERIFICATION_SID'),
        'from'             => env('TWILIO_FROM_NUMBER'),
    ],

    'messagebird' => [
        'api_key'    => env('MESSAGEBIRD_API_KEY'),
        'originator' => env('MESSAGEBIRD_ORIGINATOR'),
    ],

    'sendmysms' => [
        'url'         => env('SENDMYSMS_URL', 'https://sendmysms.net/api.php'),
        'user'        => env('SENDMYSMS_USER'),
        'key'         => env('SENDMYSMS_KEY'),
        'otp_length'  => env('OTP_LENGTH', 6),
        'otp_ttl'     => env('OTP_TTL', 300),
        'otp_message' => env('OTP_MESSAGE', 'Your verification code is :code'),
    ],

    'smsgatewaybd' => [
        'base_url'    => env('SMSGATEWAYBD_URL', 'https://api.smsgateway.com.bd/api'),
        'client_id'   => env('SMSGATEWAYBD_CLIENT_ID'),
        'key'         => env('SMSGATEWAYBD_KEY'),
        'sender_id'   => env('SMSGATEWAYBD_SENDER_ID'),
        'otp_length'  => env('OTP_LENGTH', 6),
        'otp_ttl'     => env('OTP_TTL', 300),
        'otp_message' => env('OTP_MESSAGE', 'Your verification code is :code'),
    ],

];
