<?php
/*
 +----------------------------------------------------------------------
 + Title        : 微信公众平台开发,JsApi - 订单处理类
 + Author       : 小黄牛
 + Version      : V1.0.0.1
 + Initial-Time : 2017-02-27 10:14:00
 + Last-time    :
 + List         :
 + Desc         : 本类封装了所有关于订单的操作处理 现有(订单查询、申请退款、退款查询)
 +----------------------------------------------------------------------
*/
# 引入第三方核心基类
require_once('Wx.php');
require_once "lib/WxPay.Api.php";

# 支付订单处理类
class WxOrder extends Wx{

    /*
	 * Title : 订单查询
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
	 * @param string $Transaction_Id ：微信系统订单号
     * @param string $Out_Trade_No ：商户自定义单号
	 * Return: 查询到的订单信息
	*/
    public function OrderSelect($Transaction_Id='', $Out_Trade_No=''){
        # 优先处理 微信系统订单号
        if (!empty($Transaction_Id)){
            $input = new WxPayOrderQuery();
            $input->SetTransaction_id($Transaction_Id);
            return WxPayApi::orderQuery($input);
        }

        # 次级处理 商户自定义单号
        $input = new WxPayOrderQuery();
        $input->SetOut_trade_no($Out_Trade_No);
        return WxPayApi::orderQuery($input);
    }

    /*
	 * Title : 申请退款 - 退款金额不能大于订单总额
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
     * @param string $Total_Fee ：订单总金额
     * @param string $Refund_Fee ：退款金额
	 * @param string $Transaction_Id ：微信系统订单号 二选一 优先
     * @param string $Out_Trade_No ：商户自定义单号 二选一 次级
     * Desc : 如果调用时出现CURL错误， 请参考这个网址进行解决： http://blog.csdn.net/Hiking_Tsang/article/details/52667781
	 * Return: 退款返回值
	*/
    public function OrderRefund($Total_Fee, $Refund_Fee, $Transaction_Id='', $Out_Trade_No=''){
        # 检查金额是否带有小数点 - 是则把小数点去掉
        $Total_Fee  = $this->Money($Total_Fee);
        $Refund_Fee = $this->Money($Refund_Fee);

        # 优先处理 微信系统订单号
        if (!empty($Transaction_Id)){
            $input = new WxPayRefund();
            $input->SetTransaction_id($Transaction_Id);
            $input->SetTotal_fee($Total_Fee);
            $input->SetRefund_fee($Refund_Fee);
            $input->SetOut_refund_no(WxPayConfig::MCHID.date("YmdHis"));
            $input->SetOp_user_id(WxPayConfig::MCHID);
            $arr = WxPayApi::refund($input);
        }else{
            # 次级处理 商户自定义单号
            $input = new WxPayRefund();
            $input->SetOut_trade_no($Out_Trade_No);
            $input->SetTotal_fee($Total_Fee);
            $input->SetRefund_fee($Refund_Fee);
            $input->SetOut_refund_no(WxPayConfig::MCHID.date("YmdHis"));
            $input->SetOp_user_id(WxPayConfig::MCHID);
            $arr = WxPayApi::refund($input);
        }
        # 写入日志
        $this->ErrorLog(array(
            'Title' => '退款申请返回值：',
            'Body'  => json_encode($arr)
        ));
        return $arr;
    }

    /*
	 * Title : 退款查询 - 四个参数只需要填写一个，其余为空即可
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
     * @param string $Refund_Id ：微信退款单号 一级
     * @param string $Transaction_Id ：微信系统订单号 二级
	 * @param string $Out_Trade_No ：商户自定义单号 三级
     * @param string $Out_Refund_No ：商户退款单号 四级
	 * Return: 查询返回值 $data['result_code'] SUCCESS 申请成功
     *         退款状态： $data['refund_status_$n'] $n一般为0 ，SUCCESS|FAIL|PROCESSING|CHANGE    退款成功|退款失败|退款处理中|转入代发
     *         CHANGE|转入代发 退款到银行发现用户的卡作废或者冻结了，导致原路退款银行卡失败，资金回流到商户的现金帐号，需要商户人工干预，通过线下或者财付通转账的方式进行退款。
	*/
    public function RetundSelect($Refund_Id='', $Transaction_Id='', $Out_Trade_No='', $Out_Refund_No=''){
        $input = new WxPayRefundQuery();
        # 微信退款单号 优先
        if (!empty($Refund_Id)){
            $input->SetRefund_id($Refund_Id);
        }else if(!empty($Transaction_Id)){ # 微信订单号 二级
            $input->SetTransaction_id($Transaction_Id);
        }else if(!empty($Out_Trade_No)){# 商户自定义单号 三级
            $input->SetOut_trade_no($Out_Trade_No);
        }else{# 商户退款单号 四级
            $input->SetOut_refund_no($Out_Refund_No);
        }
        $arr = WxPayApi::refundQuery($input);
        # 写入日志
        $this->ErrorLog(array(
            'Title' => '退款查询返回值：',
            'Body'  => json_encode($arr)
        ));
        return $arr;
    }

    /*
	 * Title : 下载对账单
	 * Author: 小黄牛
	 * Last  : 修改时间 + 修改人
     * @param string $Time ：对账单日期时间：精确到日 20170227
     * @param string $Type ：对账单类型：ALL|SUCCESS|REFUND|REVOKED 所有订单|成功支付订单|退款订单|撤销订单
	 * Return: 一个三维数组，top是table的表题值，body是内容值
	*/
    public function OrderDownload($Time, $Type='ALL'){
        # 全转大写
        $Type = strtoupper($Type);
        $input = new WxPayDownloadBill();
        $input->SetBill_date($Time);
        $input->SetBill_type($Type);
        $res = WxPayApi::downloadBill($input);
        $arr = explode(' ', $res);
        # 生成表单 Top值
        $Top = explode(',', array_shift($arr));
        # 生成表单 Body值
        foreach ($arr as $k=>$v){
            $arr[$k] = explode(',', str_replace("`", '', $v));
        }
        $array = array(
            'top'  => $Top,
            'body' => $arr
        );
        return $array;
    }

}