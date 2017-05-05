微信开发类包
===============================================

小黄牛
-----------------------------------------------

### 1731223728@qq.com 

weixin  类包根目录

├─ cert 微信支付证书存放目录

├

├─ lib 微信支付SDK存放目录，这里的文件都是SDK原始的文件，并没做过任何修改，也不能做任何修改，否则后期很难升级维护

├

├─ demo 测试文件， 里面的文件需要移动到类包根目录，并对WxConfig.php和lib/WxPay.Config文件进行配置添加

├

├─ App_Log 日志文件目录

├─ access_token.json 普通actoken缓存文件

├

├─ Wx.php 微信基类文件

├─ WxConfig.php 微信类包所有公共配置文件

├─ WxPayToken.php 获取普通actoken

├─ WxPayUserBasic.php 用户管理功能汇总 + 网页授权登录

├─ WxPayReplyXml.php 普通微信自动功能汇总 : Token认证、自动回复、自定义菜单、模板信息

├─ WxPayQrcode.php 二维码相关功能(不包括扫描二维码的推送请求)

├─ WxPayJsApi.php 微信支付之 JSAPI支付

├─ WxPayNotify.php 微信支付回调功能 回调页面，直接调用 $this->wxnotif();方法， 需要在WxConfig.php中设置回调参数

├─ WxPayOrderSelect.php 微信订单处理 查询订单 申请退款 退款查询 下载对账单

└─ WxPayLogin.php 微信登录功能汇总
