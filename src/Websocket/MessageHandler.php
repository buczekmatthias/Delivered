<?php

namespace App\Websocket;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

class MessageHandler implements MessageComponentInterface
{
    protected $connections;

    public function __construct()
    {
        $this->conn = new SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->conn->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        foreach ($this->conn as $conn) {
            if ($conn === $from) continue;

            $conn->send([$from, $msg]);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->conn->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        $this->conn->detach($conn);
        $conn->close();
    }
}
