<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            uid:<br>
            <input type="text" id="uid" value="{{request('uid')}}">
            <br>
            说了句:<br>
            <input type="text" id="talk" value="你好！">
            <br><br>
            <button id="send">发送</button>
        </div>
    </body>
</html>
<script src="jquery.min.js"></script>
<script>
    var ws = new WebSocket("ws://47.107.242.203:9501");
    ws.onopen = function(evt) {
        console.log("连接成功");
        var uid = $("#uid").val();
        ws.send("uid_"+uid)
    };
    ws.onerror = function(evt, e) {
        console.log('Error occured: ' + evt.data);
    };
    ws.onmessage = function(evt) {
        console.log("收到服务端的消息：" + evt.data);
    };

    $("#send").on('click',function(){
        var talk = $("#talk").val();
        ws.send(talk);
    });
</script>