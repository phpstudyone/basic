<!--<script type="text/javascript" src="js/Accept.js" charset="utf-8"></script>-->
<script type="text/javascript" src="https://jstest.authorize.net/v1/Accept.js" charset="utf-8"></script>
<form>
    Card Number<br>
    <input type="tel" id="cardNumberID" placeholder="5424000000000015" autocomplete="off" /><br><br>
    Expiration Date (Month)<br>
    <input type="text" id="monthID" placeholder="12" value="12" /><br><br>
    Expiration Date (Year)<br>
    <input type="text" id="yearID" placeholder="2025" value="2025" /><br><br>
    Card Security Code<br>
    <input type="text" id="cardCodeID" placeholder="123" /><br><br>
    Amount<br>
    <input type="text" id="amount" placeholder="10.00" /><br><br>
    <button type="button" id="submitButton" onclick="sendPaymentDataToAnet()">Pay</button>
</form>
<script type="text/javascript">
    function sendPaymentDataToAnet() {
        var secureData = {}; authData = {}; cardData = {};
        cardData.cardNumber = document.getElementById("cardNumberID").value;
        cardData.month = document.getElementById("monthID").value;
        cardData.year = document.getElementById("yearID").value;
        cardData.cardCode = document.getElementById("cardCodeID").value;
        secureData.cardData = cardData;
        authData.clientKey = "5AS5b3Z4TuX84y83Kg2cBzJCNwk3wX9jeR3x84Tu4pJCN86gmKbJaqhvK3ejNZLE";
        authData.apiLoginID = "3ceL67Gjg";
        secureData.authData = authData;
        Accept.dispatchData(secureData, responseHandler);
        function responseHandler(response) {
            if (response.messages.resultCode === "Error") {
                for (var i = 0; i < response.messages.message.length; i++) {
                    console.log(response.messages.message[i].code + ": " + response.messages.message[i].text);
                }
                alert("acceptJS library error!")
            } else {
                console.log(response.opaqueData.dataDescriptor);
                console.log(response.opaqueData.dataValue);
                processTransaction(response.opaqueData);
            }
        }
    }
</script>
<script type="text/javascript">
    function processTransaction(responseData) {
        var transactionForm = document.createElement("form");
        transactionForm.Id = "transactionForm";
        transactionForm.action = "/index.php?r=authorizenet/paymentprocessor";
        transactionForm.method = "POST";
        document.body.appendChild(transactionForm);
        amount = document.createElement("input");
        amount.hidden = true;
        amount.value = document.getElementById('amount').value;
        amount.name = "amount";
        transactionForm.appendChild(amount);
        dataDesc = document.createElement("input");
        dataDesc.hidden = true;
        dataDesc.value = responseData.dataDescriptor;
        dataDesc.name = "dataDesc";
        transactionForm.appendChild(dataDesc);
        dataValue = document.createElement("input");
        dataValue.hidden = true;
        dataValue.value = responseData.dataValue;
        dataValue.name = "dataValue";
        transactionForm.appendChild(dataValue);
        transactionForm.submit();
    }
</script>