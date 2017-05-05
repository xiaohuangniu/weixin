<?php
/*
 +----------------------------------------------------------------------------------------------------------------------
 + Title        : 微信公众平台开发,基类
 + Author       : 小黄牛
 + Version      : V1.0.0.1
 + Initial-Time : 2017-02-24 10:39:00
 + Last-time    : 修改时间 + 修改人
 + List         :
 + Desc         : 用于存储一切公共函数，一切公共配置信息，所有自定义开发的第三方类，都该继承至该类
 +----------------------------------------------------------------------------------------------------------------------
*/

# 实际项目上，可删除该时区设置与编码
date_default_timezone_set('PRC');

# 微信基类
class Wx{
	# 配置信息
	protected $WxConfig;

	/*
	 * Title : 读取配置文件，并赋值给成员属性WxConfig
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * Return: 无
	*/
	public function __construct(){
        $this->WxConfig = require('WxConfig.php');
        # var_dump($this->WxConfig);
	}

	/*
	 * Title : 将参数植入占位符 - 占位符为%s%
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * @param string $Url : 占位字符串
	 * @param array $Array：参数是一维数组，参数需要自行按照占位符排序排列
	 * Return: 格式化后的字符串
	*/
	protected function StrUrl($Url, $Array){
        # $num = (count($Array)-1 == 0) ? 1 : count($Array)-1;
		# 循环替换占位内容
		foreach ($Array as $v){
			$Url = preg_replace('/%s%/', $v, $Url, 1);
		}
		return $Url;
	}

	/*
	 * Title : 用于微信接口数据传输的万能函数
	 * Author: 焰哥
	 * Last  : 修改时间 + 修改人
	 * @param string $url : 请求地址
	 * @param max $data   ：请求参数，默认为空
	 * Return: 请求返回内容
	*/
	protected function HttpsRequest($url, $data = null){
		# 初始化一个cURL会话
		$curl = curl_init();

		//设置请求选项, 包括具体的url
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  //禁用后cURL将终止从服务端进行验证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);  //设置为post请求类型
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);  //设置具体的post数据
		}

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($curl);  //执行一个cURL会话并且获取相关回复
		curl_close($curl);  //释放cURL句柄,关闭一个cURL会话

		return $response;
	}

	/*
     * Title  :SQL注入过滤
	 * Author : 小黄牛
	 * Last   : 修改时间 + 修改人
     * $string : 需要过滤的字符串
	 * @param string : $strIng  需要过滤的字符串
     * Return : 返回过滤之后的字符串
    */
	protected function Sql($strIng) {
		$source   = $this->WxConfig['SqlString'];
		$badword  = explode('|', $source);
		# 将val翻转为key，并将val改为null
		$badword1 = array_combine($badword, array_fill(0, count($badword), ''));
		# 将所有非法字符串替换为null
		$strIng   = strtr($strIng, $badword1);
		# 最后再进一步语义化
		$strIng = addslashes($strIng);
		return $strIng;
	}

    /*
     * Title  : 获取交互数据
     * Author : 小黄牛
     * Last  : 修改时间 + 修改人
     * Return : Obj对象
    */
    protected function XmlMsg(){
        # 获得数据包的信息
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        # 如果数据包内的信息不为空
        if (!empty($postStr)) {
            # XML文件的解析依赖libxml库,libxml_disable_entity_loader函数,是为了安全性,防止入侵者通过协议注入XML向服务器发起攻击
            libxml_disable_entity_loader(true);
            # 把XML编译成一个Class对象
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            return $postObj;
        }
        return false;
    }

	/*
	 * Title  : 记录错误信息与查看部分信息
	 * Author : 小黄牛
	 * Last  : 修改时间 + 修改人
	 * @param array  : $Arr_Title  一个一维数组自定义内容
	 * @param bool   : $Arr_Error  是否插入系统错误信息
	 * @param string : $File       日志名
	 * Return : 无
	*/
	protected function ErrorLog($Arr_Title, $Arr_Error=false, $File='Error_log.log'){
		if (!is_dir($this->WxConfig['DeBugList'])) {
			mkdir ($this->WxConfig['DeBugList']);
		}
		switch ($this->WxConfig['DeBug']) {
			case 3 :
				return true;
				break;
			case 1 :
				if (file_exists($this->WxConfig['DeBugList'] . $File)) {
					unlink($this->WxConfig['DeBugList'] . $File);
				}
				break;
		}
		# 不是数组中断程序
		if (!is_array($Arr_Title)) {return false;}
		# 定义一个空的变量,用于存放日志TXT实体
		$Error_TXT = "自定义信息如下(". date('Y-m-d H:m:s', time()) .")：\r\n";
		# 解析Arr_Title 自定义日志内容
		foreach ($Arr_Title as $key=>$val){
			$Error_TXT .= $key.'：'.$val."\r\n";
		}

		# 判断系统错误显示是否开启
		if ($Arr_Error === true) {
			# 获取刚发生的错误信息，并返回数组，无错返回null
			$Arr_Error = error_get_last();
			# 不为空则执行错误解析
			if (isset($Arr_Error)) {
				$Error_TXT .= "系统错误信息如下：\r\n";
				# 解析$Arr_Errore 系统错误信息
				foreach ($Arr_Title as $key=>$val){
					$Error_TXT .= $key.'：'.$val."\r\n";
				}
			}
		}

		# 最后再写入两个换行符,以便追加查看
		$Error_TXT .= "\r\n\r\n";
		# 最后写入日志
		error_log($Error_TXT, 3, $this->WxConfig['DeBugList'] . $File);
	}

	/*
	 * Title  : 对于微信支付金额 单位是 分 的金额进行小数转换
	 * Author : 小黄牛
	 * Last  : 修改时间 + 修改人
	 * @param array  : $Total_fee  金额
	 * Return : 返回转换后的金额
	*/
	protected function Money($Total_fee){
		if(floor($Total_fee) != $Total_fee){
			$Total_fee = sprintf("%.2f",substr(sprintf("%.3f", $Total_fee), 0, -1));// 只保留2位小数，并且不做四舍五入
			$Total_fee = str_replace('.', '', $Total_fee);// 删除小数点
		}
		return $Total_fee;
	}

}