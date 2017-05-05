<?php
/*
 +----------------------------------------------------------------------
 + Title        : 微信公众平台开发,公共配置文件
 + Author       : 小黄牛
 + Version      : V1.0.0.1
 + Initial-Time : 2017-02-24 10:48:00
 + Last-time    : 2017-02-26 10:29:00 + 小黄牛
 + List         :
 + Desc         : 用于存储一切公共配置信息
 +----------------------------------------------------------------------
*/
return array(
    'DeBug'                    => 2,// 日志开关，1|2|3  为1时不追加日志，为2时追加日志，为3时不生成日志
	'DeBugList'                => 'App_Log/',//日志文件保存路径
    # 过滤的字符串里包括了 =和一些特殊字符串如or，特殊情况可去掉
    'SqlString'                => 'or|and|xor|not|select|insert|update|delete|=>|<=|!=|=|%|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile',
    'Token'                    => '',// 基本设置->(令牌)
    'EncodingAESKey'           => '',// 基本设置->(消息加解密密钥)
	'AppId'                    => '',// 微信的appid
    'AppSecret'                => '',// 微信的appsecret
    'AC_Token_Name'            => 'access_token.json',// 普通 actoken保存的缓存文件名
    'AC_Token_Time'            => 6600,// 普通actoken的缓存更新时间，单位秒，默认110分钟，最多不超过120分钟
    'WebMicro_Type'            => 'snsapi_base',// 微信网页授权模式 snsapi_userinfo|snsapi_base   点击授权|静默授权
    'WebMicro_Lang'            => 'zh_CN',// 用户信息返回结果语言版本 zh_CN 简体，zh_TW 繁体，en 英语
    'Template_Id_Short'        => 'TM**',// 默认使用的信息模板编号

	/***************************************************************  微信支付相关  *************************************************************************/
	# 到lib/WxPayConfig.php 文件中进行修改
	'Wx_Notify_Url'   => 'huitiao.php',// 订单回调处理类的地址，绝对路径，在这个类的内部新建一个WxNotify(订单号)方法，用于处理回调业务逻辑，不管如何，这个方法必须返回true|false - 这个地址是相对于回调页面来设置的
	'Wx_Notify_Class' => 'Huitiao',// 订单回调处理类的【类名】，注意：【并不是类的文件名】

    /***************************************************************  所有API请求地址汇总  *************************************************************************/
    # 微信获取 普通 actoken的请求地址
    'AppToken_Url' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s%&secret=%s%',
	# 验证 普通actoken的有效性
	'AppTokenCheck_Url' => 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=%s%',
	# 通过OpenID来获取用户基本信息（包括UnionID机制）
    'AppUserBasic_Url' => 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s%&openid=%s%',
    # 通过OpenID设置用户备注名
    'AppUserRemarks_Url' => 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token=%s%',
    # 通过OpenId获取用户列表
    'AppUserList_Url' => 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=%s%&next_openid=%s%',
    # 点击授权的生成链接 state参数虽然为可选，但还是建议必填
    'Snsapi_Url' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s%&redirect_uri=%s%&response_type=code&scope=%s%&state=%s%#wechat_redirect',
    # 用授权后获得code去换取特殊的Access_Token
    'Snsapi_AcToken_Url' => 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s%&secret=%s%&code=%s%&grant_type=authorization_code',
    # 使用refresh_token更新特殊的Access_Token
    'Snsapi_SaveAcToken_Url' => 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=%s%&grant_type=refresh_token&refresh_token=%s%',
    # 使用特殊的Access_Token去获取用户信息
    'Snsapi_User_Url' => 'https://api.weixin.qq.com/sns/userinfo?access_token=%s%&openid=%s%&lang=%s%',

    /********************************** 微信自定义菜单相关 *******************************/
    # 创建菜单
    'Menu_Add_Url' => 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=%s%',
    # 删除菜单
    'Menu_Del_Url' => 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=%s%',
    # 查询菜单
    'Menu_Get_Url' => 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=%s%',
    # 查询菜单(包括菜单配置)
    'Menu_GetList_Url' => 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=%s%',

    /********************************** 模板信息相关 *******************************/
    # 更改行业
    'Industry_Save_Url' => 'https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token=%s%',
    # 选择模板
    'Industry_Set_Url' => 'https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token=%s%',
    # 发送模板信息
    'Industry_Add_Url' => 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=%s%',

    /********************************** 二维码相关 *******************************/
    # 创建二维码的ticket
    'Qrcode_Add_Url' => 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=%s%',
	# 使用ticket去换取二维码
	'Qrcode_Get_Url' => 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=%s%',

	/******************************* 微信支付相关 ******************************/
	# 扫码模式一 生成二维码地址
	'ScanCode_One_Url' => 'weixin：//wxpay/bizpayurl?sign=%s%&appid=%s%&mch_id=%s%&product_id=%s%&time_stamp=%s%&nonce_str=%%',





    # 微信自定义菜单内容 - 具体参考微信开发手册
    'Menu_List' => '{
     "button":[
     {
          "type":"click",
          "name":"今日歌曲",
          "key":"V1001_TODAY_MUSIC"
      },
      {
           "name":"菜单",
           "sub_button":[
           {
               "type":"view",
               "name":"搜索",
               "url":"http://www.junphp.com/"
            },
            {
               "type":"view",
               "name":"视频",
               "url":"http://v.qq.com/"
            },
            {
               "type":"click",
               "name":"赞一下我们",
               "key":"V1001_GOOD"
            }]
       }]
 }',

);
