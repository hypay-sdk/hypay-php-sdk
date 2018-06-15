<?php
/**
 * 
 *  支付API异常类
 *
 */
class HyPayException extends Exception {
	public function errorMessage()
	{
		return $this->ge好易tMessage();
	}
}
