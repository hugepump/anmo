<?php 
namespace app\Common;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use	think\facade\Config;
/**
 * 发布消息Api
 */

class PublisherApi {
    protected $configs;
    protected $connection;
    protected $exchangeName;
    protected $queueName;
    protected $routingKey;
    protected $qos_limit = 20;
    function __construct() {
        $this->configs = array(
            'host'     => Config::get('rabbit.rabbit_host'),
            'port'     => Config::get('rabbit.rabbit_port'),
            'login'    => Config::get('rabbit.rabbit_login'),
            'password' => Config::get('rabbit.rabbit_passwd'),
            'vhost'    => Config::get('rabbit.rabbit_vhost')
        );
        $this->qos_limit = Config::get('rabbit.rabbit_qos_limit');
        $this->exchangeName = Config::get('rabbit.rabbit_exchange_name'); 
        $this->queueName = Config::get('rabbit.rabbit_query_name'); 
        $this->routingKey = Config::get('rabbit.rabbit_key');
    }
    public function publishMessage($message) {
        $this->connection = new AMQPStreamConnection(
            $this->configs['host'],
            $this->configs['port'],
            $this->configs['login'],
            $this->configs['password'],
            $this->configs['vhost']
        );
        // $channel = new \AMQPChannel($this->conn);
        // $ex = new \AMQPExchange($channel);
        // $ex->setName($this->exchangeName);
        // $ex->setType(AMQP_EX_TYPE_DIRECT);
        // $ex->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);
        // $ex->declare();
        // $q = new \AMQPQueue($channel);
        // $q->setName($this->queueName);
        // $q->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);
        // $q->declare();
        // $q->bind($this->exchangeName, $this->routingKey);
        // $ex->publish($message, $this->routingKey);
        // $this->conn->disconnect();
        $connection = $this->connection;
        $channel = $connection->channel();
        $channel->exchange_declare($this->exchangeName, 'direct', false, false, false);
        $channel->queue_declare($this->queueName, false, true, false, false, false);
        $channel->queue_bind($this->queueName, $this->exchangeName, $this->routingKey);

        $msg = new AMQPMessage($message, array(
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ));

        $channel->basic_publish($msg, $this->exchangeName, $this->routingKey);

        $channel->close();
        $connection->close();
    }
    public function delayMessage($message, $expiration = 10000) {
        $this->connection = new AMQPStreamConnection(
            $this->configs['host'],
            $this->configs['port'],
            $this->configs['login'],
            $this->configs['password'],
            $this->configs['vhost']
        );
        $connection = $this->connection;
//		var_dump($connection);die;
        $channel = $connection->channel();

        $cache_exchange_name = 'cache_exchange' . $expiration;
		
        $cache_queue_name = 'cache_queue' . $expiration;
        $channel->exchange_declare($this->exchangeName, 'direct', false, false, false);
        $channel->exchange_declare($cache_exchange_name, 'direct', false, false, false);

        $tale = new AMQPTable();
        $tale->set('x-dead-letter-exchange', $this->exchangeName);
        $tale->set('x-dead-letter-routing-key', $this->routingKey);
        $tale->set('x-message-ttl', $expiration);
        $channel->queue_declare($cache_queue_name, false, true, false, false, false,$tale);
        $channel->queue_bind($cache_queue_name, $cache_exchange_name, '');

        $channel->queue_declare($this->queueName, false, true, false, false, false);
        $channel->queue_bind($this->queueName, $this->exchangeName, $this->routingKey);

        $msg = new AMQPMessage($message, array(
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ));

        $channel->basic_publish($msg, $cache_exchange_name, '');

        $channel->close();
        $connection->close();
    }
    public function scheduleMessage($message, $taskName, $expiration = 10000) {
        $this->connection = new AMQPStreamConnection(
            $this->configs['host'],
            $this->configs['port'],
            $this->configs['login'],
            $this->configs['password'],
            $this->configs['vhost']
        );
        $connection = $this->connection;
        $channel = $connection->channel();

        $schedule_exchange_name = 'schedule_exchange_' . $taskName;
        $schedule_queue_name = 'schedule_queue_' . $taskName;

        $channel->exchange_declare($this->exchangeName, 'direct', false, false, false);
        $channel->exchange_declare($schedule_exchange_name, 'direct', false, false, false);

        $tale = new AMQPTable();
        $tale->set('x-dead-letter-exchange', $this->exchangeName);
        $tale->set('x-dead-letter-routing-key', $this->routingKey);
        $tale->set('x-message-ttl', $expiration);

        $channel->queue_declare($schedule_queue_name, false, false, false, false, false,$tale);
        $channel->queue_bind($schedule_queue_name, $schedule_exchange_name, '');

        $channel->queue_declare($this->queueName, false, true, false, false, false);
        $channel->queue_bind($this->queueName, $this->exchangeName, $this->routingKey);

        $msg = new AMQPMessage($message, array(
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ));

        $channel->basic_publish($msg, $schedule_exchange_name, '');

        $channel->close();
        $connection->close();
    }
    public function scheduleInfo($taskName, $expiration = 10000) {
        $this->connection = new AMQPStreamConnection(
            $this->configs['host'],
            $this->configs['port'],
            $this->configs['login'],
            $this->configs['password'],
            $this->configs['vhost']
        );
        $connection = $this->connection;
        $channel = $connection->channel();

        $schedule_queue_name = 'schedule_queue_' . $taskName;

        $tale = new AMQPTable();
        $tale->set('x-dead-letter-exchange', $this->exchangeName);
        $tale->set('x-dead-letter-routing-key', $this->routingKey);
        $tale->set('x-message-ttl', $expiration);
        $result = $channel->queue_declare($schedule_queue_name, false, false, false, false, false,$tale);

        $channel->close();
        $connection->close();
        $result = $result ?: [0, 0];
        return [
            'ready'   => $result[1],
            'unacked' => $result[2]
        ];
    }
    public function consumer() {
        $this->connection = new AMQPStreamConnection(
            $this->configs['host'],
            $this->configs['port'],
            $this->configs['login'],
            $this->configs['password'],
            $this->configs['vhost'],
            false, // insist
            'AMQPLAIN', // login_method
            null, // login_response
            'en_US', // locale
            3, // connection_timeout
            360, // read_write_timeout
            null, // context
            false, // keepalive
            180 // heartbeat
        );
        $connection = $this->connection;
        $channel = $connection->channel();
        $channel->exchange_declare($this->exchangeName, 'direct', false, false, false);
        $channel->queue_declare($this->queueName, false, true, false, false, false);
        $channel->queue_bind($this->queueName, $this->exchangeName, $this->routingKey);
        $callback = function ($msg) {
            // message原文位于消息对象body属性中
            messagesProcess($msg);
        };
		
        //流量控制
        $channel->basic_qos(null, $this->qos_limit, null);
        $channel->basic_consume($this->queueName, '', false, false, false, false, $callback);
        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }
}

