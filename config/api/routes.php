<?php

use App\Api\Home\HomeController;

return [
    "/" => [
        "GET" => [
            "handler" => HomeController::class . "::default"
        ]
    ]
];
