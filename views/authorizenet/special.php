<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>IFrame Communicator</title>
    <script type="text/javascript">

        function callParentFunction(str) {
            var referrer = document.referrer;
            var s = {qstr : str , parent : referrer};
            console.log(s);
        }

        function receiveMessage(event) {
            if (event && event.data) {
                callParentFunction(event.data);
            }
        }

        if (window.addEventListener) {
            window.addEventListener("message", receiveMessage, false);
        } else if (window.attachEvent) {
            window.attachEvent("onmessage", receiveMessage);
        }

        if (window.location.hash && window.location.hash.length > 1) {
            callParentFunction(window.location.hash.substring(1));
        }

    </script>
</head>
<body>
</body>
</html>
