<?php
$session = mt_rand(1, 999);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <script src="js/jquery.js" type="text/javascript"></script>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: arial, sans-serif;
            resize: none;
        }

        html, body {
            width: 100%;
            height: 100%;
        }

        #chat_input {
            position: absolute;
            bottom: 0;
            left: 0;
            padding: 10px;
            width: 100%;
            height: 100px;
            border: 1px solid #ccc;
        }


        #chatbox {
            overflow: none;
            position: _ relative;
            width: 100%;
            height: 100%;
            border: 1px solid #ccc;
        }

        #chat_output {
            overflow: scroll;
            position: absolute;
            bottom: 100px;
            width: 100%;
            max-height: 80%;
        }

        #chat_output div {
            /*border: 1px solid #e2e4e3;*/
            /*border-radius: 5px;*/
            /*margin: 5px;*/
            padding: 5px;
        }
    </style>
</head>
<body>
<?php
$config = include './config.php';
?>
<div id="wrapper11">
    <div id="chatbox">
        <div id="chat_output"></div>
    </div>
    <textarea id="chat_input" placeholder="Enter your name...."></textarea>
    <script type="text/javascript">
        var sessionUser = null;
        var element = document.getElementById("chat_output");
        jQuery(function ($) {
            // Websocket
            var websocket_server = new WebSocket("ws://<?=$config['url']?>:<?=$config['port']?>/");
            websocket_server.onopen = function (e) {
                websocket_server.send(
                    JSON.stringify({
                        'type': 'socket',
                        //'user_id':<?php //echo $session; ?>
                        'user_id': sessionUser
                    })
                );
            };
            websocket_server.onerror = function (e) {
                console.log(e);
            }
            websocket_server.onmessage = function (e) {
                var json = JSON.parse(e.data);
                switch (json.type) {
                    case 'chat':
                        $('#chat_output').append(json.msg);

                        element.scrollTop = element.scrollHeight;
                        break;
                }
            }

            // Events
            $('#chat_input').on('keyup', function (e) {


                    if (e.keyCode == 13 && !e.shiftKey) {

                        if(!sessionUser){
                            sessionUser = $(this).val();

                            sessionUser = sessionUser.split(' ')[0];
                            sessionUser = sessionUser.trim();
                            $(this).val('');
                            $('#chat_input').attr('placeholder', 'start write....');
                            return;
                        }

                        var chat_msg = $(this).val();
                        websocket_server.send(
                            JSON.stringify({
                                'type': 'chat',
                                //'user_id':<?php //echo $session; ?>//,
                                'user_id': sessionUser,
                                'chat_msg': chat_msg
                            })
                        );
                        $(this).val('');
                    }
            });
        });
    </script>
</div>
</body>
</html>