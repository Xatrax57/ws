<?php
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServerInterface;
use Ratchet\Server\IoServer;

require 'vendor/autoload.php';

class Chat implements MessageComponentInterface {

    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Nowy klient połączył się
        $this->clients->attach($conn);
        echo "Nowe połączenie! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Otrzymano wiadomość: $msg\n";
    
        // Dekoduj JSON na tablicę PHP
        $data = json_decode($msg, true);
    
        if (json_last_error() === JSON_ERROR_NONE && isset($data['action'])) {
            // Instrukcje co do poszczególnych akcji
            if ($data['action'] === 'message-send') {
                foreach ($this->clients as $client) {
                    // Wysyłaj wiadomość do wszystkich klientów, w tym do nadawcy
                    $client->send(json_encode($data));
                }
            }
        } else {
            // Obsługuje błędy JSON
            echo "Błąd parsowania JSON: " . json_last_error_msg() . "\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        echo "Połączenie zamknięte ({$conn->resourceId})\n";
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Błąd: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat() 
        )
    ),
    8080
);

echo "Serwer uruchomiony na porcie 8080\n";
$server->run();