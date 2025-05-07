<?php
require 'vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Ratchet\Client\WebSocket;
use React\EventLoop\Factory;

$loop = Factory::create();

// Konfigurasi MQTT
$server = 'iot-broker.pnk.ac.id';
$port = 1883;
$clientId = 'jubox';
$username = 'juan';
$password = '123';
$wsUrl = 'ws://localhost:8085';

// Fungsi untuk mengirim pesan ke WebSocket
$sendToWebSocket = function($topic, $message) use ($wsUrl, $loop) {
    \Ratchet\Client\connect($wsUrl, [], [], $loop)->then(function(WebSocket $conn) use ($topic, $message) {
        $payload = json_encode(['topic' => $topic, 'message' => $message]);
        $conn->send($payload);
        echo "Mengirim ke WebSocket: $payload\n";
        $conn->close();
    }, function($e) {
        echo "WebSocket error: {$e->getMessage()}\n";
    });
};

// Konfigurasi MQTT
$connectionSettings = (new ConnectionSettings)
    ->setUsername($username)
    ->setPassword($password)
    ->setKeepAliveInterval(60);

$mqtt = new MqttClient($server, $port, $clientId, MqttClient::MQTT_3_1_1, null, null, $loop);

try {
    $mqtt->connect($connectionSettings);
    echo "Berhasil terhubung ke broker MQTT!\n";

    $topics = ['$SYS/broker/subscriptions/count', '$SYS/broker/clients/connected'];
    foreach ($topics as $topic) {
        $mqtt->subscribe($topic, function ($topic, $message) use ($sendToWebSocket) {
            echo "Pesan diterima: Topic=$topic, Message=$message\n";
            $sendToWebSocket($topic, $message);
        }, 0);
    }

    echo "Subscribed to: " . implode(", ", $topics) . "\n";

    $mqtt->loop(true);
} catch (Exception $e) {
    echo "Gagal terhubung: {$e->getMessage()}\n";
}