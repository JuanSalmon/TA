<?php
require 'vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use React\EventLoop\Factory;
use Ratchet\Client\WebSocket;

$loop = Factory::create();

// Konfigurasi MQTT
$server = 'iot-broker.pnk.ac.id'; // Ganti dengan host Mosquitto Anda
$port = 8083; //port
$clientId = 'iot-web';
$username = 'juan'; // Ganti dengan username Anda
$password = '123'; // Ganti dengan password Anda


// WebSocket server URL
$wsUrl = 'ws://iot-broker.pnk.ac.id:8083';

// Fungsi untuk mengirim pesan ke WebSocket
$sendToWebSocket = function($message) use ($wsUrl, $loop) {
    \Ratchet\Client\connect($wsUrl, [], [], $loop)->then(function(WebSocket $conn) use ($message) {
        $conn->send(json_encode(['message' => $message]));
        $conn->close();
    }, function($e) {
        echo "Tidak dapat terhubung ke WebSocket: {$e->getMessage()}\n";
    });
};

// Setup MQTT Client
$connectionSettings = new ConnectionSettings();
$connectionSettings
    ->setUsername($username)
    ->setPassword($password);   

$mqtt = new MqttClient($server, $port, $clientId, null, $loop);
$mqtt->connect($connectionSettings);

$topic = [
    '$SYS/broker/subscriptions/count', 
    '$SYS/broker/clients/connected', 
    //'topic/test3'
    ]; 


$mqtt->subscribe($topic, function ($topic, $message) use ($loop) {
    echo "Pesan diterima";
    // : {$message} pada topic {$topic}\n"; 
    sendToWebSocket("{$topic}: {$message}");
}, 0);

echo "Subscribed to multipe topics:" . implode(",", array_keys($topic)) ."\n";

// Jalankan loop
$mqtt->loop(true);
$loop->run();
?>