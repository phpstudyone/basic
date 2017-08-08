<body onload="func()">
<form method="post" action="https://test.authorize.net/customer/addPayment" target="add_payment">
    <input type="hidden" name="token" value="<?php echo $token;?>"/>
    <input id='submit' type="submit" value="添加支付信息"/>
</form>
<iframe id="add_payment" class="embed-responsive-item panel" name="add_payment" width="100%" height="650px" frameborder="0" scrolling="no">
</iframe>
</body>
<script type="application/javascript">
    function func(){
        var button = document.getElementById('submit');
        button.click();
    }
</script>