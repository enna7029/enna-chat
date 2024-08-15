<?php

use GatewayWorker\Lib\Gateway;

class Events
{
    /**
     * Note: 消息回调
     * User: enna
     * Date: 2024-08-13
     * Time: 15:04
     * @param int $client_id 客户端id
     * @param mixed $message 消息
     * @return void
     */
    public static function onMessage($client_id, $message)
    {
        $messageData = json_decode($message, true);

        if (!$messageData) {
            return;
        }

        switch ($messageData['type']) {
            case 'pong':
                return;
            case 'login':
                //判断是否有房间号
                if (!isset($messageData['room_id'])) {
                    throw new \Exception("\$messageData['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }

                $room_id = $messageData['room_id'];
                $client_name = htmlspecialchars($messageData['client_name']);

                //把信息放入session中
                $_SESSION['room_id'] = $room_id;
                $_SESSION['client_name'] = $client_name;

                //获取房间内所有用户列表,包括自己
                $clients_list = Gateway::getClientSessionsByGroup($room_id);
                if ($clients_list) {
                    $clients_list = array_column($clients_list, 'client_name');
                }
                $clients_list[$client_id] = $client_name;

                //广播给当前房间的所有客户端:**进入聊天室
                $newMessage = ['type' => $messageData['type'], 'client_id' => $client_id, 'client_name' => $client_name, 'time' => date('Y-m-d H:i:s')];
                Gateway::sendToGroup($room_id, json_encode($newMessage));
                Gateway::joinGroup($client_id, $room_id);

                //给当前用户发送用户列表
                $newMessage['client_list'] = $clients_list;
                Gateway::sendToCurrentClient(json_encode($newMessage));
                return;
            case 'say':
                if (!isset($_SESSION['room_id'])) {
                    throw new \Exception("\$messageData['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }
                $room_id = $_SESSION['room_id'];
                $client_name = $_SESSION['client_name'];

                if ($messageData['to_client_id'] != 'all') {
                    $newMessage = [
                        'type' => 'say',
                        'from_client_id' => $client_id,
                        'from_client_name' => $client_name,
                        'to_client_id' => $messageData['to_client_id'],
                        'content' => "<b>对你说：</b>" . nl2br(htmlspecialchars($messageData['content'])),
                        'time' => date('Y-m-d H:i:s'),
                    ];
                    Gateway::sendToClient($messageData['to_client_id'], json_encode($newMessage));
                    $newMessage['content'] = "<b>您对" . htmlspecialchars($messageData['to_client_name']) . "说：</b>" . nl2br(htmlspecialchars($messageData['content']));
                    Gateway::sendToCurrentClient(json_encode($newMessage));
                }
                $newMessage = [
                    'type' => 'say',
                    'from_client_id' => $client_id,
                    'from_client_name' => $client_name,
                    'to_client_id' => 'all',
                    'content' => nl2br(htmlspecialchars($messageData['content'])),
                    'time' => date('Y-m-d H:i:s'),
                ];

                Gateway::sendToGroup($room_id, json_encode($newMessage));

                return;
        }
    }

    /**
     * Note: 断开连接回调
     * User: enna
     * Date: 2024-08-14
     * Time: 11:54
     * @param int $client_id 客户端id
     */
    public static function onClose($client_id)
    {
        if (isset($_SESSION['room_id'])) {
            $room_id = $_SESSION['room_id'];
            $newMessage = [
                'type' => 'logout',
                'from_client_id' => $client_id,
                'from_client_name' => $_SESSION['client_name'],
                'time' => date('Y-m-d H:i:s'),
            ];
            Gateway::sendToGroup($room_id, json_encode($newMessage));
        }
    }
}
