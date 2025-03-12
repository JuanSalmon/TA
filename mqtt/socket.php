<?php

require 'vendor/autoload.php';
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface{
    protected $clients;

    public function __construct(){
        $this->clients = new \SplObjectStorage;
    }
    public function onOpen(ConnectionInterface $conn){
        $this->clients->attach($conn);
        echo "Koneksi baru: {$conn->resourceId}\n";
    }
    public function onMassage(ConnectionInterface $form, $msg){
        $data = json_decode($msg, true);
        $message = $data['message'];
        
        foreach($this->clients as $client){
            if($form !== $client){
                $client->send($message);
            }
        }
    }
    public function onClose(ConnectionInterface $conn){
        $this->clients->detach($conn);
        echo "koneksi {$conn->resourceId} telah ditutup\n";
    }
    public function onError(ConnectionInterface $conn, \Exception $e){
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new Chat()
        )
    ),
    8083
);

$server->run();
?>
