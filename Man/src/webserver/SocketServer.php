<?php

namespace WebServer;

use App\Enums\ManEnum;
use App\Man as ManApp;
use React\EventLoop\Loop;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\Socket\SocketServer as ReactSocketServer;

class SocketServer implements MessageComponentInterface
{
    private \SplObjectStorage $clients;
    private ManApp $man;
    private float $interval = 0.1;
    private $server = null;

    private $loop = null;

    public function __construct(ManApp $man)
    {
        $this->clients = new \SplObjectStorage;
        $this->man = $man;
    }

    public function start(int $port = 8080): void
    {
        $loop = Loop::get();

        // Créer le socket server en spécifiant seulement le port et le contexte d'options
        $socket = new ReactSocketServer("0.0.0.0:$port", [], $loop);

        // Créer le serveur WebSocket et HttpServer
        $server = new IoServer(
            new HttpServer(new WsServer($this)),
            $socket,
            $loop
        );

        $this->loop = $loop;

        echo "Server running at 0.0.0.0:$port\n";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients[$conn] = true;
        echo "New client connected: {$conn->resourceId}\n";
        if ($this->man->state === ManEnum::UNUNITIALIZED) {
            $this->configMan($conn);
        }
    }

    private function configMan(ConnectionInterface $conn): void
    {
        $conn->send(json_encode([
            'configuring' => [
                'arrets' => json_encode(json_decode(file_get_contents(__DIR__ . '/../data/arrets.json'))),
                'bus' => json_encode(json_decode(file_get_contents(__DIR__ . '/../data/bus.json'))),
                'routes' => json_encode(json_decode(file_get_contents(__DIR__ . '/../data/routes.json'))),
                'parcours' => json_encode(json_decode(file_get_contents(__DIR__ . '/../data/parcours.json'))),
                'buses' => json_encode(json_decode(file_get_contents(__DIR__ . '/../data/buses.json'))),
                'peoples' => json_encode(json_decode(file_get_contents(__DIR__ . '/../data/peoples.json')))
            ]
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "Received message: $msg\n";

        if (json_validate($msg)) {
            $msg = json_decode($msg, true);
            if (isset($msg['configuration']) && $this->man->state === ManEnum::UNUNITIALIZED) {
                $this->man->build($msg['configuration']);
                $from->send(json_encode(['configuration' => 'done', 'data' => $this->man->getLastState()]));
                // Ajouter notre timer périodique
                $this->loop->addPeriodicTimer($this->interval, function () {
                    $this->runPeriodiquement();
                });

                // Démarrer le loop
                $this->loop->run();
                $this->broadcast(json_encode(['configuration' => 'done']));
            }
            return;
        }

        switch (trim($msg)) {
            case 'pause':
                $this->man->state = ManEnum::PAUSED;
                break;
            case 'resume':
                $this->man->state = ManEnum::RUNNING;
                break;
            default:
                echo "Unknown command: $msg\n";
                break;
        }
        $this->broadcast($this->man->getLastState());
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Client disconnected: {$conn->resourceId}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function broadcast(string $message): void
    {
        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    public function runPeriodiquement(): void
    {
        if ($this->man->state === ManEnum::RUNNING) {
            $this->man->runOnce();
            $this->broadcast($this->man->getLastState());
        }

        if ($this->man->state === ManEnum::SUCCEEDED) {
            $this->broadcast(json_encode(['state' => 'succeeded']));
            $this->close();
        }
    }

    public function close(): void
    {
        foreach (Loop::get() as $timer) {
            Loop::get()->cancelTimer($timer);
        }

        foreach ($this->clients as $client) {
            $client->close();
        }
    }
}
