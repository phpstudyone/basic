<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>IFrame Communicator</title>
    <script type="text/javascript">

        function callParentFunction(str) {
            var referrer = document.referrer;
            var s = {qstr : str , parent : referrer};
            console.log(s);
            if(referrer == 'https://test.authorize.net/customer/addPayment'){
                switch(str){
                    case 'action=successfulSave' :
                        window.parent.parent.location.href="https://www.basic.com/authorizenet/payment";
                        break;
                }
            }else if(referrer == 'https://test.authorize.net/payment/payment'){

            }
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
            console.log(window.location.hash.substring(1),11111);
            callParentFunction(window.location.hash.substring(1));
        }

    </script>
</head>
<body>
</body>
</html>
