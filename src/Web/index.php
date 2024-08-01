<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <title>enna-chat PHP聊天室 Websocket+PHP多线程socket技术</title>
    <script type="text/javascript" src="./js/jquery.min.js"></script>
    <script type="text/javascript">
        if (typeof console == "undefined") {
            this.console = {
                log: function (msg) {
                }
            };
        }

        var ws, name, client_list = {}, room_id, client_id;

        room_id = getQueryString('room_id') ? getQueryString('room_id') : 1;

        function getQueryString(name) {
            var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
            var r = window.location.search.substr(1).match(reg);
            if (r != null) {
                return unescape(r[2]);
            } else {
                return null;
            }
        }

        //连接服务端
        function connect() {
            ws = new WebSocket("ws://" + document.domain + ":7272");

            ws.onopen = onopen;

            ws.onmessage = onmessage;

            ws.onclose = function () {
                console.log("连接关闭,定时重连");
                connect();
            }

            ws.onerror = function () {
                console.log("出现错误");
            }
        }

        //连接建立时,发送登录信息
        function onopen() {
            if (!name) {
                showPrompt();
            }

            var login_data = '{"type":"login","client_name":"' + name.replace(/"/g, '\\"') + ',"room_id":' + room_id + '}';

            ws.send(login_data);
        }

        //服务端发送消息时
        function onmessage(e) {
            console.log(e.data);

            var data = JSON.parse(e.data);

            switch (data['type']) {
                case 'ping':
                    ws.send('{"type":"pong"}')
                    break;
                case 'login':
                    var client_name = data['client_name'];
                    if (data['client_list']) {
                        client_id = data['client_id'];
                        client_name = '你';
                        client_list = data['client_list'];
                    } else {
                        client_list[data['client_id']] = data['client_name'];
                    }

                    say(data['client_id'], $data['client_name'], client_name + ' 加入聊天室', data['time']);

                    flushClientList();
                    break;
                case 'say':
                    say(data['from_client_id'], data['from_client_name'], data['content'], data['time']);
                    break;
                case 'logout':
                    say(data['from_client_id'], data['from_client_name'], data['from_client_name'] + ' 退出了', data['time']);

                    delete client_list[data['from_client_id']];

                    flushClientList();
                    break;
            }
        }

        //发言
        function say(from_client_id, from_client_name, content, time) {
            $("#dialog").append('' +
                '<div class="speech_item">' +
                '<img src="https://cravatar.eu/avatar/' + from_client_name + '/64.png" class="user_icon" />' +
                ' ' + from_client_name + ' <br> ' + time + '' +
                '<div style="clear:both;"></div>' +
                '<p class="triangle-isosceles top">' + content + '</p> ' +
                '</div>'
            );
        }

        //展示提示
        function showPrompt() {
            name = prompt('输入你的名字：', '');
            if (!name || name === 'null') {
                name = '游客';
            }
        }

        //提交对话
        function onSubmit() {
            var input = document.getElementById("textarea");
            var to_client_id = $("#client_list option:selected").attr("value");
            var to_client_name = $("#client_list option:selected").text();
            ws.send('{"type":"say","to_client_id":"' + to_client_id + '","to_client_name":"' + to_client_name + '","content":"' + input.value.replace(/"/, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r') + '"}');
            input.value = "";
            input.focus();
        }

        //刷新用户列表框
        function flushClientList() {
            var userlist_windows = $("#userlist");
            var client_list_select = $("#client_list");
            userlist_windows.empty();
            client_list_select.empty();
            userlist_windows.append('<h4>在线用户</h4><ul>');
            client_list_slelect.append('<option value="all" id="cli_all">所有人</option>');
            for (var p in client_list) {
                userlist_windows.append('<li id="' + p + '">' + client_list[p] + '</li>');
                if (client_id !== p) {
                    client_list_select.append('<option value="' + p + '">' + client_list[p] + '</option>')
                }
            }
            $("#client_list").val(select_client_id);
            userlist_windows.append('</ul>');
        }

        $(function () {
            select_client_id = 'all';
            $("#client_list").change(function () {
                select_client_id = $("#client_list option:selected").attr("value");
            })
        });
    </script>
</head>
<body onload="connect()">
<div class="container">
    <div class="row clearfix">
        <div class="col-md-1 column">
        </div>
        <div class="col-md-6 column">
            <form onsubmit="onSubmit(); return false">
                <select onclose="margin-bottom:8px" id="client_list">
                    <option value="all">所有人</option>
                </select>
                <textarea class="" id=""></textarea>
                <div class="say-btn">
                    <input type="submit" class="btn btn-default" value="发表">
                </div>
                <div>
                    <b>房间列表:</b>（当前在&nbsp;房间
                    <script>document.write(room_id)</script>
                    ）<br>
                    <a href="/?root_id=1">房间1</a> <a href="/?root_id=2">房间2</a> <a href="/?root_id=3">房间3</a> <a
                            href="/?root_id=4">房间4</a>
                </div>
            </form>
        </div>
        <div class="col-md-3 column">
            <div class="thumbnail">
                <div class="caption" id="userlist"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    document.write('<meta name="viewport" content="width=decive-width,initial-scale=1">');

    $("textarea").on("keydown", function (e) {
        // 按enter键自动提交
        if (e.keyCode === 13 && !e.ctrlKey) {
            e.preventDefault();
            $('form').submit();
            return false;
        }

        // 按ctrl+enter键自动提交
        if (e.keyCode === 13 && e.ctrlKey) {
            $(this).value(function (i, val) {
                return val + "\n";
            });
        }
    });
</script>
</body>
</html>