<?php
/*
 +----------------------------------------------------------------------
 + Title        : 微信公众平台开发,二维码相关功能
 + Author       : 小黄牛
 + Version      : V1.0.0.1
 + Initial-Time : 2017-02-25 17:40:00
 + Last-time    : 2017-02-26 10:10:00 + 小黄牛
 + List         :
 + Desc         : 该类囊括了所有微信二维码相关功能，但(不包括扫描二维码的推送请求)
 +----------------------------------------------------------------------
*/
require_once('Wx.php');
# 引入普通Access_Token获取类
require_once('WxPayToken.php');

# 二维码类
class WxQrcode extends Wx{

	/*
	 * Title : 创建二维码的ticket
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * @param string $Val ：
	 * @param int $Type  ：二维码的类型  1|2|3  =  临时|永久|永久的字符串参数值
	 * Return: 获取到的ticket值
	*/
	public function TemporaryQr($Val, $Type = 1) {
        # 根据类型，选择不同的二维码请求
        switch($Type){
            case '1':
                $Val = intval($Val);
                $jsondata = '{"expire_seconds": 2592000,"action_name": "QR_SCENE","action_info":{"scene":{"scene_id": '.$Val.'}}}';
                break;
            case '2':
                $Val = intval($Val);
                $jsondata = '{"action_name": "QR_LIMIT_SCENE","action_info":{"scene":{"scene_id": '.$Val.'}}}';
                break;
            case '3':
                $jsondata = '{"action_name": "QR_LIMIT_STR_SCENE","action_info":{"scene":{"scene_str": '.$Val.'}}}';
                break;
        }
        # 获取Access_Token
        $obj = new Access_Token();
        $access_token = $obj->GetToken();
        # 使用OpenID去获取用户信息
        # 发送请求
        $res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['Qrcode_Add_Url'], array(
            $access_token
        )), $jsondata);

        // var_dump(json_decode($res, true));
        $arr = json_decode($res, true);
        return $arr['ticket'];
	}

    /*
	 * Title : 使用ticket去换取二维码的链接
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * @param string $Ticket ：通过上面的方法，获取到的ticket值
	 * Return: 一张二维码图片
	*/
    public function Qrcode($Ticket) {
        # 发送请求
        $res    = $this->StrUrl($this->WxConfig['Qrcode_Get_Url'], array(
            urlencode($Ticket)
        ));

        // echo "<img src='$res' />";
        return $res;
    }
}