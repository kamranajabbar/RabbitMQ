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

    $job_id = 0;
    while (true)
    {
        $data = array(
            'id'           => $job_id++,
            'task'         => 'sleep',
            'sleep_period' => rand(0, 3)
        );

        $delay = 10;
        $objAMQP->produceWithDelay($data, $delay);
        print 'Job # ' . $job_id . ' has been created with produceWithDelay()' . PHP_EOL;
        
        $objAMQP->produce($data);
        print 'Job # ' . $job_id . ' has been created width produce()' . PHP_EOL;

        $getQueueSize = $objAMQP->getQueueSize();
        print 'Total Queue is ' . $getQueueSize . PHP_EOL;

        print PHP_EOL;

        sleep(1);
    }
?>