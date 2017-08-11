<?php
$result = array(
    'orderAmountPrice' => 0,
    'refundFee'        => 0,
    'orgCountFee'      => 0,
    'countFee'         => 0,
    'platformFee'      => 0,
    'commission'       => 0,
    'commissionPer'    => 0,
    'orderNum'         => count($orderList),
    'order_ids'        => array(),
    'orderNoList'      => array(),
    'shouxufei'        =>0,
    'tichen'           =>0,
    'mjiesuan'         =>0,
);

if($orderList && is_array($orderList))
{
    $refundObj = new IModel("refundment_doc");//退款表
    $propObj   = new IModel("prop");          //道具表
    foreach($orderList as $key => $item)
    {
        //检查平台促销活动
        //1,代金券
        if($item['prop'])
        {
            $propRow = $propObj->getObj('id = '.$item['prop'].' and type = 0');
            if($propRow && $propRow['seller_id'] == 0)
            {
                $propRow['value'] = min($item['real_amount'],$propRow['value']);
                $result['platformFee'] += $propRow['value'];
            }
        }

        $result['orderAmountPrice'] += $item['order_amount'] - $item['pay_fee'];
         
        /*
         * 结算金额
         *
    			 **/
        //手续费
        $result['shouxufei']+= $item['shouxufei'];
         
        //提成（三级提成+销售提成+归属+）
        //$tichen= $item['payable_amount'] - $item['chengben'] - $item['shouxufei'];
        if(empty($item['payable_amount'])){
            $tichen= $item['order_amount'] - $item['chengben'] - $item['shouxufei'];
        }else{
            $tichen= $item['payable_amount'] - $item['chengben'] - $item['shouxufei'];
        }
        $result['tichen']+= $tichen;
        //结算
        $result['mjiesuan']+=$item['chengben'] ;
        $result['order_ids'][]       = $item['id'];
        $result['orderNoList'][]     = $item['order_no'];

        //商品的原价
        //$result['payable_amount']+= $item['payable_amount'];
        if(empty($item['payable_amount'])){
            $result['payable_amount']+= $item['order_amount'];
        }else{
            $result['payable_amount']+= $item['payable_amount'];
        }
         
        //是否存在退款
        $refundList = $refundObj->query("order_id = ".$item['id'].' and pay_status = 2');
        
        foreach($refundList as $k => $val)
        {
            $result['refundFee'] += $val['amount'];
        }
    }
}

//应该结算金额
$result['orgCountFee'] = $result['orderAmountPrice'] - $result['refundFee'] + $result['platformFee'];

//获取结算手续费
$siteConfigData = new Config('site_config');
$result['commissionPer'] = $siteConfigData->commission ? $siteConfigData->commission : 0;
$result['commission']    = round($result['orgCountFee'] * $result['commissionPer']/100,2);

//最终结算金额
$result['countFee'] = $result['orgCountFee'] - $result['commission'];

return $result;
}