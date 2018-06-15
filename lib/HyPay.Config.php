<?php
/**
* 	配置账号信息
*/

class HyPayConfig
{
	//=======【基本信息设置】=====================================
	//
	/**
	 * TODO: 修改这里配置为您自己申请的商户信息
	 * 好易支付信息配置
	 * 
	 * APPID：绑定支付的APPID（必须配置，登录商户平台查看）
	 * 
	 * MCHID：商户号（必须配置，登录商户平台查看）
	 * 
	 * KEY：商户支付密钥（必须配置，登录商户平台自行设置）
	 * 设置地址：http://www.1080.com/api/app/edit
	 *
	 */
	const APPID = '7649de410f334e9bb5e11066a1bb6020';
	const MCHID = '15';
	const KEY = 'nLJ98EYBOx6F5IVWANuAHmrpHm9X8x05P3vBhgqgmwqPElEv';

	//=======【curl代理设置】===================================
	/**
	 * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
	 * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
	 * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
	 * @var unknown_type
	 */
	const CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
	const CURL_PROXY_PORT = 0;//8080;
}
