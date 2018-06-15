<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" /> 
    <title>好易支付样例-查退款单</title>
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
	$input = new HyPayRefundQuery();
	$input->SetPayOrderId($payOrderId);
	printf_info(HyPayApi::refundQuery($input));
}

if(isset($_REQUEST["mchOrderNo"]) && $_REQUEST["mchOrderNo"] != ""){
	$mchOrderNo = $_REQUEST["mchOrderNo"];
	$input = new HyPayRefundQuery();
	$input->SetMchOrderNo($mchOrderNo);
	printf_info(HyPayApi::refundQuery($input));
	exit();
}

if(isset($_REQUEST["mchRefundNo"]) && $_REQUEST["mchRefundNo"] != ""){
	$mchRefundNo = $_REQUEST["mchRefundNo"];
	$input = new HyPayRefundQuery();
	$input->SetMchRefundNo($mchRefundNo);
	printf_info(HyPayApi::refundQuery($input));
	exit();
}

if(isset($_REQUEST["refundOrderId"]) && $_REQUEST["refundOrderId"] != ""){
	$refundOrderId = $_REQUEST["refundOrderId"];
	$input = new HyPayRefundQuery();
	$input->SetRefundOrderId($refundOrderId);
	printf_info(HyPayApi::refundQuery($input));
	exit();
}
	
?>
<body>  
	<form action="#" method="post">
        <div style="margin-left:2%;color:#f00">好易支付订单号、商户订单号、商户退款单号、好易支付退款单号选填至少一个，好易支付退款单号优先：</div><br/>
        <div style="margin-left:2%;">好易支付订单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="payOrderId" /><br /><br />
        <div style="margin-left:2%;">商户订单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="mchOrderNo" /><br /><br />
        <div style="margin-left:2%;">商户退款单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="mchRefundNo" /><br /><br />
        <div style="margin-left:2%;">好易支付退款单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="refundOrderId" /><br /><br />
		<div align="center">
			<input type="submit" value="查询" style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" />
		</div>
	</form>
</body>
</html>