<?php
require_once "HyPay.Exception.php";
require_once "HyPay.Config.php";
require_once "HyPay.Data.php";

/**
 * 
 * 接口访问类，包含所有好易支付API列表的封装，类中方法为static方法，
 * 每个接口有默认超时时间（除提交被扫支付为10s，上报超时时间为1s外，其他均为6s）
 * @author widyhu
 *
 */
class HyPayApi
{
	/**
	 * 
	 * 统一下单，HyPayUnifiedOrder中mchOrderNo、body、total_fee、channelId必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param HyPayUnifiedOrder $inputObj
	 * @param int $timeOut
	 * @throws HyPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function createOrder($inputObj, $timeOut = 6)
	{
		$url = "http://pay.1080.com/api/pay/create_order";
		//检测必填参数
		if(!$inputObj->IsMchOrderNoSet()) {
			throw new HyPayException("缺少统一支付接口必填参数mchOrderNo！");
		}else if(!$inputObj->IsBodySet()){
			throw new HyPayException("缺少统一支付接口必填参数body！");
		}else if(!$inputObj->IsAmountSet()) {
			throw new HyPayException("缺少统一支付接口必填参数amount！");
		}else if(!$inputObj->IsChannelIdSet()) {
			throw new HyPayException("缺少统一支付接口必填参数channelId！");
		}else if(!$inputObj->IsSubjectSet()) {
            throw new HyPayException("缺少统一支付接口必填参数subject！");
        }
		//关联参数
		if($inputObj->GetChannelId() == "wxpay_jsapi"){
		    if (!$inputObj->IsExtraSet()
                || !json_decode($inputObj->GetExtra())->openid){
                throw new HyPayException("统一支付接口中，缺少必填参数openid！channelId为wxpay_jsapi时，openid为必填参数！");
            }
		}
		else if($inputObj->GetChannelId() == "wxpay_native"){
            if (!$inputObj->IsExtraSet()
                || !json_decode($inputObj->GetExtra())->product_id){
                throw new HyPayException("统一支付接口中，缺少必填参数product_id！channelId为wxpay_native时，product_id为必填参数！");
            }
		}
        else if($inputObj->GetChannelId() == "wxpay_mweb"){
            if (!$inputObj->IsExtraSet()
                || !json_decode($inputObj->GetExtra())->sceneInfo){
                throw new HyPayException("统一支付接口中，缺少必填参数sceneInfo！channelId为wxpay_mweb时，sceneInfo为必填参数！");
            }
        }
		
		$inputObj->SetAppid(HyPayConfig::APPID);//应用账号ID
		$inputObj->SetMchId(HyPayConfig::MCHID);//商户号
		$inputObj->SetClientIp($_SERVER['REMOTE_ADDR']);//终端ip	  
		//$inputObj->SetClientIp("1.1.1.1");  	    
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串
		
		//签名
		$inputObj->SetSign();
		$params = $inputObj->ToParams();
		
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postParamsCurl($params, $url, false, $timeOut);
		$result = HyPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
		
		return $result;
	}
	
	/**
	 * 
	 * 查询订单，HyPayOrderQuery中mchOrderNo、payOrderId至少填一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param HyPayOrderQuery $inputObj
	 * @param int $timeOut
	 * @throws HyPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function orderQuery($inputObj, $timeOut = 6)
	{
		$url = "http://pay.1080.com/api/pay/query_order";
		//检测必填参数
		if(!$inputObj->IsMchOrderNoSet() && !$inputObj->IsPayOrderIdSet()) {
			throw new HyPayException("订单查询接口中，mchOrderNo、payOrderId至少填一个！");
		}
		$inputObj->SetAppid(HyPayConfig::APPID);//应用账号ID
		$inputObj->SetMchId(HyPayConfig::MCHID);//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串
		
		$inputObj->SetSign();//签名
		$params = $inputObj->ToParams();
		
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postParamsCurl($params, $url, false, $timeOut);
		$result = HyPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
		
		return $result;
	}


	/**
	 * 
	 * 申请退款，HyPayRefund中mchOrderNo、payOrderId至少填一个且
	 * mchRefundNo、total_fee、refund_fee、op_user_id为必填参数
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param HyPayRefund $inputObj
	 * @param int $timeOut
	 * @throws HyPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function refund($inputObj, $timeOut = 6)
	{
		$url = "http://pay.1080.com/api/refund/create_order";
		//检测必填参数
		if(!$inputObj->IsMchOrderNoSet() && !$inputObj->IsPayOrderIdSet()) {
			throw new HyPayException("退款申请接口中，mchOrderNo、payOrderId至少填一个！");
		}else if(!$inputObj->IsMchRefundNoSet()){
			throw new HyPayException("退款申请接口中，缺少必填参数mchRefundNo！");
		}else if(!$inputObj->IsAmountSet()){
			throw new HyPayException("退款申请接口中，缺少必填参数amount！");
		}
		$inputObj->SetAppid(HyPayConfig::APPID);//应用账号ID
		$inputObj->SetMchId(HyPayConfig::MCHID);//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串
		
		$inputObj->SetSign();//签名
		$params = $inputObj->ToParams();
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postParamsCurl($params, $url, true, $timeOut);
		$result = HyPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
		
		return $result;
	}
	
	/**
	 * 
	 * 查询退款
	 * 提交退款申请后，通过调用该接口查询退款状态。退款有一定延时，
	 * 用零钱支付的退款20分钟内到账，银行卡支付的退款3个工作日后重新查询退款状态。
	 * HyPayRefundQuery中mchRefundNo、mchOrderNo、payOrderId、refundOrderId四个参数必填一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param HyPayRefundQuery $inputObj
	 * @param int $timeOut
	 * @throws HyPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function refundQuery($inputObj, $timeOut = 6)
	{
		$url = "http://pay.1080.com/api/refund/query_order";
		//检测必填参数
		if(!$inputObj->IsMchRefundNoSet() &&
			!$inputObj->IsMchOrderNoSet() &&
			!$inputObj->IsPayOrderIdSet() &&
			!$inputObj->IsRefundOrderIdSet()) {
			throw new HyPayException("退款查询接口中，mchRefundNo、mchOrderNo、payOrderId、refundOrderId四个参数必填一个！");
		}
		$inputObj->SetAppid(HyPayConfig::APPID);//应用账号ID
		$inputObj->SetMchId(HyPayConfig::MCHID);//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串
		
		$inputObj->SetSign();//签名
		$params = $inputObj->ToParams();
		
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postParamsCurl($params, $url, false, $timeOut);
		$result = HyPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
		
		return $result;
	}

    /**
     * 下载对账单，WxPayDownloadBill中bill_date为必填参数
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param HyPayDownloadBill $inputObj
     * @param int $timeOut
     * @throws HyPayException
     * @return 成功时返回，其他抛异常
     */
    public static function downloadBill($inputObj, $timeOut = 6)
    {
        $url = "http://pay.1080.com/api/pay/downloadbill";
        //检测必填参数
        if(!$inputObj->IsBill_dateSet()) {
            throw new HyPayException("对账单接口中，缺少必填参数bill_date！");
        }
        $inputObj->SetAppid(HyPayConfig::APPID);//公众账号ID
        $inputObj->SetMchId(HyPayConfig::MCHID);//商户号
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        $inputObj->SetSign();//签名
        $xml = $inputObj->ToParams();

        $response = self::postParamsCurl($xml, $url, false, $timeOut);
        if(substr($response, 0 , 5) == "<xml>"){
            return "";
        }
        return $response;
    }
	

 	/**
 	 * 
 	 * 支付结果通用通知
 	 * @param function $callback
 	 * 直接回调函数使用方法: notify(you_function);
 	 * 回调类成员函数方法:notify(array($this, you_function));
 	 * $callback  原型为：function function_name($data){}
 	 */
	public static function notify($callback, &$msg)
	{
		//获取通知的数据
        try {

            $result = HyPayResults::InitFromArray($_GET);
        } catch (HyPayException $e){
            $msg = $e->errorMessage();
            return false;
        }

		return call_user_func($callback, $result);
	}

	/**
	 * 
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return 产生的随机字符串
	 */
	public static function getNonceStr($length = 32) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		} 
		return $str;
	}
	
	/**
	 * 直接输出xml
	 * @param string $params
	 */
	public static function replyNotify($params)
	{
		echo $params;
	}
	


	/**
	 * 以post方式提交xml到对应的接口url
	 * 
	 * @param string $params  需要post的xml数据
	 * @param string $url  url
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 * @throws HyPayException
	 */
	private static function postParamsCurl($params, $url, $useCert = false, $second = 30)
	{		
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		
		//如果有配置代理这里就设置代理
		if(HyPayConfig::CURL_PROXY_HOST != "0.0.0.0" 
			&& HyPayConfig::CURL_PROXY_PORT != 0){
			curl_setopt($ch,CURLOPT_PROXY, HyPayConfig::CURL_PROXY_HOST);
			curl_setopt($ch,CURLOPT_PROXYPORT, HyPayConfig::CURL_PROXY_PORT);
		}
		curl_setopt($ch,CURLOPT_URL, $url);
//		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
//		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	
		if($useCert == true){
			//设置证书
			//使用证书：cert 与 key 分别属于两个.pem文件
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, HyPayConfig::SSLCERT_PATH);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, HyPayConfig::SSLKEY_PATH);
		}
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		} else { 
			$error = curl_errno($ch);
			curl_close($ch);
			throw new HyPayException("curl出错，错误码:$error");
		}
	}
	
	/**
	 * 获取毫秒级别的时间戳
	 */
	private static function getMillisecond()
	{
		//获取毫秒的时间戳
		$time = explode ( " ", microtime () );
		$time = $time[1] . ($time[0] * 1000);
		$time2 = explode( ".", $time );
		$time = $time2[0];
		return $time;
	}

    /**
     *
     * 上报数据， 上报的时候将屏蔽所有异常流程
     * @param string $usrl
     * @param int $startTimeStamp
     * @param array $data
     */
    private static function reportCostTime($url, $startTimeStamp, $data)
    {
        return;
    }
}

