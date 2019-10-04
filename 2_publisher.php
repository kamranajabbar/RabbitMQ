<?php 
    require_once __DIR__ . '/vendor/autoload.php';
    use \PhpAmqpLib\Connection\AMQPStreamConnection;
    use \PhpAmqpLib\Message\AMQPMessage;

    define("RABBITMQ_HOST", "127.0.0.1");
    define("RABBITMQ_PORT", 5672);
    define("RABBITMQ_USERNAME", "guest");
    define("RABBITMQ_PASSWORD", "guest");
    define("RABBITMQ_QUEUE_NAME", "test-queue");

    $connection = new AMQPStreamConnection(
        RABBITMQ_HOST,
        RABBITMQ_PORT,
        RABBITMQ_USERNAME,
        RABBITMQ_PASSWORD
    );

    $channel = $connection->channel();

    # Create the queue if it does not already exist.
    $channel->queue_declare(
        $queue       = RABBITMQ_QUEUE_NAME,
        $passive     = false,
        $durable     = true,    // true means declare it as durable 
        $exclusive   = false,
        $auto_delete = false,
        $nowait      = false,
        $arguments   = null,
        $ticket      = null
    );

    $job_id = 0;
    while (true)
    {
        $jobArray = array(
            'id'           => $job_id++,
            'task'         => 'sleep',
            'sleep_period' => rand(0, 3)
        );

        $msg = new AMQPMessage(
            json_encode($jobArray, JSON_UNESCAPED_SLASHES),
            array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)     # make message persistent
            //array('delivery_mode' => AMQPMessage::DELIVERY_MODE_NON_PERSISTENT) # make message non-persistent
        );

        $channel->basic_publish($msg, '', RABBITMQ_QUEUE_NAME);
        print 'Job ' . $job_id . ' created' . PHP_EOL;
        sleep(1);
    }
?>