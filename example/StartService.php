<?php
require('../no-composer-require.php');
require('autoload-mynamespace.php');



use iry\queue\Service;
use MyNamespace\Queue2Config\SettingTest2;
use MyNamespace\QueueConfig\SettingTest;

$queueService = new Service(SettingTest2::class);
$queueService ->listen();