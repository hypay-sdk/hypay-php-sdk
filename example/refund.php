<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" /> 
    <title>好易支付样例-退款</title>
</head>
<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
require_once "../lib/HyPay.Api.php";
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#f00;'>$key</font> : $value <br/>";
    }
}

if(isset($_REQUEST["payOrderId"]) && $_REQUEST["payOrderId"] != ""){
	$payOrderId = $_REQUEST["payOrderId"];
	$amount = $_REQUEST["amount"];
	$input = new HyPayRefund();
	$input->SetPayOrderId($payOrderId);
	$input->SetAmount($amount);
    $input->SetMchRefundNo(HyPayConfig::MCHID.date("YmdHis"));
    $input->SetOp_user_id(HyPayConfig::MCHID);
	printf_info(HyPayApi::refund($input));
	exit();
}

//$_REQUEST["mchOrderNo"]= "122531270220150304194108";
///$_REQUEST["amount"]= "1";
//$_REQUEST["amount"] = "1";
if(isset($_REQUEST["mchOrderNo"]) && $_REQUEST["mchOrderNo"] != ""){
	$mchOrderNo = $_REQUEST["mchOrderNo"];
	$amount = $_REQUEST["amount"];
	$input = new HyPayRefund();
	$input->SetMchOrderNo($mchOrderNo);
	$input->SetAmount($amount);
    $input->SetMchRefundNo(HyPayConfig::MCHID.date("YmdHis"));
    $input->SetOp_user_id(HyPayConfig::MCHID);
	printf_info(HyPayApi::refund($input));
	exit();
}
?>
<body>  
	<form action="#" method="post">
        <div style="margin-left:2%;color:#f00">好易支付订单号和商户订单号选少填一个，好易支付订单号优先：</div><br/>
        <div style="margin-left:2%;">好易支付订单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="payOrderId" /><br /><br />
        <div style="margin-left:2%;">商户订单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="mchOrderNo" /><br /><br />
        <div style="margin-left:2%;">退款金额(分)：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="amount" /><br /><br />
		<div align="center">
			<input type="submit" value="提交退款" style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" />
		</div>
	</form>
</body>
</html>