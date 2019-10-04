<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once __DIR__ . '/vendor/autoload.php';
    use PhpAmqpLib\Connection\AMQPStreamConnection;
    use PhpAmqpLib\Message\AMQPMessage;

    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();

    $channel->queue_declare('test-queue', false, true, false, false);
    $msg = new AMQPMessage('Hello M3!');
    $channel->basic_publish($msg, '', 'test-queue');

    echo " [x] Sent 'Hello M3!' to receiver\n";
    $channel->close();
    $connection->close();
?>