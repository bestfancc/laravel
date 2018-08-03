<!DOCTYPE html>
<html>
<head>
    <title></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="{{ asset('bootstrap/3.3.4/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bootstrap/3.3.4/css/bootstrap-theme.min.css') }}">
    <script src="{{ asset('jquery/1.11.2/jquery.min.js') }}"></script>
    <script src="{{ asset('bootstrap/3.3.4/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('socket.io/1.3.5/socket.io.js') }}"></script>
</head>
<body>
<div id="box" style="max-width:700px;margin:0 auto;">
    <div class="panel panel-default">
        <div class="panel-heading"><h2>聊天室</h2><span style="color:green;display:none;">(当前在线:<span id="length">0</span>人)</span></div>
        <div class="panel-body" id="body" style="height:400px;overflow-y:auto;">
        </div>
    </div>
    <div class="input-group">
        <input type="text" class="form-control" id="in" placeholder="您想说什么?" aria-describedby="basic-addon2">
        <span class="input-group-addon" id="basic-addon2" style="cursor:pointer;">发送</span>
    </div>
</div>
{{--<div class="modal fade bs-example-modal-sm" data-backdrop="static" id="model" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">--}}
    {{--<div class="modal-dialog modal-sm">--}}
        {{--<div class="modal-content">--}}
            {{--<div class="input-group">--}}
                {{--<input type="text" class="form-control" id="name" placeholder="请输入您的昵称" aria-describedby="basic-addon3">--}}
                {{--<span class="input-group-addon" id="basic-addon3" style="cursor:pointer;">开始聊天</span>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}
{{--</div>--}}
</body>
<script>
    window.onload=function(){
        window.username = 'others';

        /****************/
        var ws;//websocket实例
        var lockReconnect = false;//避免重复连接
        var wsUrl = 'ws://laravel.fancc.top:9502?chatroomId=159753&userId={{$user->id}}';

        function createWebSocket(url) {
            try {
                ws = new WebSocket(url);
                initEventHandle();
            } catch (e) {
                reconnect(url);
            }
        }

        function initEventHandle() {
            ws.onclose = function () {
                reconnect(wsUrl);
            };
            ws.onerror = function () {
                reconnect(wsUrl);
            };
            ws.onopen = function (evt) {
//                console.log("Connected to WebSocket server.");
                var data = {};
                data.msgType = 'login';
                data.msg = '';
                ws.send(JSON.stringify(data));
//            $("#model").modal('show');
                //心跳检测重置
                heartCheck.reset().start();
            };
            ws.onmessage = function (evt) {
//                console.log('Retrieved data from server: ' + evt.data);
                if(evt.data !== '') {
                    $(".panel-body").append(evt.data);
                    //$(".panel-body").append('<p><span style="color:#177bbb">'+evt.data.username+'</span> <span style="color:#aaaaaa">('+evt.data.time+')</span>: '+evt.data.msg+'</p>');
                    var body = document.getElementById("body");
                    body.scrollTop = body.scrollHeight;
                    $("#in").focus();
                }
                //如果获取到消息，心跳检测重置
                //拿到任何消息都说明当前连接是正常的
                heartCheck.reset().start();
            }
        }

        function reconnect(url) {
            if(lockReconnect) return;
            lockReconnect = true;
            //没连接上会一直重连，设置延迟避免请求过多
            setTimeout(function () {
                createWebSocket(url);
                lockReconnect = false;
            }, 2000);
        }


        //心跳检测
        var heartCheck = {
            timeout: 60000,
            timeoutObj: null,
            serverTimeoutObj: null,
            reset: function(){
                clearTimeout(this.timeoutObj);
                clearTimeout(this.serverTimeoutObj);
                return this;
            },
            start: function(){
                var self = this;
                self.timeoutObj = setTimeout(function(){
                    //这里发送一个心跳，后端收到后，返回一个心跳消息，
                    //onmessage拿到返回的心跳就说明连接正常
                    var data = {};
                    data.msgType = 'HeartBeat';
                    data.msg = '';
//                    console.log(JSON.stringify(data));
                    ws.send(JSON.stringify(data));
                    self.serverTimeoutObj = setTimeout(function(){//如果超过一定时间还没重置，说明后端主动断开了
                        ws.close();//如果onclose会执行reconnect，我们执行ws.close()就行了.如果直接执行reconnect 会触发onclose导致重连两次
                    }, self.timeout)
                }, this.timeout)
            }
        }
        //启动websocket
        createWebSocket(wsUrl);

        $("#basic-addon2").click(function(){
            sentMessage();
        });
        document.onkeydown = function(e){
            var ev = document.all ? window.event : e;
            if(ev.keyCode==13) {
                if(window.username) {
                    sentMessage();
                }else{
                    logName();
                }
            }
        }
        function sentMessage() {
            var msg = $("#in").val();
            if(msg) {
                var data = {};
                data.msgType = 'sentMessage';
                data.msg = msg;
                ws.send(JSON.stringify(data));
                $("#in").val('');
            }
        }
    }
</script>
</html>