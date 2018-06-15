<?php
ini_set('date.timezone','Asia/Shanghai');

require_once "../lib/HyPay.Api.php";
require_once 'log.php';

//初始化日志
function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#f00;'>$key</font> : $value <br/>";
    }
}

if(isset($_REQUEST["mchOrderNo"]) && $_REQUEST["mchOrderNo"] != ""){
    $mchOrderNo = $_REQUEST["mchOrderNo"];
    $amount = $_REQUEST["amount"];
    $input = new HyPayUnifiedOrder();
    $input->SetBody("test");
    $input->SetParam1("test");
    $input->SetMchOrderNo($mchOrderNo);
    $input->SetAmount($amount);
    $input->SetCurrency("cny");
    $input->SetChannelId("qrpay_ali");
    $input->SetSubject("sdk测试");
    $order = HyPayApi::createOrder($input);
    printf_info(HyPayApi::createOrder($input));
    exit();
}

$mchOrderNo = HyPayConfig::MCHID.date("YmdHis");
?>


<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>支付宝个人转账支付样例-支付</title>
</head>
<body>
<br/>
<form  method="post">
    <div style="margin-left:2%;">商户订单号：</div><br/>
    <input type="text" style="width:96%;height:35px;margin-left:2%;" name="mchOrderNo" value="<?php echo $mchOrderNo ?>"/><br /><br />
    <div style="margin-left:2%;">订单金额：</div><br/>
    <input type="text" style="width:96%;height:35px;margin-left:2%;" name="amount"  value="1" /><br /><br />
    <div align="center">
        <input type="submit" value="提交支付" style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" />
    </div>
</form>
</body>
</html>