<!DOCTYPE html>
<html>
<body>
<h1>获得服务器更新</h1>
<div id="result"></div>

<script>
    if(typeof(EventSource)!=="undefined")
    {
        var source=new EventSource("http://www.basic.com/index.php?r=site/web2");
        source.onmessage=function(event)
        {
            document.getElementById("result").innerHTML+=event.data + "<br />";
        };
    }
    else
    {
        document.getElementById("result").innerHTML="抱歉，您的浏览器不支持 server-sent 事件 ...";
    }
</script>

</body>
</html>
