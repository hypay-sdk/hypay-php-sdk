<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" /> 
    <title>好易支付样例-订单查询</title>
</head>
<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
require_once "../lib/HyPay.Api.php";
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("./logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#f00;'>$key</font> : $value <br/>";
    }
}


if(isset($_REQUEST["payOrderId"]) && $_REQUEST["payOrderId"] != ""){
	$payOrderId = $_REQUEST["payOrderId"];
	$input = new HyPayOrderQuery();
	$input->SetPayOrderId($payOrderId);
	printf_info(HyPayApi::orderQuery($input));
	exit();
}

if(isset($_REQUEST["mchOrderNo"]) && $_REQUEST["mchOrderNo"] != ""){
	$mchOrderNo = $_REQUEST["mchOrderNo"];
	$input = new HyPayOrderQuery();
	$input->SetMchOrderNo($mchOrderNo);
	printf_info(HyPayApi::orderQuery($input));
	exit();
}
?>
<body>  
	<form action="#" method="post">
        <div style="margin-left:2%;color:#f00">好易支付订单号和商户订单号选少填一个，订单号优先：</div><br/>
        <div style="margin-left:2%;">好易支付订单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="payOrderId" /><br /><br />
        <div style="margin-left:2%;">商户订单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="mchOrderNo" /><br /><br />
		<div align="center">
			<input type="submit" value="查询" style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" />
		</div>
	</form>
</body>
</html>