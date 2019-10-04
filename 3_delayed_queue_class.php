<?php   
    require_once __DIR__ . '/vendor/autoload.php';

    use PhpAmqpLib\Connection\AMQPStreamConnection;
    use PhpAmqpLib\Message\AMQPMessage;
    use PhpAmqpLib\Wire\AMQPTable;

    class Amqp {
        private $connection;
        private $delayedQueueName;
        private $channel;
        private $callback;

        private $host;
        private $port;
        private $user;
        private $pass;
        private $queueName;

        //OK
        public function __construct($host, $port, $user, $pass, $queueName) 
        {
            $this->connection       = new AMQPStreamConnection($host, $port, $user, $pass);
            $this->queueName        = $queueName;
            $this->delayedQueueName = null;

            $this->channel = $this->connection->channel();
            $this->channel->queue_declare(
                $queue       = $this->queueName,
                $passive     = false,
                $durable     = true,    // true means declare it as durable 
                $exclusive   = false,
                $auto_delete = false,
                $nowait      = false,
                $arguments   = null,
                $ticket      = null
            );
        }

        //OK
        public function __destruct()
        {
            $this->close();
        }

        //OK
        public function close()
        {
            if (!is_null($this->channel)) 
            {
                $this->channel->close();
                $this->channel = null;
            }

            if (!is_null($this->connection)) 
            {
                $this->connection->close();
                $this->connection = null;
            }
        }

        //OK
        public function produceWithDelay($data, $delay)
        {
            if (is_null($this->delayedQueueName))
            {
                $delayedQueueName = $this->queueName . '.delayed';

                $this->channel->queue_declare($delayedQueueName, false, true, false, false, false,
                    new AMQPTable(array(
                        'x-dead-letter-exchange' => '',
                        'x-dead-letter-routing-key' => $this->queueName
                    ))
                );

                $this->delayedQueueName = $delayedQueueName;
            }

            $msg = new AMQPMessage(
                json_encode($data, JSON_UNESCAPED_SLASHES),
                array(
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    'expiration' => $delay
                )
            );

            $this->channel->basic_publish($msg, '', $this->delayedQueueName);
        }

        //OK
        public function produce($data)
        {
            $msg = new AMQPMessage(
                json_encode($data, JSON_UNESCAPED_SLASHES),
                array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
            );

            $this->channel->basic_publish($msg, '', $this->queueName);
        }

        //OK
        public function consume($callback)
        {
            echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

            $this->callback = $callback;

            // This tells RabbitMQ not to give more than one message to a worker at a time.
            $this->channel->basic_qos(null, 1, null);

            // Requires ack.
            $this->channel->basic_consume($this->queueName, '', false, false, false, false, array($this, 'consumeCallback'));

            while(count($this->channel->callbacks)) 
            {
                $this->channel->wait();
            }
        }

        //OK
        public function consumeCallback($msg)
        {
            echo " [x] Received ", $msg->body, "\n";
            
            $job = json_decode($msg->body, $assocForm=true);
            sleep($job['sleep_period']);

            // call_user_func_array(
            //     $this->callback,
            //     array($msg)
            // );

            // Very important to ack, in order to remove msg from queue. Ack after callback, as exception might happen in callback.
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        }

        //OK
        public function getQueueSize()
        {
            // three tuple containing (<queue name>, <message count>, <consumer count>)
            $tuple = $this->channel->queue_declare($this->queueName, false, true, false, false);

            if ($tuple != null && isset($tuple[1])) 
            {
                return $tuple[1]+1;
            }

            return -1;
        }
    }
?>