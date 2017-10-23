<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>
    </head>
    <body>
        <div id="msg"></div>
        <input type="text" name="" id="text">
        <input type="submit" value="send" onclick="song()">
        <script>
            var msg = document.getElementById('msg');
            var ws = new WebSocket('ws://0.0.0.0:9501')
            ws.onopen = function (event) {
                msg.innerHTML = ws.readyState;
            };
            ws.onmessage = function(e){
                var data = e.data;
                console.log(data);
            };
            function song() {
                var text = document.getElementById('text').value;
                document.getElementById('text').value = '';
                ws.send(text);
            }
        </script>
    </body>
</html>
