<?php
set_time_limit(0);

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
require_once __DIR__.'/../vendor/autoload.php';

$config = include __DIR__.'/../config.php';

class Chat implements MessageComponentInterface {
	protected $clients;
	protected $users;
	protected $config;
	protected $storageFile = 'history.txt';
    protected $fileToWrite;

	public function __construct() {
		$this->clients = new \SplObjectStorage;
		$this->config = include __DIR__.'/../config.php';
        $this->storageFile = __DIR__.$this->storageFile;
	}

	public function __destruct()
    {
//        fclose($this->fileToWrite);
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        // $this->users[$conn->resourceId] = $conn;

        $content = file_get_contents($this->storageFile);
        $type = 'chat';
        $response_to = $content;
        foreach (explode('</div>', $content) as $msg){
            $conn->send(json_encode(array("type"=>$type,"msg"=>$msg)));
        }
    }

	public function onClose(ConnectionInterface $conn) {
		$this->clients->detach($conn);
		// unset($this->users[$conn->resourceId]);
	}

	public function onMessage(ConnectionInterface $from,  $data) {
		$from_id = $from->resourceId;
		$data = json_decode($data);
		$type = $data->type;
		switch ($type) {
			case 'chat':
				$user_id = $data->user_id;
				$chat_msg = $data->chat_msg;
				$response_from = "<div style='color:#999'><strong>$user_id: </strong>$chat_msg</div>";
				$response_to = "<div style='color:#4c4c4c'><strong>$user_id: </strong>:$chat_msg</div>";
				// Output
				$from->send(json_encode(array("type"=>$type,"msg"=>$response_from)));
				foreach($this->clients as $client)
				{
					if($from!=$client)
					{
						$client->send(json_encode(array("type"=>$type,"msg"=>$response_to)));
					}
				}

                $txt = $response_to;
                $this->fileToWrite = fopen($this->storageFile, "a") or die("Unable to open file!");
                fwrite($this->fileToWrite, $txt);

				break;
		}
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		$conn->close();
	}
}
$server = IoServer::factory(
	new HttpServer(new WsServer(new Chat())),
	$config['port']
);
$server->run();
?>