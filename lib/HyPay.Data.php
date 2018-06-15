<?php
/**
* 2018-06-11 新建
**/
require_once "HyPay.Config.php";
require_once "HyPay.Exception.php";

/**
 * 
 * 数据对象基础类，该类中定义数据类最基本的行为，包括：
 * 计算/设置/获取签名、输出Form格式的参数、从Json字符串中读取数据对象等
 *
 */
class HyPayDataBase
{
	protected $values = array();
	
	/**
	* 设置签名，详见签名生成算法
	* @param string $value 
	**/
	public function SetSign()
	{
		$sign = $this->MakeSign();
		$this->values['sign'] = $sign;
		return $sign;
	}
	
	/**
	* 获取签名，详见签名生成算法的值
	* @return 值
	**/
	public function GetSign()
	{
		return $this->values['sign'];
	}
	
	/**
	* 判断签名，详见签名生成算法是否存在
	* @return true 或 false
	**/
	public function IsSignSet()
	{
		return array_key_exists('sign', $this->values);
	}

	/**
	 * 输出xml字符
	 * @throws HyPayException
	**/
	public function ToParams()
	{
		if(!is_array($this->values) 
			|| count($this->values) <= 0)
		{
    		throw new HyPayException("数组数据异常！");
    	}

    	$params = "params=".urlencode(json_encode($this->values));
        return $params;
	}
	/**
     * 将json转为array
     * @param string $params
     * @throws HyPayException
     */
	public function FromJson($params)
	{	
		if(!$params){
			throw new HyPayException("xml数据异常！");
		}
        $this->values = json_decode($params, true);
		return $this->values;
	}
	
	/**
	 * 格式化参数格式化成url参数
	 */
	public function ToUrlParams()
	{
		$buff = "";
		foreach ($this->values as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}
	
	/**
	 * 生成签名
	 * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
	 */
	public function MakeSign()
	{
		//签名步骤一：按字典序排序参数
		ksort($this->values);
		$string = $this->ToUrlParams();
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=".HyPayConfig::KEY;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}
	
	/**
	 * 获取设置的值
	 */
	public function GetValues()
	{
		return $this->values;
	}
}

/**
 * 
 * 接口调用结果类
 * @author widyhu
 *
 */
class HyPayResults extends HyPayDataBase
{
	/**
	 * 
	 * 检测签名
	 */
	public function CheckSign()
	{
		//fix异常
		if(!$this->IsSignSet()){
			throw new HyPayException("签名错误！");
		}
		
		$sign = $this->MakeSign();
		if($this->GetSign() == $sign){
			return true;
		}
		throw new HyPayException("签名错误！");
	}
	
	/**
	 * 
	 * 使用数组初始化
	 * @param array $array
	 */
	public function FromArray($array)
	{
		$this->values = $array;
	}
	
	/**
	 * 
	 * 使用数组初始化对象
	 * @param array $array
	 * @param 是否检测签名 $noCheckSign
	 */
	public static function InitFromArray($array, $noCheckSign = false)
	{
		$obj = new self();
		$obj->FromArray($array);
		if($noCheckSign == false){
			$obj->CheckSign();
		}
        return $obj->GetValues();
	}
	
	/**
	 * 
	 * 设置参数
	 * @param string $key
	 * @param string $value
	 */
	public function SetData($key, $value)
	{
		$this->values[$key] = $value;
	}
	
    /**
     * 将xml转为array
     * @param array $params
     * @throws HyPayException
     */
	public static function Init($params)
	{	
		$obj = new self();

		$obj->FromJson($params);
		//fix bug 2015-06-29
		if($obj->values['retCode'] != 'SUCCESS'){
			 return $obj->GetValues();
		}
		$obj->CheckSign();
        return $obj->GetValues();
	}
}

/**
 * 
 * 回调基础类
 *
 */
class HyPayNotifyReply extends  HyPayDataBase
{
	/**
	 * 
	 * 设置错误码 FAIL 或者 SUCCESS
	 * @param string
	 */
	public function SetReturn_code($return_code)
	{
		$this->values['return_code'] = $return_code;
	}
	
	/**
	 * 
	 * 获取错误码 FAIL 或者 SUCCESS
	 * @return string $return_code
	 */
	public function GetReturn_code()
	{
		return $this->values['return_code'];
	}

	/**
	 * 
	 * 设置错误信息
	 * @param string $return_code
	 */
	public function SetReturn_msg($return_msg)
	{
		$this->values['return_msg'] = $return_msg;
	}
	
	/**
	 * 
	 * 获取错误信息
	 * @return string
	 */
	public function GetReturn_msg()
	{
		return $this->values['return_msg'];
	}
	
	/**
	 * 
	 * 设置返回参数
	 * @param string $key
	 * @param string $value
	 */
	public function SetData($key, $value)
	{
		$this->values[$key] = $value;
	}
}

/**
 * 
 * 统一下单输入对象
 * @author widyhu
 *
 */
class HyPayUnifiedOrder extends HyPayDataBase
{	
	/**
	* 设置好易分配的应用账号ID
	* @param string $value 
	**/
	public function SetAppId($value)
	{
		$this->values['appId'] = $value;
	}
	/**
	* 获取好易分配的应用账号ID的值
	* @return 值
	**/
	public function GetAppId()
	{
		return $this->values['appId'];
	}
	/**
	* 判断好易分配的应用账号ID是否存在
	* @return true 或 false
	**/
	public function IsAppIdSet()
	{
		return array_key_exists('appId', $this->values);
	}


	/**
	* 设置好易支付分配的商户号
	* @param string $value 
	**/
	public function SetMchId($value)
	{
		$this->values['mchId'] = $value;
	}
	/**
	* 获取好易支付分配的商户号的值
	* @return 值
	**/
	public function GetMchId()
	{
		return $this->values['mchId'];
	}
	/**
	* 判断好易支付分配的商户号是否存在
	* @return true 或 false
	**/
	public function IsMchIdSet()
	{
		return array_key_exists('mchId', $this->values);
	}


	/**
	* 设置好易支付分配的终端设备号，商户自定义
	* @param string $value 
	**/
	public function SetDevice($value)
	{
		$this->values['device'] = $value;
	}
	/**
	* 获取好易支付分配的终端设备号，商户自定义的值
	* @return 值
	**/
	public function GetDevice()
	{
		return $this->values['device'];
	}
	/**
	* 判断好易支付分配的终端设备号，商户自定义是否存在
	* @return true 或 false
	**/
	public function IsDeviceSet()
	{
		return array_key_exists('device', $this->values);
	}


	/**
	* 设置随机字符串，不长于32位。推荐随机数生成算法
	* @param string $value 
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* 获取随机字符串，不长于32位。推荐随机数生成算法的值
	* @return 值
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* 判断随机字符串，不长于32位。推荐随机数生成算法是否存在
	* @return true 或 false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}

	/**
	* 设置商品或支付单简要描述
	* @param string $value 
	**/
	public function SetBody($value)
	{
		$this->values['body'] = $value;
	}
	/**
	* 获取商品或支付单简要描述的值
	* @return 值
	**/
	public function GetBody()
	{
		return $this->values['body'];
	}
	/**
	* 判断商品或支付单简要描述是否存在
	* @return true 或 false
	**/
	public function IsBodySet()
	{
		return array_key_exists('body', $this->values);
	}


	/**
	* 设置商品名称明细列表
	* @param string $value 
	**/
	public function SetSubject($value)
	{
		$this->values['subject'] = $value;
	}
	/**
	* 获取商品名称明细列表的值
	* @return 值
	**/
	public function GetSubject()
	{
		return $this->values['subject'];
	}
	/**
	* 判断商品名称明细列表是否存在
	* @return true 或 false
	**/
	public function IsSubjectSet()
	{
		return array_key_exists('subject', $this->values);
	}


	/**
	* 设置附加数据1，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
	* @param string $value 
	**/
	public function SetParam1($value)
	{
		$this->values['param1'] = $value;
	}
	/**
	* 获取附加数据1，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据的值
	* @return 值
	**/
	public function GetParam1()
	{
		return $this->values['param1'];
	}
	/**
	* 判断附加数据1，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据是否存在
	* @return true 或 false
	**/
	public function IsParam1Set()
	{
		return array_key_exists('param1', $this->values);
	}

    /**
     * 设置附加数据2，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
     * @param string $value
     **/
    public function SetParam2($value)
    {
        $this->values['param2'] = $value;
    }
    /**
     * 获取附加数据2，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据的值
     * @return 值
     **/
    public function GetParam2()
    {
        return $this->values['param2'];
    }
    /**
     * 判断附加数据2，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据是否存在
     * @return true 或 false
     **/
    public function IsParam2Set()
    {
        return array_key_exists('param2', $this->values);
    }


	/**
	* 设置商户系统内部的订单号,64个字符内、可包含字母, 其他说明见商户订单号
	* @param string $value 
	**/
	public function SetMchOrderNo($value)
	{
		$this->values['mchOrderNo'] = $value;
	}
	/**
	* 获取商户系统内部的订单号,64个字符内、可包含字母, 其他说明见商户订单号的值
	* @return 值
	**/
	public function GetMchOrderNo()
	{
		return $this->values['mchOrderNo'];
	}
	/**
	* 判断商户系统内部的订单号,64个字符内、可包含字母, 其他说明见商户订单号是否存在
	* @return true 或 false
	**/
	public function IsMchOrderNoSet()
	{
		return array_key_exists('mchOrderNo', $this->values);
	}


	/**
	* 设置符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型
	* @param string $value 
	**/
	public function SetCurrency($value)
	{
		$this->values['currency'] = $value;
	}
	/**
	* 获取符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型的值
	* @return 值
	**/
	public function GetCurrency()
	{
		return $this->values['currency'];
	}
	/**
	* 判断符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型是否存在
	* @return true 或 false
	**/
	public function IsCurrencySet()
	{
		return array_key_exists('currency', $this->values);
	}


	/**
	* 设置订单总金额，只能为整数，详见支付金额
	* @param string $value 
	**/
	public function SetAmount($value)
	{
		$this->values['amount'] = $value;
	}
	/**
	* 获取订单总金额，只能为整数，详见支付金额的值
	* @return 值
	**/
	public function GetAmount()
	{
		return $this->values['amount'];
	}
	/**
	* 判断订单总金额，只能为整数，详见支付金额是否存在
	* @return true 或 false
	**/
	public function IsAmountSet()
	{
		return array_key_exists('amount', $this->values);
	}


	/**
	* 设置APP和网页支付提交用户端ip，Native支付填调用好易支付API的机器IP。
	* @param string $value 
	**/
	public function SetClientIp($value)
	{
		$this->values['clientIp'] = $value;
	}
	/**
	* 获取APP和网页支付提交用户端ip，Native支付填调用好易支付API的机器IP。的值
	* @return 值
	**/
	public function GetClientIp()
	{
		return $this->values['clientIp'];
	}
	/**
	* 判断APP和网页支付提交用户端ip，Native支付填调用好易支付API的机器IP。是否存在
	* @return true 或 false
	**/
	public function IsClientIpSet()
	{
		return array_key_exists('clientIp', $this->values);
	}
    

	/**
	* 设置接收好易支付异步通知回调地址
	* @param string $value 
	**/
	public function SetNotifyUrl($value)
	{
		$this->values['notifyUrl'] = $value;
	}
	/**
	* 获取接收好易支付异步通知回调地址的值
	* @return 值
	**/
	public function GetNotifyUrl()
	{
		return $this->values['notifyUrl'];
	}
	/**
	* 判断接收好易支付异步通知回调地址是否存在
	* @return true 或 false
	**/
	public function IsNotifyUrlSet()
	{
		return array_key_exists('notifyUrl', $this->values);
	}


	/**
	* 设置取值如下：qrpay_ali，wxpay_app
    * 详细说明见参数规定http://doc.1080.com/guide/doc/41.html
	* @param string $value 
	**/
	public function SetChannelId($value)
	{
		$this->values['channelId'] = $value;
	}
	/**
     * 设置取值如下：qrpay_ali，wxpay_app
     * 详细说明见参数规定http://doc.1080.com/guide/doc/41.html
	* @return 值
	**/
	public function GetChannelId()
	{
		return $this->values['channelId'];
	}
	/**
     * 设置取值如下：qrpay_ali，wxpay_app
     * 详细说明见参数规定http://doc.1080.com/guide/doc/41.html
	* @return true 或 false
	**/
	public function IsChannelIdSet()
	{
		return array_key_exists('channelId', $this->values);
	}


	/**
	* （1）当请求参数channelId = wxpay_jsapi （好易应用号支付）时，openId参数必填，对应用户所在好易应用号的openId。

    {"openId":"o2RvowBf7sOVJf8kJksUEMceaDqo"}
    （2）当请求参数channelId = wxpay_native （好易原生扫码支付）时，productId参数必填，对应业务系统定义的商品ID。

    {"productId":"120989823"}
    （3）当请求参数channelId = alipay_wap （支付宝WAP支付）时，可传参数ali_show_url，表示用户付款中途退出返回商户网站的地址。不传默认地址为：www.xxpay.org。

    {"ali_show_url":"http://www.xiaoshuding.com"}
    （4）当请求参数channelId = alipay_pc （支付宝PC支付）时，可传参数qr_pay_mode、qrcode_width。

    {"qr_pay_mode":"4", "qrcode_width":"200"}
	* @param string $value 
	**/
	public function SetExtra($value)
	{
		$this->values['extra'] = $value;
	}
	/**
	* @return 值
	**/
	public function GetExtra()
	{
		return $this->values['extra'];
	}
	/**
	* @return true 或 false
	**/
	public function IsExtraSet()
	{
		return array_key_exists('extra', $this->values);
	}


	/**
	* 平台用户，此参数必传
	* @param string $value 
	**/
	public function SetPassageId($value)
	{
		$this->values['passageId'] = $value;
	}
	/**
	* 平台用户，此参数必传
     * @return 值
	**/
	public function GetPassageId()
	{
		return $this->values['passageId'];
	}
	/**
	* @return true 或 false
	**/
	public function IsPassageIdSet()
	{
		return array_key_exists('passageId', $this->values);
	}
}

/**
 * 
 * 订单查询输入对象
 * @author widyhu
 *
 */
class HyPayOrderQuery extends HyPayDataBase
{
	/**
	* 设置好易分配的应用账号ID
	* @param string $value 
	**/
	public function SetAppId($value)
	{
		$this->values['appId'] = $value;
	}
	/**
	* 获取好易分配的应用账号ID的值
	* @return 值
	**/
	public function GetAppId()
	{
		return $this->values['appId'];
	}
	/**
	* 判断好易分配的应用账号ID是否存在
	* @return true 或 false
	**/
	public function IsAppIdSet()
	{
		return array_key_exists('appId', $this->values);
	}


	/**
	* 设置好易支付分配的商户号
	* @param string $value 
	**/
	public function SetMchId($value)
	{
		$this->values['mchId'] = $value;
	}
	/**
	* 获取好易支付分配的商户号的值
	* @return 值
	**/
	public function GetMchId()
	{
		return $this->values['mchId'];
	}
	/**
	* 判断好易支付分配的商户号是否存在
	* @return true 或 false
	**/
	public function IsMchIdSet()
	{
		return array_key_exists('mchId', $this->values);
	}


	/**
	* 设置好易的订单号，优先使用
	* @param string $value 
	**/
	public function SetPayOrderId($value)
	{
		$this->values['payOrderId'] = $value;
	}
	/**
	* 获取好易的订单号，优先使用的值
	* @return 值
	**/
	public function GetPayOrderId()
	{
		return $this->values['payOrderId'];
	}
	/**
	* 判断好易的订单号，优先使用是否存在
	* @return true 或 false
	**/
	public function IsPayOrderIdSet()
	{
		return array_key_exists('payOrderId', $this->values);
	}


	/**
	* 设置商户系统内部的订单号，当没提供payOrderId时需要传这个。
	* @param string $value 
	**/
	public function SetMchOrderNo($value)
	{
		$this->values['mchOrderNo'] = $value;
	}
	/**
	* 获取商户系统内部的订单号，当没提供payOrderId时需要传这个。的值
	* @return 值
	**/
	public function GetMchOrderNo()
	{
		return $this->values['mchOrderNo'];
	}
	/**
	* 判断商户系统内部的订单号，当没提供payOrderId时需要传这个。是否存在
	* @return true 或 false
	**/
	public function IsMchOrderNoSet()
	{
		return array_key_exists('mchOrderNo', $this->values);
	}


	/**
	* 设置随机字符串，不长于32位。推荐随机数生成算法
	* @param string $value 
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* 获取随机字符串，不长于32位。推荐随机数生成算法的值
	* @return 值
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* 判断随机字符串，不长于32位。推荐随机数生成算法是否存在
	* @return true 或 false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}
}


/**
 * 
 * 提交退款输入对象
 * @author widyhu
 *
 */
class HyPayRefund extends HyPayDataBase
{
	/**
	* 设置好易分配的应用账号ID
	* @param string $value 
	**/
	public function SetAppId($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* 获取好易分配的应用账号ID的值
	* @return 值
	**/
	public function GetAppId()
	{
		return $this->values['appid'];
	}
	/**
	* 判断好易分配的应用账号ID是否存在
	* @return true 或 false
	**/
	public function IsAppIdSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* 设置好易支付分配的商户号
	* @param string $value 
	**/
	public function SetMchId($value)
	{
		$this->values['mchId'] = $value;
	}
	/**
	* 获取好易支付分配的商户号的值
	* @return 值
	**/
	public function GetMchId()
	{
		return $this->values['mchId'];
	}
	/**
	* 判断好易支付分配的商户号是否存在
	* @return true 或 false
	**/
	public function IsMchIdSet()
	{
		return array_key_exists('mchId', $this->values);
	}


	/**
	* 设置好易支付分配的终端设备号，与下单一致
	* @param string $value 
	**/
	public function SetDevice($value)
	{
		$this->values['device'] = $value;
	}
	/**
	* 获取好易支付分配的终端设备号，与下单一致的值
	* @return 值
	**/
	public function GetDevice()
	{
		return $this->values['device'];
	}
	/**
	* 判断好易支付分配的终端设备号，与下单一致是否存在
	* @return true 或 false
	**/
	public function IsDeviceSet()
	{
		return array_key_exists('device', $this->values);
	}


	/**
	* 设置随机字符串，不长于32位。推荐随机数生成算法
	* @param string $value 
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* 获取随机字符串，不长于32位。推荐随机数生成算法的值
	* @return 值
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* 判断随机字符串，不长于32位。推荐随机数生成算法是否存在
	* @return true 或 false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}

	/**
	* 设置好易订单号
	* @param string $value 
	**/
	public function SetPayOrderId($value)
	{
		$this->values['payOrderId'] = $value;
	}
	/**
	* 获取好易订单号的值
	* @return 值
	**/
	public function GetPayOrderId()
	{
		return $this->values['payOrderId'];
	}
	/**
	* 判断好易订单号是否存在
	* @return true 或 false
	**/
	public function IsPayOrderIdSet()
	{
		return array_key_exists('payOrderId', $this->values);
	}


	/**
	* 设置商户系统内部的订单号,payOrderId、mchOrderNo二选一，如果同时存在优先级：payOrderId> mchOrderNo
	* @param string $value 
	**/
	public function SetMchOrderNo($value)
	{
		$this->values['mchOrderNo'] = $value;
	}
	/**
	* 获取商户系统内部的订单号,payOrderId、mchOrderNo二选一，如果同时存在优先级：payOrderId> mchOrderNo的值
	* @return 值
	**/
	public function GetMchOrderNo()
	{
		return $this->values['mchOrderNo'];
	}
	/**
	* 判断商户系统内部的订单号,payOrderId、mchOrderNo二选一，如果同时存在优先级：payOrderId> mchOrderNo是否存在
	* @return true 或 false
	**/
	public function IsMchOrderNoSet()
	{
		return array_key_exists('mchOrderNo', $this->values);
	}


	/**
	* 设置商户系统内部的退款单号，商户系统内部唯一，同一退款单号多次请求只退一笔
	* @param string $value 
	**/
	public function SetMchRefundNo($value)
	{
		$this->values['mchRefundNo'] = $value;
	}
	/**
	* 获取商户系统内部的退款单号，商户系统内部唯一，同一退款单号多次请求只退一笔的值
	* @return 值
	**/
	public function GetMchRefundNo()
	{
		return $this->values['mchRefundNo'];
	}
	/**
	* 判断商户系统内部的退款单号，商户系统内部唯一，同一退款单号多次请求只退一笔是否存在
	* @return true 或 false
	**/
	public function IsMchRefundNoSet()
	{
		return array_key_exists('mchRefundNo', $this->values);
	}


	/**
	* 设置订单总金额，单位为分，只能为整数，详见支付金额
	* @param string $value 
	**/
	public function SetAmount($value)
	{
		$this->values['amount'] = $value;
	}
	/**
	* 获取订单总金额，单位为分，只能为整数，详见支付金额的值
	* @return 值
	**/
	public function GetAmount()
	{
		return $this->values['amount'];
	}
	/**
	* 判断订单总金额，单位为分，只能为整数，详见支付金额是否存在
	* @return true 或 false
	**/
	public function IsAmountSet()
	{
		return array_key_exists('amount', $this->values);
	}


	/**
	* 设置退款总金额，订单总金额，单位为分，只能为整数，详见支付金额
	* @param string $value
	**/
	public function SetRefund_fee($value)
	{
		$this->values['refund_fee'] = $value;
	}
	/**
	* 获取退款总金额，订单总金额，单位为分，只能为整数，详见支付金额的值
	* @return 值
	**/
	public function GetRefund_fee()
	{
		return $this->values['refund_fee'];
	}
	/**
	* 判断退款总金额，订单总金额，单位为分，只能为整数，详见支付金额是否存在
	* @return true 或 false
	**/
	public function IsRefund_feeSet()
	{
		return array_key_exists('refund_fee', $this->values);
	}


	/**
	* 设置货币类型，符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型
	* @param string $value 
	**/
	public function SetCurrency($value)
	{
		$this->values['currency'] = $value;
	}
	/**
	* 获取货币类型，符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型的值
	* @return 值
	**/
	public function GetCurrency()
	{
		return $this->values['currency'];
	}
	/**
	* 判断货币类型，符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型是否存在
	* @return true 或 false
	**/
	public function IsCurrencySet()
	{
		return array_key_exists('currency', $this->values);
	}


	/**
	* 设置操作员帐号, 默认为商户号
	* @param string $value 
	**/
	public function SetOp_user_id($value)
	{
		$this->values['op_user_id'] = $value;
	}
	/**
	* 获取操作员帐号, 默认为商户号的值
	* @return 值
	**/
	public function GetOp_user_id()
	{
		return $this->values['op_user_id'];
	}
	/**
	* 判断操作员帐号, 默认为商户号是否存在
	* @return true 或 false
	**/
	public function IsOp_user_idSet()
	{
		return array_key_exists('op_user_id', $this->values);
	}
}

/**
 * 
 * 退款查询输入对象
 * @author widyhu
 *
 */
class HyPayRefundQuery extends HyPayDataBase
{
	/**
	* 设置好易分配的应用账号ID
	* @param string $value 
	**/
	public function SetAppId($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* 获取好易分配的应用账号ID的值
	* @return 值
	**/
	public function GetAppId()
	{
		return $this->values['appid'];
	}
	/**
	* 判断好易分配的应用账号ID是否存在
	* @return true 或 false
	**/
	public function IsAppIdSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* 设置好易支付分配的商户号
	* @param string $value 
	**/
	public function SetMchId($value)
	{
		$this->values['mchId'] = $value;
	}
	/**
	* 获取好易支付分配的商户号的值
	* @return 值
	**/
	public function GetMchId()
	{
		return $this->values['mchId'];
	}
	/**
	* 判断好易支付分配的商户号是否存在
	* @return true 或 false
	**/
	public function IsMchIdSet()
	{
		return array_key_exists('mchId', $this->values);
	}


	/**
	* 设置好易支付分配的终端设备号
	* @param string $value 
	**/
	public function SetDevice($value)
	{
		$this->values['device'] = $value;
	}
	/**
	* 获取好易支付分配的终端设备号的值
	* @return 值
	**/
	public function GetDevice()
	{
		return $this->values['device'];
	}
	/**
	* 判断好易支付分配的终端设备号是否存在
	* @return true 或 false
	**/
	public function IsDeviceSet()
	{
		return array_key_exists('device', $this->values);
	}


	/**
	* 设置随机字符串，不长于32位。推荐随机数生成算法
	* @param string $value 
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* 获取随机字符串，不长于32位。推荐随机数生成算法的值
	* @return 值
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* 判断随机字符串，不长于32位。推荐随机数生成算法是否存在
	* @return true 或 false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}

	/**
	* 设置好易订单号
	* @param string $value 
	**/
	public function SetPayOrderId($value)
	{
		$this->values['payOrderId'] = $value;
	}
	/**
	* 获取好易订单号的值
	* @return 值
	**/
	public function GetPayOrderId()
	{
		return $this->values['payOrderId'];
	}
	/**
	* 判断好易订单号是否存在
	* @return true 或 false
	**/
	public function IsPayOrderIdSet()
	{
		return array_key_exists('payOrderId', $this->values);
	}


	/**
	* 设置商户系统内部的订单号
	* @param string $value 
	**/
	public function SetMchOrderNo($value)
	{
		$this->values['mchOrderNo'] = $value;
	}
	/**
	* 获取商户系统内部的订单号的值
	* @return 值
	**/
	public function GetMchOrderNo()
	{
		return $this->values['mchOrderNo'];
	}
	/**
	* 判断商户系统内部的订单号是否存在
	* @return true 或 false
	**/
	public function IsMchOrderNoSet()
	{
		return array_key_exists('mchOrderNo', $this->values);
	}


	/**
	* 设置商户退款单号
	* @param string $value 
	**/
	public function SetMchRefundNo($value)
	{
		$this->values['mchRefundNo'] = $value;
	}
	/**
	* 获取商户退款单号的值
	* @return 值
	**/
	public function GetMchRefundNo()
	{
		return $this->values['mchRefundNo'];
	}
	/**
	* 判断商户退款单号是否存在
	* @return true 或 false
	**/
	public function IsMchRefundNoSet()
	{
		return array_key_exists('mchRefundNo', $this->values);
	}


	/**
	* 设置好易退款单号refundOrderId、mchRefundNo、mchOrderNo、payOrderId四个参数必填一个，如果同时存在优先级为：refundOrderId>mchRefundNo>payOrderId>mchOrderNo
	* @param string $value 
	**/
	public function SetRefundOrderId($value)
	{
		$this->values['refundOrderId'] = $value;
	}
	/**
	* 获取好易退款单号refundOrderId、mchRefundNo、mchOrderNo、payOrderId四个参数必填一个，如果同时存在优先级为：refundOrderId>mchRefundNo>payOrderId>mchOrderNo的值
	* @return 值
	**/
	public function GetRefundOrderId()
	{
		return $this->values['refundOrderId'];
	}
	/**
	* 判断好易退款单号refundOrderId、mchRefundNo、mchOrderNo、payOrderId四个参数必填一个，如果同时存在优先级为：refundOrderId>mchRefundNo>payOrderId>mchOrderNo是否存在
	* @return true 或 false
	**/
	public function IsRefundOrderIdSet()
	{
		return array_key_exists('refundOrderId', $this->values);
	}
}

/**
 * 
 * 下载对账单输入对象
 * @author widyhu
 *
 */
class HyPayDownloadBill extends HyPayDataBase
{
	/**
	* 设置好易分配的应用账号ID
	* @param string $value 
	**/
	public function SetAppId($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* 获取好易分配的应用账号ID的值
	* @return 值
	**/
	public function GetAppId()
	{
		return $this->values['appid'];
	}
	/**
	* 判断好易分配的应用账号ID是否存在
	* @return true 或 false
	**/
	public function IsAppIdSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* 设置好易支付分配的商户号
	* @param string $value 
	**/
	public function SetMchId($value)
	{
		$this->values['mchId'] = $value;
	}
	/**
	* 获取好易支付分配的商户号的值
	* @return 值
	**/
	public function GetMchId()
	{
		return $this->values['mchId'];
	}
	/**
	* 判断好易支付分配的商户号是否存在
	* @return true 或 false
	**/
	public function IsMchIdSet()
	{
		return array_key_exists('mchId', $this->values);
	}


	/**
	* 设置好易支付分配的终端设备号，填写此字段，只下载该设备号的对账单
	* @param string $value 
	**/
	public function SetDevice($value)
	{
		$this->values['device'] = $value;
	}
	/**
	* 获取好易支付分配的终端设备号，填写此字段，只下载该设备号的对账单的值
	* @return 值
	**/
	public function GetDevice()
	{
		return $this->values['device'];
	}
	/**
	* 判断好易支付分配的终端设备号，填写此字段，只下载该设备号的对账单是否存在
	* @return true 或 false
	**/
	public function IsDeviceSet()
	{
		return array_key_exists('device', $this->values);
	}


	/**
	* 设置随机字符串，不长于32位。推荐随机数生成算法
	* @param string $value 
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* 获取随机字符串，不长于32位。推荐随机数生成算法的值
	* @return 值
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* 判断随机字符串，不长于32位。推荐随机数生成算法是否存在
	* @return true 或 false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}

	/**
	* 设置下载对账单的日期，格式：20140603
	* @param string $value 
	**/
	public function SetBill_date($value)
	{
		$this->values['bill_date'] = $value;
	}
	/**
	* 获取下载对账单的日期，格式：20140603的值
	* @return 值
	**/
	public function GetBill_date()
	{
		return $this->values['bill_date'];
	}
	/**
	* 判断下载对账单的日期，格式：20140603是否存在
	* @return true 或 false
	**/
	public function IsBill_dateSet()
	{
		return array_key_exists('bill_date', $this->values);
	}


	/**
	* 设置ALL，返回当日所有订单信息，默认值SUCCESS，返回当日成功支付的订单REFUND，返回当日退款订单REVOKED，已撤销的订单
	* @param string $value 
	**/
	public function SetBill_type($value)
	{
		$this->values['bill_type'] = $value;
	}
	/**
	* 获取ALL，返回当日所有订单信息，默认值SUCCESS，返回当日成功支付的订单REFUND，返回当日退款订单REVOKED，已撤销的订单的值
	* @return 值
	**/
	public function GetBill_type()
	{
		return $this->values['bill_type'];
	}
	/**
	* 判断ALL，返回当日所有订单信息，默认值SUCCESS，返回当日成功支付的订单REFUND，返回当日退款订单REVOKED，已撤销的订单是否存在
	* @return true 或 false
	**/
	public function IsBill_typeSet()
	{
		return array_key_exists('bill_type', $this->values);
	}
}
