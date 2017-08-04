<form id="send_hptoken" action="https://test.authorize.net/payment/payment" method="post" target="load_payment" >
    <input type="hidden" name="token" value="<?php echo $token ?>" />
    <button type="submit">我要升级</button>
</form>

<iframe id="load_payment" class="embed-responsive-item" name="load_payment" width="100%" height="850px" frameborder="0" scrolling="no">
</iframe>