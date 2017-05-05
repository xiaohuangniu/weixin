<?php
/*
 +----------------------------------------------------------------------
 + Title        : 微信公众平台开发,普通Access_Token的中控器
 + Author       : 小黄牛
 + Version      : V1.0.0.1
 + Initial-Time : 2017-02-24 14:05:00
 + Last-time    : 2017-02-25 10:25:00 + 小黄牛
 + List         :
 + Desc         : 该文件只将Access_Token写入缓存文件
 +----------------------------------------------------------------------
*/
require_once('Wx.php');

# 微信获取Access_Token 基础使用类
class Access_Token extends Wx{
	/*
	 * Title : 更新Access_Token(会更改缓存)
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * Return: 返回更新后的Access_Token
	*/
	private function SaveToken(){
		# 请求ToKen
		$res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['AppToken_Url'], array(
			$this->WxConfig['AppId'],
			$this->WxConfig['AppSecret']
		)));
		# 接受一个 JSON 格式的字符串并且把它转换为 PHP 变量
		$result = json_decode($res, true);

		# 写入缓存文件
		$info = file_put_contents($this->WxConfig['AC_Token_Name'], json_encode(array(
			'access_token' => $result['access_token'],// Access_Token
			'time'          => time()+$this->WxConfig['AC_Token_Time']// 过期时间
		)));
		return $result['access_token'];
	}

	/*
	 * Title : 验证access_token有效期,有效则返回最新一条access_token,没则更新
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * Return: 返回没过期的Access_Token
	*/
	public function GetToken(){
		# 如果删除了缓存，则重新建立
		if (!file_exists($this->WxConfig['AC_Token_Name'])) {
			return $this->SaveToken();
		}
		# 获取缓存中的access_token
		$body = file_get_contents($this->WxConfig['AC_Token_Name']);
		# 先转回数组
		$result = json_decode($body, true);
		# 如何缓存超过有效期，则重新建立
		if (time() >= $result['time']){
			return $this->SaveToken();
		}

		# 验证token有效性，有效期能不一定不会过期
		$res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['AppTokenCheck_Url'], array(
			$result['access_token']
		)));
		$res = json_decode($res, true);
		# token丢失了有效性，则重新建立
		if (count($res) <= 2){
			return $this->SaveToken();
		}
		# 否则返回缓存token
		return $result['access_token'];
	}

}