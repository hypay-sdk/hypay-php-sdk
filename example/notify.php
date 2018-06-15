<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once "../lib/HyPay.Api.php";
require_once '../lib/HyPay.Notify.php';
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class PayNotifyCallBack extends HyPayNotify
{
	//查询订单
	public function Queryorder($payOrderId)
	{
		$input = new HyPayOrderQuery();
		$input->SetPayOrderId($payOrderId);
		$result = HyPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("retCode", $result)
			&& array_key_exists("status", $result)
			&& $result["retCode"] == "SUCCESS"
			&& ($result["status"] == 1
                || $result["status"] == 2))
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
		
		if(!array_key_exists("payOrderId", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["payOrderId"])){
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}
}

Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
$notify->Handle();
