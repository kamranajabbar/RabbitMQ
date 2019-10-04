<?php 
    require_once __DIR__ . '/vendor/autoload.php';
    use \PhpAmqpLib\Connection\AMQPStreamConnection;

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
        $durable     = true,
        $exclusive   = false,
        $auto_delete = false,
        $nowait      = false,
        $arguments   = null,
        $ticket      = null
    );

    echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

    $callback = function($msg) {
        echo " [x] Received ", $msg->body, "\n";
        $job = json_decode($msg->body, $assocForm=true);
        sleep($job['sleep_period']);
        echo " [x] Done", "\n";

        // Acknowledgement must be sent on the same channel that received the delivery.
        // Very important to ack, in order to remove msg from queue. Ack after callback, as exception might happen in callback.
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    };

    // This tells RabbitMQ not to give more than one message to a worker at a time.
    $channel->basic_qos(null, 1, null);

    $channel->basic_consume(
        $queue          = RABBITMQ_QUEUE_NAME,
        $consumer_tag   = '',
        $no_local       = false,
        $no_ack         = false,    // "true" means no ack
        $exclusive      = false,
        $nowait         = false,
        $callback
    );

    while (count($channel->callbacks)) 
    {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
?>