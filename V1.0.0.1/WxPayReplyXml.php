<?php
/*
 +----------------------------------------------------------------------
 + Title        : 微信公众平台开发,基本应用功能封装(包括Token验证)
 + Author       : 小黄牛
 + Version      : V1.0.0.1
 + Initial-Time : 2017-02-25 15:46:00
 + Last-time    :
 + List         :
 + Desc         : 该类囊括了所有自动化功能，包括Token验证等
 +----------------------------------------------------------------------
*/
require_once('Wx.php');
# 引入普通Access_Token获取类
require_once('WxPayToken.php');

# 基本应用功能封装类
class WxReplyXml extends Wx{

	/*
	 * Title : 封装Token验证的方法
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * Return bool:  true|false
	*/
	private function CheckSign(){
		# 获得微信发送过来的加密签名
		$signature = $_GET["signature"];
		# 时间戳
		$timestamp = $_GET["timestamp"];
		# 随机数
		$nonce = $_GET["nonce"];

		# Token + 时间戳 + 随机数 = 组合成数组
		$tmpArr = array($this->WxConfig['Token'], $timestamp, $nonce);
		// 对数组进行升序重新排序
		sort($tmpArr, SORT_STRING);
		# 把数组for拼接成字符串
		$tmpStr = implode( $tmpArr );
		# 进行sha1加密
		$tmpStr = sha1( $tmpStr );

		# 最后与微信发送过来的加密签名进行对比,成功返回true
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

	/*
	 * Title : 执行该方法验证Token有效性
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * Return bool:  true|false
	*/
	public function TokenVif(){
		# 接收微信向你服务器发送过来的随机字符串
		$echoStr = $_GET["echostr"];
		# 执行checkSignature , 进行数字认证
		if($this->CheckSign()){
			# 认证通过返回随机字符串给微信,告诉它认证通过了
			echo $echoStr;
			return true;
		}
	}

	/*
	 * Title : 使用该方法做信息交互回复
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * Return : 各种子类分支的回复内容
	*/
	public function ResponseMsg(){
		$postObj = $this->XmlMsg(); //获取XML格式的数据,返回的是一个对象
		if (!empty($postObj)){

			# 用户消息类型分支判定
			switch($postObj->MsgType){
				case "event":// 事件类型
					$result = $this->receiveEvent($postObj);
					break;
				case "text":// 文本类型
					$result = $this->receiveText($postObj);
					break;
				case "image" :// 图片类型
					$result = $this->receiveText($postObj);
					break;
				case "voice" :// 语音类型
					$result = $this->receiveText($postObj);
					break;
				case "video" :// 视频类型
					$result = $this->receiveText($postObj);
					break;
				case "shortvideo" :// 小视频类型
					$result = $this->receiveText($postObj);
					break;
				case "location" :// 地理位置类型
					$result = $this->receiveLocation($postObj);
					break;
				case "link" :// 链接类型
					$result = $this->receiveLocation($postObj);
					break;
				default :
					$result = "此项信息类型尚未开发，敬请期待...";
			}
			echo $result;
		}

	}

	/*
	 * Title : ResponseMsg的子函数，处理事件类型
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * Return : 事件类型的处理
	*/
    private function receiveEvent($object){
		# $object->Event 表示用户消息的的事件类型
		switch ($object->Event){
			case "subscribe":// subscribe（订阅）
				# 关注后回复的欢迎语，若要修改，请查看微信公众号文档及该类封装的方法
				$content = $this->welcome;
                # 文本回复
				$result = $this->transmitText($object, $content);
				/**
				 *  附加功能 ———— 带参数的二维码，关注时即静默登录/注册会员 （不用可删）
				 *  判断的内容是：获取扫描关注时二维码的参数值 （该功能为 带链接的二维码）
				 */
				if( preg_match("/^qr/i",$object->EventKey) ){
					$contentStr = explode('_',$object->EventKey);
					$tjuserid = $contentStr[1];
				}else{
					$tjuserid = 0;
				}
				/*
				 * 此处是自动注册的逻辑代码，需要该功能的人可以自行添加
				*/
				break;
			case "unsubscribe":// unsubscribe（取消订阅）
				break;
			case "CLICK":// click（点击菜单拉取消息的事件推送）
				switch($object->EventKey){
					# 菜单中的"key"值，此处设置点击菜单的相应操作
					case "V1001_MUSIC":
						//$content = "请回复歌曲名（如:“白色恋人”），就能获取想听的歌曲";
						//$result = $this->transmitText($object, $content);
						break;
					case "V1001_DELIVERY":
						//$content = "";
						//$result = $this->transmitText($object, $content);
						break;
				}
			case "SCAN":// 扫描二维码
				# 要实现统计分析，则需要扫描事件写入数据库，这里可以记录 EventKey及用户OpenID，扫描时间
				break;
			//case "scancode_push"  "scancode_waitmsg":
			//case "pic_sysphoto"  "pic_photo_or_album" "pic_weixin":
			//case "location_select":
			default:
				break;
		}
		return $result;
	}

    /*
     * Title : ResponseMsg的子函数，多种普通消息类型
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * Return : 事件类型的处理
    */
    private function receiveText($object){
        # 暂无代码
	}

    /*
     * Title : ResponseMsg的子函数，收到地理位置和链接的操作->文本形式回复
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * Return : 事件类型的处理
    */
    private function receiveLocation($object){
        switch ($object->MsgType){
            case "location": // 地理位置类型
                $content = "";
                break;
            case "link":// 链接类型
                $content = "";
                break;
        }
        // 文本回复
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /******************************************** 以下方法是公众号回复形式 ********************************************/
    /*
     * Title : 文本形式回复
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * Return : Xml
    */
    private function transmitText($object, $content=null){
        //构建XML格式文本  CDATA表示不转义； %s 表示数据类型；*** <FuncFlag>0</FuncFlag>  Funcflag 表示是否是星标微信,暂时不保留***
        $textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
					</xml>";

        $msgType = "text";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $msgType, $content);
        return $result;
    }

    /*
     * Title : 图片形式回复
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * Return : Xml
    */
    private function transmitImage($object, $media_id=null){
        $imageTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Image>
							<MediaId><![CDATA[media_id]]></MediaId>
						</Image>
					</xml>";

        $msgType = "image";
        $result = sprintf($imageTpl, $object->FromUserName, $object->ToUserName, time(), $msgType, $media_id);
        return $result;
    }

    /*
     * Title : 语音形式回复
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * Return : Xml
    */
    private function transmitVoice($object, $media_id=null){
        $voiceTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Voice>
							<MediaId><![CDATA[media_id]]></MediaId>
						</Voice>
					</xml>";

        $msgType = "voice";
        $result = sprintf($voiceTpl, $object->FromUserName, $object->ToUserName, time(), $msgType, $media_id);
        return $result;
    }

    /*
     * Title : 视频形式回复（$videoArray是一个数组数据）
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * Return : Xml
    */
    private function transmitVideo($object, $videoArray){
        $videoTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Video>
							<MediaId><![CDATA[media_id]]></MediaId>
							<Title><![CDATA[title]]></Title>
							<Description><![CDATA[description]]></Description>
						</Video>
					</xml>";

        $msgType = "video";
        $result = sprintf($videoTpl, $object->FromUserName, $object->ToUserName, time(), $msgType);
        return $result;
    }

    /*
     * Title : 音乐形式回复（$musicArray是一个数组数据）
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * Return : Xml
    */
    private function transmitMusic($object, $musicArray){
        $musicTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Music>
							<Title><![CDATA[南山南]]></Title>
							<Description><![CDATA[马頔]]></Description>
							<MusicUrl><![CDATA[http://weiyanweiyan.duapp-preview.com/music/1.mp3]]></MusicUrl>
							<HQMusicUrl><![CDATA[http://weiyanweiyan.duapp-preview.com/music/1.mp3]]></HQMusicUrl>
						</Music>
					</xml>";

        $msgType = "music";
        $result = sprintf($musicTpl, $object->FromUserName, $object->ToUserName, time(), $msgType);
        return $result;
    }

    /*
     * Title : 图文形式回复（$newsArray是一个数组数据）
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * Return : Xml
    */
    private function transmitNews($object, $newsArray){
        $newsTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<ArticleCount>2</ArticleCount>
						<Articles>
							<item>
								<Title><![CDATA[title1]]></Title>
								<Description><![CDATA[description1]]></Description>
								<PicUrl><![CDATA[picurl]]></PicUrl>
								<Url><![CDATA[url]]></Url>
							</item>
						</Articles>
					</xml>";

        $msgType = "news";
        $result = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), $msgType);
        return $result;
    }

    /******************************************** 以下方法是菜单相关 **************************************************/
    /*
     * Title : 创建自定义菜单
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * Return : 无
    */
    public function CreateMenu(){
        # 获取Access_Token
        $obj = new Access_Token();
        $access_token = $obj->GetToken();
        # 发送请求
        $res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['Menu_Add_Url'], array(
            $access_token
        )), $this->WxConfig['Menu_List']);
        //var_dump(json_decode($res, true));
    }

    /*
     * Title : 删除自定义菜单
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * Return : 无
    */
    public function DelMenu(){
        # 获取Access_Token
        $obj = new Access_Token();
        $access_token = $obj->GetToken();
        # 发送请求
        $res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['Menu_Del_Url'], array(
            $access_token
        )));
        //var_dump(json_decode($res, true));
    }

    /*
     * Title : 查询自定义菜单
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * Return : 无
    */
    public function GetMenu(){
        # 获取Access_Token
        $obj = new Access_Token();
        $access_token = $obj->GetToken();
        # 发送请求
        $res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['Menu_Get_Url'], array(
            $access_token
        )));
        //var_dump(json_decode($res, true));
    }

    /*
     * Title : 查询自定义菜单(包括菜单配置)
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * Return : 无
    */
    public function GetListMenu(){
        # 获取Access_Token
        $obj = new Access_Token();
        $access_token = $obj->GetToken();
        # 发送请求
        $res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['Menu_GetList_Url'], array(
            $access_token
        )));
        //var_dump(json_decode($res, true));
    }

    /******************************************** 以下是模板信息相关 **************************************************/
    /*
     * Title : 更改行业信息
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * @param int $Str_1  : 所属行业编码
     * @param int $Str_2  : 所属行业编码
     * Return : 无
    */
    public function ChangeJob($Str_1=1, $Str_2=2){
        $jsonjob = '{
					  "industry_id1":"'.$Str_1.'",
					  "industry_id2":"'.$Str_2.'"
				    }';
        # 获取Access_Token
        $obj = new Access_Token();
        $access_token = $obj->GetToken();
        # 发送请求
        $res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['Industry_Save_Url'], array(
            $access_token
        )), $jsonjob);
        //var_dump(json_decode($res, true));
    }

    /*
     * Title : 选择模板
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * @param string $Mid  : 模板库中模板的编号
     * Return : 请求返回内容
    */
    public function ChoseMod($Mid=null){
        # 为空使用配置文件里的默认模板编号
        $Mid = empty($Mid) ? $this->WxConfig['Template_Id_Short'] : $Mid;
        $jsonmod = '{
					  "template_id_short": '.$mid.'
				    }';
        # 获取Access_Token
        $obj = new Access_Token();
        $access_token = $obj->GetToken();
        # 发送请求
        $res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['Industry_Set_Url'], array(
            $access_token
        )), $jsonmod);
        //var_dump(json_decode($res, true));
        return json_decode($res, true);
    }

    /*
     * Title : 发送模板信息
     * Author: 小黄牛
     * Last  : 修改时间 + 修改人
     * @param string $Opendi  : 用户的微信ID
     * @param string $Mid    : ChoseMod()返回的模板ID
     * @param string $Url   : 模板消息中，需要点击跳转到的详情地址
     * @param JSON  $Data  :  模板消息内容，具体格式参考微信开发文档
     * Return : 请求返回内容
    */
    public function SendMsg($OpenId, $Mid, $Url, $Data){
        $jsonmod = ' {
					   "touser":"'.$OpenId.'",
					   "template_id":"'.$Mid.'",
					   "url":"'.$Url.'",
					   "data":'.$Data.'
				   }';
        # 获取Access_Token
        $obj = new Access_Token();
        $access_token = $obj->GetToken();
        # 发送请求
        $res    = $this->HttpsRequest($this->StrUrl($this->WxConfig['Industry_Add_Url'], array(
            $access_token
        )), $jsonmod);
        //var_dump(json_decode($res, true));
        return json_decode($res, true);
    }

}