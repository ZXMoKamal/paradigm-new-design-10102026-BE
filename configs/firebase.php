<?php

require_once __DIR__ . "/../vendor/autoload.php";

// var_dump(class_exists(\Kreait\Firebase\Factory::class));
use Kreait\Firebase\Factory;

$factory = (new Factory)
    ->withServiceAccount(__DIR__ . "/../../../../../configs/paradigm-capital-group-firebase-adminsdk-fbsvc-4328d554b5.json");

$auth = $factory->createAuth();