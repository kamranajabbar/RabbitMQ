<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once __DIR__ . '/vendor/autoload.php';
    use PhpAmqpLib\Connection\AMQPStreamConnection;

    $host = 'localhost';
    $port = 5672;
    $user = 'guest';
    $pass = 'guest';

    $connection = new AMQPStreamConnection($host, $port, $user, $pass);
    $channel = $connection->channel();






    /*  
        // queue_declare function parameters with default values

        $queue       = '',
        $passive     = false,
        $durable     = false,
        $exclusive   = false,
        $auto_delete = true,
        $nowait      = false,
        $arguments   = array(),
        $ticket      = null
    */
    $channel->queue_declare('m3-queue-ttl-dlx', true);
    
    echo " --- Ready to received messages from queue. To exit press CTRL+C --- \n";

    // To consumed all message //
    $callback = function ($msg) {
        echo ' [x] Received from Exchange : ', $msg->delivery_info['exchange'], ', with Routing Key : ', $msg->delivery_info['routing_key'], ', Message : ' , $msg->body, "\n";

        @$channel->basic_ack($msg->delivery_info['delivery_tag'], true);
    };

    /*  
        // basic_consume function parameters with default values

        $queue          = '',
        $consumer_tag   = '',
        $no_local       = false,
        $no_ack         = false,
        $exclusive      = false,
        $nowait         = false,
        $callback       = null,
        $ticket         = null,
        $arguments      = array()
    */
    $channel->basic_consume('m3-queue-ttl-dlx', '', false, true, false, false, $callback);
    
    // Wait in CLI for receiving responce
    while (count($channel->callbacks)) {
        $channel->wait();
    }







    // To consumed single message //
    /*   
        // basic_get function parameters with default values

        $queue  = '', 
        $no_ack = false, 
        $ticket = null
    */
    // $message = $channel->basic_get('m3-queue-durable', true, null);
    // echo empty($message->body) ? " [*] Message : No Message Available \n" : " [*] Message : " . $message->body . "\n";
    
    $channel->close();
    $connection->close();
?>