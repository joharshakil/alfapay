<?php
error_reporting(0);

if($_GET['s'] == "1" || $_GET['success'] != "false"){
$data = unserialize($_COOKIE['getcookieValues']);

$auth = $_GET['AuthToken'];
$Key1 = $data['key1'];
$Key2 = $data['key2'];
$returnURL = $data['return_url'];
$channel_id = $data['HS_ChannelId'];
$merchant_id = $data['HS_MerchantId'];
$store_id = $data['HS_StoreId'];
$merchan_hash = $data['HS_MerchantHash'];
$merchant_username = $data['HS_MerchantUsername'];
$merchant_password = $data['HS_MerchantPassword'];
$order_id = $data['TransactionReferenceNumber'];
$order_amount = $data['TransactionAmount'];
$transectionTypeId =  $data['TransactionTypeId'];
?>
<html style="position: relative;width: 100%;height: 100%;">
<body class="loader">

<style>
    .loader{
        position: relative;
    }
    .loader::after {
        height: 300px;
        width: 300px;
        position: absolute;
        top: 35%;
        left: 0%;
        right: 0%;
        margin: auto;
        text-align: center;
        display: block;
        content: "";
        -webkit-animation: none;
        -moz-animation: none;
        animation: none;
        background: url('https://netbanking.bankalfalah.com/ib/assets/images/alfa-loader.gif') center center;
        background-repeat: no-repeat;
        line-height: 1;
        font-size: 2em;
        z-index: 9999;
        margin: auto;
    }
</style>
<script
        src="https://code.jquery.com/jquery-1.12.4.min.js"
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
        crossorigin="anonymous"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/rollups/aes.js"></script>

<?php echo "<input id='Key1' name='Key1' type='hidden'  value='".$Key1."'>"; ?>
<?php echo "<input id='Key2' name='Key2' type='hidden'  value='".$Key2."'>"; ?>

<?php


$HS_ChannelId= $channel_id;
$HS_MerchantId= $merchant_id;
$HS_StoreId= $store_id;
$HS_MerchantHash=  $merchan_hash;
$HS_MerchantUsername=  $merchant_username;
$HS_MerchantPassword= $merchant_password;


$mapString =
    "HS_ChannelId=$HS_ChannelId"
    . "&HS_MerchantId=$HS_MerchantId"
    . "&HS_StoreId=$HS_StoreId"
    . "&HS_ReturnURL=$returnURL"
    . "&HS_MerchantHash=$HS_MerchantHash"
    . "&HS_MerchantUsername=$HS_MerchantUsername"
    . "&HS_MerchantPassword=$HS_MerchantPassword";


$cipher="aes-128-cbc";
$cipher_text = openssl_encrypt($mapString, $cipher, $Key1,   OPENSSL_RAW_DATA , $Key2);
$hash1 =  base64_encode($cipher_text); ?>

<?php if($_GET['AuthToken'] == "" ) { ?>

    <form action="https://payments.bankalfalah.com/HS/HS/HS" id="HandshakeForm" method="post">
        <?php echo "<input id='HS_RequestHash' name='HS_RequestHash' type='hidden'  value='".$hash1."'>"; ?>
        <input id="HS_IsRedirectionRequest" name="HS_IsRedirectionRequest" type="hidden" value="1">
        <?php echo "<input id='HS_ChannelId' name='HS_ChannelId' type='hidden'  value='".$HS_ChannelId."'>"; ?>
        <?php echo "<input id='HS_ReturnURL' name='HS_ReturnURL' type='hidden'  value='".$returnURL."'>"; ?>
        <?php echo "<input id='HS_MerchantId' name='HS_MerchantId' type='hidden'  value='".$HS_MerchantId."'>"; ?>
        <?php echo "<input id='HS_StoreId' name='HS_StoreId' type='hidden'  value='".$HS_StoreId."'>"; ?>
        <?php echo "<input id='HS_MerchantHash' name='HS_MerchantHash' type='hidden'  value='".$HS_MerchantHash."'>"; ?>
        <?php echo "<input id='HS_MerchantUsername' name='HS_MerchantUsername' type='hidden'  value='".$HS_MerchantUsername."'>"; ?>
        <?php echo "<input id='HS_MerchantPassword' name='HS_MerchantPassword' type='hidden'  value='".$HS_MerchantPassword."'>"; ?>
        <?php echo "<input id='HS_TransactionReferenceNumber' name='HS_TransactionReferenceNumber' type='hidden'   value='".$order_id."'>"; ?>
        <button type="submit" class="btn btn-custon-four btn-danger" id="handshake" style="display: none;">Handshake</button>
    </form>

    <script  type="text/javascript">
        setTimeout(function(){
            jQuery("#handshake").click();
        },500);


        $("#handshake").click(function (e) {
            e.preventDefault();
            $("#handshake").attr('disabled', 'disabled');
            submitRequest("HandshakeForm");
            if ($("#HS_IsRedirectionRequest").val() == "1") {
                document.getElementById("HandshakeForm").submit();
            }
            else {
                var myData = {
                    HS_MerchantId : $("#HS_MerchantId").val(),
                    HS_StoreId : $("#HS_StoreId").val(),
                    HS_MerchantHash : $("#HS_MerchantHash").val(),
                    HS_MerchantUsername : $("#HS_MerchantUsername").val(),
                    HS_MerchantPassword : $("#HS_MerchantPassword").val(),
                    HS_IsRedirectionRequest : $("#HS_IsRedirectionRequest").val(),
                    HS_ReturnURL : $("#HS_ReturnURL").val(),
                    HS_RequestHash : $("#HS_RequestHash").val(),
                    HS_ChannelId: $("#HS_ChannelId").val(),
                    HS_TransactionReferenceNumber: $("#HS_TransactionReferenceNumber").val(),
                }


                $.ajax({
                    type: 'POST',
                    url: 'https://payments.bankalfalah.com/HS/HS/HS',
                    contentType: "application/x-www-form-urlencoded",
                    data: myData,
                    dataType: "json",
                    beforeSend: function () {
                    },
                    success: function (r) {
                        if (r != '') {
                            if (r.success == "true") {
                                $("#AuthToken").val(r.AuthToken);
                                $("#ReturnURL").val(r.ReturnURL);
                                alert('Success: Handshake Successful');
                            }
                            else
                            {
                                alert('Error: Handshake Unsuccessful');
                            }
                        }
                        else
                        {
                            alert('Error: Handshake Unsuccessful');
                        }
                    },
                    error: function (error) {
                        alert('Error: An error occurred');
                    },
                    complete: function(data) {
                        $("#handshake").removeAttr('disabled', 'disabled');
                    }
                });
            }

        });

    </script>
<?php  } ?>



<?php


if($auth){

    $HS_ChannelId = $channel_id;
    $HS_MerchantId = $merchant_id;
    $HS_StoreId = $store_id;
    $HS_MerchantHash = $merchan_hash;
    $HS_MerchantUsername =  $merchant_username;
    $HS_MerchantPassword = $merchant_password;
    $ReturnURL = "https://homage.pk/thank-you";
    $RequestHash=$hash1;
    $Currency="PKR";
    $AuthToken=$auth;
    $mapString2 =
        "ChannelId=$HS_ChannelId"
        . "&MerchantId=$HS_MerchantId"
        . "&StoreId=$HS_StoreId"
        . "&ReturnURL=$ReturnURL"
        . "&MerchantHash=$HS_MerchantHash"
        . "&MerchantUsername=$HS_MerchantUsername"
        . "&MerchantPassword=$HS_MerchantPassword"
        . "&RequestHash=$hash1"
        . "&Currency=$Currency"
        . "&AuthToken=$AuthToken";
    $cipher="aes-128-cbc";
    $cipher_text2 = openssl_encrypt($mapString2, $cipher, $Key1,   OPENSSL_RAW_DATA , $Key2);
    $hash2 =  base64_encode($cipher_text2);


    ?>

    <form action="https://payments.bankalfalah.com/SSO/SSO/SSO" id="PageRedirectionForm" method="post" novalidate="novalidate">
        <?php echo "<input id='AuthToken' name='AuthToken' type='hidden'  value='".$auth."'>"; ?>
        <?php echo "<input id='RequestHash' name='RequestHash' type='hidden'  value='".$hash2."'>"; ?>
        <?php echo "<input id='ChannelId' name='ChannelId' type='hidden'  value='".$HS_ChannelId."'>"; ?>
        <input id="Currency" name="Currency" type="hidden" value="PKR">
        <input id="ReturnURL" name="ReturnURL" type="hidden" value="https://homage.pk/thank-you">
        <?php echo "<input id='MerchantId' name='MerchantId' type='hidden'  value='".$HS_MerchantId."'>"; ?>
        <?php echo "<input id='StoreId' name='StoreId' type='hidden'  value='".$HS_StoreId."'>"; ?>
        <?php echo "<input id='MerchantHash' name='MerchantHash' type='hidden'  value='".$HS_MerchantHash."'>"; ?>
        <?php echo "<input id='MerchantUsername' name='MerchantUsername' type='hidden'  value='".$HS_MerchantUsername."'>"; ?>
        <?php echo "<input id='MerchantPassword' name='MerchantPassword' type='hidden'  value='".$HS_MerchantPassword."'>"; ?>
        <?php echo "<input id='TransactionTypeId' name='TransactionTypeId' type='hidden'  value='".$transectionTypeId."'>"; ?>
        <?php echo "<input  id='TransactionReferenceNumber' name='TransactionReferenceNumber' type='hidden'  value='".$order_id."'>"; ?>
        <?php echo "<input  id='TransactionAmount' name='TransactionAmount'  type='hidden' value='".$order_amount."'>"; ?>
        <button type="submit" class="btn btn-custon-four btn-danger" id="run" style="display: none">RUN</button>
    </form>

    <script type="text/javascript">
        /*   document.getElementById('PageRedirectionForm').submit(); // SUBMIT FORM*/
        setTimeout(function(){
            jQuery("#run").click();
        },1000);

    </script>

<?php }

?>

<script
        src="https://code.jquery.com/jquery-1.12.4.min.js"
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
        crossorigin="anonymous"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/rollups/aes.js"></script>




<script type="text/javascript">
    $(function () {
        $("#run").click(function (e) {
            e.preventDefault();
            submitRequest("PageRedirectionForm");
            document.getElementById("PageRedirectionForm").submit();
        });
    });

    function submitRequest(formName) {

        var mapString = '', hashName = 'RequestHash';
        if (formName == "HandshakeForm") {
            hashName = 'HS_' + hashName;
        }

        $("#" + formName+" :input").each(function () {
            if ($(this).attr('id') != '') {
                mapString += $(this).attr('id') + '=' + $(this).val() + '&';
            }
        });

        $("#" + hashName).val(CryptoJS.AES.encrypt(CryptoJS.enc.Utf8.parse(mapString.substr(0, mapString.length - 1)), CryptoJS.enc.Utf8.parse($("#Key1").val()),
            {
                keySize: 128 / 8,
                iv: CryptoJS.enc.Utf8.parse($("#Key2").val()),
                mode: CryptoJS.mode.CBC,
                padding: CryptoJS.pad.Pkcs7
            }));
    }
  window.onbeforeunload = () => {}
</script>




<?php } else { ?>
<script type="text/javascript">
    window.location.href = "https://merchants.bankalfalah.com/PaymentsProd/PaymentsProd/DeclinePayment/";
    <?php
    }
    ?>


    <script>


</script>
</body>
</html>