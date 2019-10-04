<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    include('./3_delayed_queue_class.php');

    $host      = "127.0.0.1";
    $port      = 5672;
    $user      = "guest";
    $pass      = "guest";
    $queueName = "test-queue";

    $objAMQP = new Amqp($host,$port,$user,$pass,$queueName);

    $objAMQP->consume(null);
?>