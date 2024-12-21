<?php
/*
 * mqp(RabbitMQ) - consumer 消费消息Api
 *
 *************************************/  
namespace app\Common;

use think\facade\Config;
class ConsumerApi{
	public $conn;
    public $q_name;
    public $k_route;
	function __construct(){
    	$conn_args = array(
    		'host'     => Config::get('rabbit.rabbit_host'),
            'port'     => Config::get('rabbit.rabbit_port'),
            'login'    => Config::get('rabbit.rabbit_login'),
            'password' => Config::get('rabbit.rabbit_passwd'),
            'vhost'    => Config::get('rabbit.rabbit_vhost')
    	);
        $this->e_name = Config::get('rabbit.rabbit_exchange_name'); 
        $this->q_name = Config::get('rabbit.rabbit_query_name'); 
        $this->k_route = Config::get('rabbit.rabbit_key');
        $this->conn = new AMQPConnection($conn_args);    
        if (!$this->conn->connect()) {    
        	die("Cannot connect to the broker!\n");    
        }
	}
	public function consumerMessage(){	    
 			$channel = new AMQPChannel($this->conn);    
            $q = new AMQPQueue($channel);  
            $q->setName($this->q_name);    
            $q->setFlags(AMQP_DURABLE);
            while($a=$q->declare())
            {
                $gets = $q->get(AMQP_AUTOACK);
    		    $messages[] = $gets->getBody();
            }
           
            $this->conn->disconnect();
            return $messages;
	}
}	
