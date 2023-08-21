<?php

return [
    "google" => [
        'g_client_id' => "####",
        'g_client_secret' => "###",
        'g_client_status' => "#####",
    ],
    "gateway_credentials" => [
        "sms" => [
            "default_gateway_id" => 1,
            "101NEX" => [
                "api_key" => "####",
                "api_secret" => "####",
                "sender_id" => 1
            ],
            "102TWI" => [
                "account_sid" => "####",
                "auth_token" => "####",
                "from_number" => "####",
                "sender_id" => "####"
            ],
            "103BIRD" => [
                "access_key" => "####",
                "sender_id" => "####",
            ],
            "104MAG" => [
                "api_key" => "####",
                "text_magic_username" => "#####",
                "sender_id" => "####"
            ],
            "105CLICKATELL" => [
                "clickatell_api_key" => "####",
                "sender_id" => "####"
            ],
            "106INFOBIP" => [
                "infobip_base_url" => "####",
                "infobip_api_key" => "####",
                "sender_id" => "####"
            ],
            "107SMSBROADCAST" => [
                "sms_broadcast_username" => "####",
                "sms_broadcast_password" => "####",
            ],
        ],
        "email" => [
            "default_gateway_id" => 1,
            "SMTP" => [
                "driver" => "SMTP",
                "host" => "smtp.mailtrap.io",
                "port" => "2525",
                "encryption" => 'tls',
                "username" => null,
                "password" => null,
                "from_address" => null,
                "from_name" => null,
            ],
            "send_grid_api" => [
                "app_key" => "####",
                "from_address" => "demo@gmail.com",
                "from_name" => "Demo User",
            ]
        ]
    ],
];


//'from'       => [
//'address'=> @$mail->driver_information->from->address,
//'name'   => @$mail->driver_information->from->name
//],
//'encryption' => @$mail->driver_information->encryption=="PWMTA"?null:$mail->driver_information->encryption,
//'username'   => @$mail->driver_information->username,
//'password'   => @$mail->driver_information->password,
//'sendmail'   => '/usr/sbin/sendmail -bs',
//'pretend'    => false,
