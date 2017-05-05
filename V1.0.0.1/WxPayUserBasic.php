<?php
/*
 +----------------------------------------------------------------------
 + Title        : 微信公众平台开发,用户管理功能汇总 + 网页授权登录
 + Author       : 小黄牛
 + Version      : V1.0.0.1
 + Initial-Time : 2017-02-24 14:34:00
 + Last-time    : 修改时间 + 修改人
 + List         :
 + Desc         : 公众号所有关于用户管理接口的功能汇总，还有两种微站授权登录模式
 +----------------------------------------------------------------------
*/
require_once('Wx.php');
# 引入普通Access_Token获取类
require_once('WxPayToken.php');

# 用户管理功能汇总 + 网页授权登录类
class WxUser_Basic extends Wx{
	/*
	 * Title : 关注时获取用户的OpenID
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * Return: 用户的OpenID
	*/
	public function OpenId() {
		# 获取交互信息
		$postObj = $this->XmlMsg();
		return $postObj->FromUserName;
	}

	/*
	 * Title : 使用OpenID去获取用户的基本信息
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * @param string $OpenId ：用户的OpenId
	 * Return: 用户的基本信息
	*/
	public function UserBasic($OpenId) {
		# 获取Access_Token
		$obj = new Access_Token();
		$access_token = $obj->GetToken();
		# 使用OpenID去获取用户信息
		# 发送请求
		$res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['AppUserBasic_Url'], array(
			$access_token,
			$OpenId
		)));
		# 将请求内容写入日志
		$this->ErrorLog(array(
			'title' => '获取到的用户信息：',
			'body'  => $res
		));
		return json_decode($res, true);
	}

	/*
	 * Title : 设置用户的备注名
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * @param string $OpenId ：用户的OpenId
	 * @param string $Remark ：设置的备注名
	 * Return: 回调内容
	*/
	public function Remarks($OpenId, $Remark) {
		# 使用OpenID去修改备注名
		$body = json_encode(array(
			'openid' => $OpenId,
			'remark' => $Remark
		));
		# 获取Access_Token
		$obj = new Access_Token();
		$access_token = $obj->GetToken();
		# 发送请求
		$res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['AppUserRemarks_Url'], array(
			$access_token
		)), $body);
		# 将请求内容写入日志
		$this->ErrorLog(array(
			'title' => '修改用户备注的请求信息：',
			'body'  => $res
		));
		return json_decode($res, true);
	}

	/*
	 * Title : 获取用户列表
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * @param string $OpenId ：用户的OpenId
	 * Return: 回调内容
	*/
	public function UserList($OpenId=null) {
		# 获取Access_Token
		$obj = new Access_Token();
		$access_token = $obj->GetToken();
		# 发送请求
		$res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['AppUserList_Url'], array(
			$access_token,
			$OpenId
		)));
		# 将请求内容写入日志
		$this->ErrorLog(array(
			'title' => '修改用户备注的请求信息：',
			'body'  => $res
		));
		return json_decode($res, true);
	}

	/*
	 * Title : 生成网页授权链接
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * @param string $Url   : 授权后重定向的回调链接地址
	 * @param string $State : 自己定义的参数，具体看微信开发手册
	 * Return: 返回生成后的链接
	*/
	public function WebUrl($Url, $State=1){
			$Array = array(
				$this->WxConfig['AppId'],
				urlencode($Url),
				$this->WxConfig['WebMicro_Type'],
				$State
			);
			return $this->StrUrl($this->WxConfig['Snsapi_Url'], $Array);
	}

	/*
	 * Title : 根据code去获取特殊access_token
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * @param string $Code : 授权后返回的code参数
	 * Return: 返回特殊AcToken的请求内容
	*/
	public function CodeToken($Code){
		# 发送请求获得特殊actoken
		$res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['Snsapi_AcToken_Url'], array(
			$this->WxConfig['AppId'],
			$this->WxConfig['AppSecret'],
			$Code
		)));
		$this->ErrorLog(array(
			'Title' => '使用code获取到的actoken：',
			'Body'  => $res
		));
		return json_decode($res, true);
	}

	/*
	 * Title : 根据凭据，更新特殊access_token
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * @param string $ReToken : 更新凭据
	 * Return: 返回更新后的特殊AcToken的请求内容
	*/
	public function SaveCodeToken($ReToken){
		# 发送请求获得特殊actoken
		$res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['Snsapi_SaveAcToken_Url'], array(
			$this->WxConfig['AppId'],
			$ReToken
		)));
		$this->ErrorLog(array(
			'Title' => '使用凭据更新后的actoken：',
			'Body'  => $res
		));
		return json_decode($res, true);
	}

	/*
	 * Title : 使用特殊access_token去获取用户信息
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * @param string $Token  : 特殊access_token
	 * @param string $OpenId : 用户的OpenId
	 * Return: 返回用户的信息
	*/
	public function CodeUser($Token, $OpenId){
		# 发送请求获得特殊actoken
		$res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['Snsapi_User_Url'], array(
			$Token,
			$OpenId,
			$this->WxConfig['WebMicro_Lang']
		)));
		$this->ErrorLog(array(
			'Title' => '使用特殊actoken获取到的用户信息：',
			'Body'  => $res
		));

		# 判断授权类型，如果是静默授权，就要使用普通接口，获取用户信息
		if ($this->WxConfig['WebMicro_Type'] == 'snsapi_base'){
			# 返回静默授权的用户信息
			return $this->UserBasic($OpenId);
		}

		# 返回点击授权的用户信息
		return json_decode($res, true);
	}

}