<?php

    // 🔧 rules (match your frontend)
    $rules = [
        "name" => ["min" => 2, "max" => 100],
        "email" => ["min" => 5, "max" => 150],
        "phone" => ["min" => 7, "max" => 20],
        "subject" => ["min" => 3, "max" => 150],
        "message" => ["min" => 10, "max" => 1000],
    ];