<?php

header("Content-type:text/html;charset=utf-8");
require_once 'AppConfig.php';
require_once 'AppUtil.php';
/**
 * @brief 公共模块
 * @class Block
 */
class Block extends IController
{
	public $layout='';

	public function init()
	{

	}

 	/**
	 * @brief Ajax获取规格值
	 */
	function spec_value_list()
	{
		// 获取POST数据
		$spec_id = IFilter::act(IReq::get('id'),'int');

		//初始化spec商品模型规格表类对象
		$specObj = new IModel('spec');
		//根据规格编号 获取规格详细信息
		$specData = $specObj->getObj("id = $spec_id");
		if($specData)
		{
			echo JSON::encode($specData);
		}
		else
		{
			//返回失败标志
			echo '';
		}
	}

	//列出筛选商品
	function goods_list()
	{
		//商品检索条件
		$show_num    = IFilter::act( IReq::get('show_num'),'int');
		$keywords    = IFilter::act( IReq::get('keywords') );
		$cat_id      = IFilter::act( IReq::get('category_id'),'int');
		$min_price   = IFilter::act( IReq::get('min_price'),'float');
		$max_price   = IFilter::act( IReq::get('max_price'),'float');
		$goods_no    = IFilter::act( IReq::get('goods_no'));
		$is_products = IFilter::act( IReq::get('is_products'),'int');
		$seller_id   = IFilter::act( IReq::get('seller_id'),'int');
		$goods_id    = IFilter::act( IReq::get('goods_id'),'int');

		//货号处理 商品货号或者货品货号2种情况
		if($goods_no)
		{
			$productDB   = new IModel('products');
			$productData = $productDB->query("products_no = '".$goods_no."'");
			if($productData)
			{
				foreach($productData as $key => $item)
				{
					$goods_id .= ",".$item['goods_id'];
				}
				//找到货品后清空货号数据
				$goods_no = "";
			}
		}

		//查询条件
		$table_name = 'goods as go';
		$fields     = 'go.id as goods_id,go.name,go.img,go.store_nums,go.goods_no,go.sell_price,go.spec_array';

		$where   = 'go.is_del = 0';
		$where  .= $goods_id  ? ' and go.id          in ('.$goods_id.')' : '';
		$where  .= $seller_id ? ' and go.seller_id    = '.$seller_id     : '';
		$where  .= $goods_no  ? ' and go.goods_no     = "'.$goods_no.'"' : '';
		$where  .= $min_price ? ' and go.sell_price  >= '.$min_price     : '';
		$where  .= $max_price ? ' and go.sell_price  <= '.$max_price     : '';
		$where  .= $keywords  ? ' and go.name like    "%'.$keywords.'%"' : '';

		//分类筛选
		if($cat_id)
		{
			$table_name .= ' ,category_extend as ca ';
			$where      .= " and ca.category_id = {$cat_id} and go.id = ca.goods_id ";
		}

		//获取商品数据
		$goodsDB = new IModel($table_name);
		$data    = $goodsDB->query($where,$fields,'go.id desc',$show_num);

		//包含货品信息
		if($is_products)
		{
			if($data)
			{
				$goodsIdArray = array();
				foreach($data as $key => $val)
				{
					//有规格有货品
					if($val['spec_array'])
					{
						$goodsIdArray[$key] = $val['goods_id'];
						unset($data[$key]);
					}
				}

				if($goodsIdArray)
				{
					$productFields = "pro.goods_id,go.name,go.img,pro.id as product_id,pro.products_no as goods_no,pro.spec_array,pro.sell_price,pro.store_nums";
					$productDB     = new IModel('goods as go,products as pro');
					$productDate   = $productDB->query('go.id = pro.goods_id and pro.goods_id in('.join(',',$goodsIdArray).')',$productFields);
					$data = array_merge($data,$productDate);
				}
			}
		}

		$this->data = $data;
		$this->type = IFilter::act(IReq::get('type'));//页面input的type类型，比如radio，checkbox
		$this->redirect('goods_list');
	}
	/**
	 * @brief 获取地区
	 */
	public function area_child()
	{
		$parent_id = intval(IReq::get("aid"));
		$areaDB    = new IModel('areas');
		$data      = $areaDB->query("parent_id=$parent_id",'*','sort asc');
		echo JSON::encode($data);
	}

    //[公共方法]通过解析products,goods表中的spec_array转化为格式：key:规格名称;value:规格值
    public static function show_spec($specJson,$param = array())
    {
    	$specArray = JSON::decode($specJson);
    	$spec      = array();
    	if($specArray)
    	{
    		$imgSize = isset($param['size']) ? $param['size'] : 20;
	    	foreach($specArray as $val)
	    	{
	    		//goods表规格数据
	    		if(is_array($val['value']))
	    		{
	    			foreach($val['value'] as $tip => $sval)
	    			{
	    				if(!isset($spec[$val['name']]))
	    				{
	    					$spec[$val['name']] = array();
	    				}

	    				list($tip,$specVal) = each($sval);

			    		if($val['type'] == 1)
			    		{
			    			$spec[$val['name']][] = $specVal;
			    		}
			    		else
			    		{
			    			$spec[$val['name']][] = '<img src="'.IUrl::creatUrl().$specVal.'" style="border: 1px solid #ddd;width:'.$imgSize.'px;height:'.$imgSize.'px;" title="'.$tip.'" />';
			    		}
	    			}
	    			$spec[$val['name']] = join("&nbsp;&nbsp;",$spec[$val['name']]);
	    		}
	    		//goods表老版本格式逗号分隔符
	    		else if(strpos($val['value'],",") && $val['value'] = explode(",",$val['value']))
	    		{
	    			foreach($val['value'] as $tip => $sval)
	    			{
	    				if(!isset($spec[$val['name']]))
	    				{
	    					$spec[$val['name']] = array();
	    				}

			    		if($val['type'] == 1)
			    		{
			    			$spec[$val['name']][] = $sval;
			    		}
			    		else
			    		{
			    			$spec[$val['name']][] = '<img src="'.IUrl::creatUrl().$sval.'" style="border: 1px solid #ddd;width:'.$imgSize.'px;height:'.$imgSize.'px;" />';
			    		}
	    			}
	    			$spec[$val['name']] = join("&nbsp;&nbsp;",$spec[$val['name']]);
	    		}
	    		//products表规格数据
	    		else
	    		{
		    		if($val['type'] == 1)
		    		{
		    			$spec[$val['name']] = $val['value'];
		    		}
		    		else
		    		{
		    			$tip = isset($val['tip']) ? $val['tip'] : "";
		    			$spec[$val['name']] = '<img src="'.IUrl::creatUrl().$val['value'].'" style="border: 1px solid #ddd;width:'.$imgSize.'px;height:'.$imgSize.'px;" title="'.$tip.'" />';
		    		}
	    		}
	    	}
    	}
    	return $spec;
    }
	/**
	 * @brief 获得配送方式ajax
	 */
	public function order_delivery()
    {
    	$productId    = IFilter::act(IReq::get("productId"),'int');
    	$goodsId      = IFilter::act(IReq::get("goodsId"),'int');
    	$province     = IFilter::act(IReq::get("province"),'int');
    	$distribution = IFilter::act(IReq::get("distribution"),'int');
    	$isjifen      = IFilter::act(IReq::get("isjifen"),'int');
    	$num          = IReq::get("num") ? IFilter::act(IReq::get("num"),'int') : 1;
		$data         = array();
		
		
		if($distribution)
		{
		   // die("1");
			$data = Delivery::getDelivery($province,$distribution,$goodsId,$productId,$num);
		}
		else
		{
		   
			$delivery     = new IModel('delivery');
			if($isjifen == 1){
			    
    			$deliveryList = $delivery->query('is_delete = 0 and status = 1 and id = 1');
			}else{
			    $deliveryList = $delivery->query('is_delete = 0 and status = 1');
			}
			foreach($deliveryList as $key => $item)
			{
				$data[$item['id']] = Delivery::getDelivery($province,$item['id'],$goodsId,$productId,$num);
			}
		}
    	echo JSON::encode($data);
    }
	/**
    * @brief 【重要】进行支付支付方法
    */
	public function doPay()
	{
		//获得相关参数
		$order_id   = IReq::get('order_id');
		$recharge   = IReq::get('recharge');
		$payment_id = IFilter::act(IReq::get('payment_id'),'int');
        $order_no   = IReq::get('order_no');
        $xiaoshou_type   = IReq::get('xiaoshouType');
        
       // echo "order id is ".$order_id;  //$order_id  $order_no
       // echo "order_no".$order_no;
       // die;
        
        
		if($order_no)
		{
		   
		    //获取订单信息
		    $vr_orderDB  = new IModel('vr_order');
		    $vr_orderRow = $vr_orderDB->getObj('order_no = '.$order_no);
		
		    if(empty($vr_orderRow))
		    {
		        IError::show(403,'要支付的订单信息不存在2');
		    }
		    $payment_id = $vr_orderRow['pay_type'];
		    
		    $wechatPayData = array();
		}elseif($xiaoshou_type == 0){
		    $order_id = explode("_",IReq::get('order_id'));
		    $order_id = IFilter::act($order_id,'int');
		   
		    //获取订单信息
		    $orderDB  = new IModel('order');
		    $orderRow = $orderDB->getObj('id = '.current($order_id));
		    
		    if(empty($orderRow))
		    {
		        IError::show(403,'要支付的订单信息不存在1');
		    }
		    $payment_id = $orderRow['pay_type'];
		}elseif ($xiaoshou_type == 1){
		    $order_id = explode("_",IReq::get('order_id'));
		    $order_id = IFilter::act($order_id,'int');
		     
		    //获取订单信息
		    $orderDB  = new IModel('supplier');
		    $orderRow = $orderDB->getObj('id = '.current($order_id));
		    
		    if(empty($orderRow))
		    {
		        IError::show(403,'要支付的订单信息不存在3');
		    }
		    $payment_id = $orderRow['pay_type'];
		}
		
		//获取支付方式类库
		$paymentInstance = Payment::createPaymentInstance($payment_id);
		
		//在线充值
		if($recharge !== null)
		{
		    //die("in在线充值");
			$recharge   = IFilter::act($recharge,'float');
			$paymentRow = Payment::getPaymentById($payment_id);

			//account:充值金额; paymentName:支付方式名字
			$reData   = array('account' => $recharge , 'paymentName' => $paymentRow['name']);
			$sendData = $paymentInstance->getSendData(Payment::getPaymentInfo($payment_id,'recharge',$reData));
		}
		else if ($order_no){ 
		   // die("in ve_order");
		    $order_no = explode("_",IReq::get('order_no'));
		    $order_no = IFilter::act($order_no);
		    
    		if($payment_id == 11){
    		    $payData = Payment::getPaymentInfo($payment_id,'vr_order',$order_no);
    		    $this->wechatPay($payData);
    		}else if($payment_id == 12){
    		    $payData = Payment::getPaymentInfo($payment_id,'vr_order',$order_no);
    		    $this->scan_wechatPay($payData);
    		}else{
    		    $sendData = $paymentInstance->getSendData(Payment::getPaymentInfo($payment_id,'vr_order',$order_no));
    		}
		}
		//订单支付
		else if($order_id && $xiaoshou_type==0)
		{
		    if($payment_id == 11){
		        $payData = Payment::getPaymentInfo($payment_id,'order',$order_id);
		        $this->wechatPay($payData);
		    }else if($payment_id == 12){
    		     $payData = Payment::getPaymentInfo($payment_id,'order',$order_id);
    		    $this->scan_wechatPay($payData);
    		}else{
		        $sendData = $paymentInstance->getSendData(Payment::getPaymentInfo($payment_id,'order',$order_id));
		    }
		}
		else if($order_id && $xiaoshou_type==1)
		{
		    if($payment_id == 11){
		        $payData = Payment::getPaymentInfo($payment_id,'supplier',$order_id);
		        $this->wechatPay($payData);
		    }else if($payment_id == 12){
    		    $payData = Payment::getPaymentInfo($payment_id,'supplier',$order_id);
    		    $this->scan_wechatPay($payData);
    		}else{
		        $sendData = $paymentInstance->getSendData(Payment::getPaymentInfo($payment_id,'supplier',$order_id));
		    }
		}
		//else
		//{
		//    $paymentInstance->doPay($sendData);
		    //IError::show(403,'发生支付错误');
		//}
		if($payment_id != 11 && $payment_id != 12){
  		    $paymentInstance->doPay($sendData);
		}
	}
	
	//微信扫码支付成功返回post数据接收方法
	function wechatPayPost(){
	    //file_put_contents("./aaaaa.txt",$_POST['cusorderid']);
	    $payment_id      = IFilter::act(IReq::get('_id'),'int');
	    if ($payment_id){
	        $orderNo = IFilter::act(IReq::get('orderNo'));
	        $postData = array(
	            'orderNO'=>$orderNo,
	            'payment_id'=>$payment_id,
	        );
	    }else{
	        $postData = array(
	            'orderNO'=>$_POST['cusorderid'],
	            'payment_id'=>12,
	        );
	    }
	    $this->tonglian_callback($postData);
	}
	
	//微信扫码支付
	function scan_wechatPay($payData){
	     
	    $params = array();
	    $params["cusid"] = AppConfig::CUSID;//商户号
	    $params["appid"] = AppConfig::APPID;//商户后台申请的appid
	    $params["version"] = AppConfig::APIVERSION;//版本号
	    $params["trxamt"] = (string)$payData['M_Amount']*100;//价钱，单位“分”
	    $params["reqsn"] = (string)$payData['M_BatchOrderNO'];//订单号,自行生成
	    $params["paytype"] = "W01";
	    $params["randomstr"] = rand(10000,99999);//生成5位随机整数
	    $params["body"] = (string)$payData['M_goodsName'];//商品名称
	    $params["remark"] = "";//备注信息
	    $params["limit_pay"] = "no_credit";
	    $params["notify_url"] = "http://www.micarline.net/block/wechatPayPost";//微信支付成功返回方法
	    $params["sign"] = AppUtil::SignArray($params,AppConfig::APPKEY);//签名
	     
	    $paramsStr = AppUtil::ToUrlParams($params);
	    $url = AppConfig::APIURL . "/pay";
	    $rsp = $this->request($url, $paramsStr);
	    //echo "请求返回:".$rsp;
	    //echo "<br/>";
	    $rspArray = json_decode($rsp, true);
	    if($this->validSign($rspArray)){
	        //echo "验签正确,进行业务处理";
	        //echo $rspArray['payinfo'];
	        //跳转到扫码页面
            $sendData = array();
	        $sendData['code_img'] = "http://qr.liantu.com/api.php?text=".$rspArray['payinfo'];
	        $sendData['amount']   = $payData['M_Amount'];
	        $sendData['url'] = "http://www.micarline.net/ucenter/order";
// 	        include('E:\AppServ\www\git\micarshop\plugins\payments\pay_scan_wechat\template\pay.php');
	        include('/www/web/micarline/public_html/plugins/payments/pay_scan_wechat/template/pay.php');
	    }
	
	}
	
	//手机公众号微信支付
	function wechatPay($payData){
	    
	    $memberDB   = new IModel('oauth_user');
	    $memberList = $memberDB->query('user_id = '.$payData['P_BuyerId']);
	    $params = array();
	    $params["cusid"] = AppConfig::CUSID;
	    $params["appid"] = AppConfig::APPID;
	    $params["version"] = AppConfig::APIVERSION;
	    $params["trxamt"] = (string)$payData['M_Amount']*100;//价钱，单位“分”
	    $params["reqsn"] = (string)$payData['M_BatchOrderNO'];//订单号,自行生成
	    $params["paytype"] = "W02";
	    $params["randomstr"] = rand(10000,99999);//生成5位随机整数
	    $params["body"] = (string)$payData['M_goodsName'];//商品名称
	    $params["remark"] = "";//备注信息
	    $params["acct"] = (string)$memberList[0]['oauth_user_id'];;//openid
	    $params["limit_pay"] = "no_credit";
	    $params["notify_url"] = "http://www.micarline.net/block/wechatPayPost";//微信支付成功返回方法
	    $params["sign"] = AppUtil::SignArray($params,AppConfig::APPKEY);//签名
	    
	    $paramsStr = AppUtil::ToUrlParams($params);
	    $url = AppConfig::APIURL . "/pay";
	    $rsp = $this->request($url, $paramsStr);
	    //echo "请求返回:".$rsp;
	    //echo "<br/>";
	    $rspArray = json_decode($rsp, true);
	    if($this->validSign($rspArray)){
	        //echo "验签正确,进行业务处理";
	        //echo $rspArray['payinfo'];
	        $payinfo = json_decode($rspArray['payinfo'],TRUE);
	        ISession::set('timeStamp',$payinfo['timeStamp']);
	        ISession::set('package',$payinfo['package']);
	        ISession::set('nonceStr',$payinfo['nonceStr']);
	        ISession::set('paySign',$payinfo['paySign']);
	        ISession::set('orderNo',$payData['M_BatchOrderNO']);
	        $this->redirect('/simple/wechatPay');
	    }
	     
	}

	//发送请求操作仅供参考,不为最佳实践
	function request($url,$params){
	    $ch = curl_init();
	    $this_header = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
	    curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
	    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	    	
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//如果不加验证,就设false,商户自行处理
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	    	
	    $output = curl_exec($ch);
	    curl_close($ch);
	    return  $output;
	}
	
	//验签
	function validSign($array){
	    if("SUCCESS"==$array["retcode"]){
	        $signRsp = strtolower($array["sign"]);
	        $array["sign"] = "";
	        $sign =  strtolower(AppUtil::SignArray($array, AppConfig::APPKEY));
	        if($sign==$signRsp){
	            return TRUE;
	        }
	        else {
	            echo "验签失败:".$signRsp."--".$sign;
	        }
	    }
	    else{
	        echo $array["retmsg"];
	    }
	
	    return FALSE;
	}
	
	/**
     * @brief 【重要】支付回调[同步](通联支付)
	 */
	public function tonglian_callback($postData)
	{
		
		$payment_id = $postData['payment_id'];
		//从URL中获取支付方式
		//$payment_id      = IFilter::act(IReq::get('_id'),'int');
		
		//file_put_contents("./aaaaa.txt",$postData['orderNO']);
		$paymentInstance = Payment::createPaymentInstance($payment_id);
		
		if(!is_object($paymentInstance))
		{
			IError::show(403,'支付方式不存在');
		}

		//初始化参数
		$money   = '';
		$message = '支付失败';
		$orderNo = '';
        if($payment_id == 11 || $payment_id == 12){
    		$return = 1;
    		//$orderNo = IFilter::act(IReq::get('orderNo'));
    		$orderNo = $postData['orderNO'];
        }else{
            //执行接口回调函数
    		$callbackData = array_merge($_POST,$_GET);
    		unset($callbackData['controller']);
    		unset($callbackData['action']);
    		unset($callbackData['_id']);
    		$return = $paymentInstance->callback($callbackData,$payment_id,$money,$message,$orderNo);
        }
		//支付成功
		if($return == 1)
		{
		  //  echo "1";
			//充值方式
			if(stripos($orderNo,'recharge') !== false)
			{
			  //  echo "2";
				$tradenoArray = explode('recharge',$orderNo);
				$recharge_no  = isset($tradenoArray[1]) ? $tradenoArray[1] : 0;
				if(payment::updateRecharge($recharge_no))
				{
				    //echo "3";
					$this->redirect('/site/success/message/'.urlencode("充值成功").'/?callback=/ucenter/account_log');
					return;
				}
			//	echo "4";
				IError::show(403,'充值失败');
			}
			else
			{
			 //   echo "5";
				//订单批量结算缓存机制
				$moreOrder = Order_Class::getBatch($orderNo);
				if($money >= array_sum($moreOrder)  || $payment_id==11 || $payment_id==12)
				{
				//    echo "6";
					foreach($moreOrder as $key => $item)
					{
						$order_id = Order_Class::updateOrderStatus($key);
						if(!$order_id)
						{
						    $vr_order_id = Order_Class::updateVrOrderStatus($key);
						    if(!$vr_order_id){
						        $supplier_order_id = Order_Class::updateSupplierOrderStatus($key);
						        if (!$supplier_order_id){
						            IError::show(403,'订单修改失败');
						        }
						    }
						}
					}
					if($vr_order_id){
    					$vrorderObj  = new IModel('vr_order');
    					$orderinfo   = $vrorderObj->getObj('order_no ='.$orderNo);
    					$vrorderObj->setData(array('status' => 2,"pay_status" => 1));
    					$vrorderObj->update('order_no = '.$orderNo);
    					if(!empty($orderinfo)){   
    					    $code_list = $this->_makeVrCode(rand(100,999), $orderinfo['seller_id'], $orderinfo['buyer_id'], $orderinfo['count']);
    					    for($a=0;$a<count($code_list);$a++){
        					    $dataArray = array(
        					        'order_no'   => $orderinfo['order_no'],
        					        'store_id'   => $orderinfo['seller_id'],
        					        'buyer_id'   => $orderinfo['buyer_id'],
        					        'vr_code'    => $code_list[$a],
        					        'pay_price'  => $orderinfo['order_amount'],
        					        'create_time'=> ITime::getDateTime(),
        					    );
        					    //生成订单插入vr_order_code表中
        					    
        					    $order_codeObj  = new IModel('vr_order_code');
        					    $order_codeObj->setData($dataArray);
        					    $is_success = $order_codeObj->add();
        					    //echo "--------------------";
        					    //print_r($dataArray);
        					    if(!$is_success){
        					        IError::show(403,'生成兑换码失败');
        					    }
    					    }
    					    //die;
    					}
					}
					
					//到店 且 积分 
					if($vr_order_id){
					    
					    //echo "vr_order is".$vr_order_id;
					    $vr_order = new IModel('vr_order');
					    $vr_order_data = $vr_order->getObj('order_no ='.$orderNo);
					    $pay_status = $vr_order_data['status'];
					    $isjifen = $vr_order_data['isjifen'];
					   
					     //付款成功 且 积分
					    if($pay_status == 2 && $isjifen == 1){
					        //获取买家信息
					        $usermember = new IModel('member');
					        $userId = $vr_order_data['buyer_id'];
					        $userdata = $usermember->getObj('user_id='.$userId);
					        
					        //更新买家积分
					        $duihuanpoint = $vr_order_data['duihuanpoint'];
					        $data = array(
					            "point"=>$userdata['point'] - $duihuanpoint
					        );
					        $usermember->setData($data);
					        $usermember->update('user_id ='.$userId);
					    }
					    $this->redirect('/site/success/message/'.urlencode("支付成功").'/?callback=/ucenter/order');
					    return;
					}
					
					//实物
					if($order_id){
					    
					    $order = new IModel('order');
					    $order_data = $order->getObj('id='.$order_id);
					    $pay_status = $order_data['status'];
					    $isjifen = $order_data['isjifen'];
					    
					    //付款成功 且 积分
					    if($pay_status == 2 && $isjifen ==1){
					        //获取买家信息
					        $usermember = new IModel('member');
					        $userId = $order_data['user_id'];
					        $userdata = $usermember->getObj('user_id='.$userId);
					        
					        
					        //更新买家积分
					        $duihuanpoint = $order_data['duihuanpoint'];
					        $data = array(
					            "point"=>$userdata['point'] - $duihuanpoint
					        );
					        $usermember->setData($data);
					        $usermember->update('user_id ='.$userId);
					    }
					    
					    $this->redirect('/site/success/message/'.urlencode("支付成功").'/?callback=/ucenter/order');
					    return;
					}
					
					//供应商
					if($supplier_order_id){
					    	
					    $order = new IModel('supplier');
					    $order_data = $order->getObj('order_no='.$orderNo);
					    $pay_status = $order_data['status'];
					    	
					    //付款成功 且 积分
					    if($pay_status == 2 && $isjifen ==1){
					        //获取买家信息
					        $usermember = new IModel('member');
					        $userId = $order_data['user_id'];
					        $userdata = $usermember->getObj('user_id='.$userId);
					         
					        //更新买家积分
// 					        $duihuanpoint = $order_data['duihuanpoint'];
// 					        $data = array(
// 					            "point"=>$userdata['point'] - $duihuanpoint
// 					        );
// 					        $usermember->setData($data);
// 					        $usermember->update('user_id ='.$userId);
					    }
					    	
					    $this->redirect('/site/success/message/'.urlencode("支付成功").'/?callback=/ucenter/order');
					    return;
					}
				}
				$message = '付款金额与订单金额不符合';
			}
		}
		//支付失败
		$message = $message ? $message : '支付失败';
		IError::show(403,$message);
	}

	/**
	 * @brief 【重要】支付回调[同步](预存款)
	 */
	public function callback()
	{
	
	    //$payment_id = $postData['payment_id'];
	    //从URL中获取支付方式
	    $payment_id      = IFilter::act(IReq::get('_id'),'int');
	
	    //file_put_contents("./aaaaa.txt",$postData['orderNO']);
	    $paymentInstance = Payment::createPaymentInstance($payment_id);
	
	    if(!is_object($paymentInstance))
	    {
	        IError::show(403,'支付方式不存在');
	    }
	
	    //初始化参数
	    $money   = '';
	    $message = '支付失败';
	    $orderNo = '';
	    if($payment_id == 11 || $payment_id == 12){
	        $return = 1;
	        //$orderNo = IFilter::act(IReq::get('orderNo'));
	        $orderNo = $postData['orderNO'];
	    }else{
	        //执行接口回调函数
	        $callbackData = array_merge($_POST,$_GET);
	        unset($callbackData['controller']);
	        unset($callbackData['action']);
	        unset($callbackData['_id']);
	        $return = $paymentInstance->callback($callbackData,$payment_id,$money,$message,$orderNo);
	    }
	    //支付成功
	    if($return == 1)
	    {
	        //  echo "1";
	        //充值方式
	        if(stripos($orderNo,'recharge') !== false)
	        {
	            //  echo "2";
	            $tradenoArray = explode('recharge',$orderNo);
	            $recharge_no  = isset($tradenoArray[1]) ? $tradenoArray[1] : 0;
	            if(payment::updateRecharge($recharge_no))
	            {
	                //echo "3";
	                $this->redirect('/site/success/message/'.urlencode("充值成功").'/?callback=/ucenter/account_log');
	                return;
	            }
	            //	echo "4";
	            IError::show(403,'充值失败');
	        }
	        else
	        {
	            //   echo "5";
	            //订单批量结算缓存机制
	            $moreOrder = Order_Class::getBatch($orderNo);
	            if($money >= array_sum($moreOrder)  || $payment_id==11 || $payment_id==12)
	            {
	                //    echo "6";
	                foreach($moreOrder as $key => $item)
	                {
	                    $order_id = Order_Class::updateOrderStatus($key);
	                    if(!$order_id)
	                    {
	                        $vr_order_id = Order_Class::updateVrOrderStatus($key);
	                        if(!$vr_order_id){
	                            $supplier_order_id = Order_Class::updateSupplierOrderStatus($key);
	                            if (!$supplier_order_id){
	                                IError::show(403,'订单修改失败');
	                            }
	                        }
	                    }
	                }
	                if($vr_order_id){
	                    $vrorderObj  = new IModel('vr_order');
	                    $orderinfo   = $vrorderObj->getObj('order_no ='.$orderNo);
	                    $vrorderObj->setData(array('status' => 2,"pay_status" => 1));
	                    $vrorderObj->update('order_no = '.$orderNo);
	                    if(!empty($orderinfo)){
	                        $code_list = $this->_makeVrCode(rand(100,999), $orderinfo['seller_id'], $orderinfo['buyer_id'], $orderinfo['count']);
	                        for($a=0;$a<count($code_list);$a++){
	                            $dataArray = array(
	                                'order_no'   => $orderinfo['order_no'],
	                                'store_id'   => $orderinfo['seller_id'],
	                                'buyer_id'   => $orderinfo['buyer_id'],
	                                'vr_code'    => $code_list[$a],
	                                'pay_price'  => $orderinfo['order_amount'],
	                                'create_time'=> ITime::getDateTime(),
	                            );
	                            //生成订单插入vr_order_code表中
	                            	
	                            $order_codeObj  = new IModel('vr_order_code');
	                            $order_codeObj->setData($dataArray);
	                            $is_success = $order_codeObj->add();
	                            //echo "--------------------";
	                            //print_r($dataArray);
	                            if(!$is_success){
	                                IError::show(403,'生成兑换码失败');
	                            }
	                        }
	                        //die;
	                    }
	                }
	                	
	                //到店 且 积分
	                if($vr_order_id){
	                    	
	                    //echo "vr_order is".$vr_order_id;
	                    $vr_order = new IModel('vr_order');
	                    $vr_order_data = $vr_order->getObj('order_no ='.$orderNo);
	                    $pay_status = $vr_order_data['status'];
	                    $isjifen = $vr_order_data['isjifen'];
	
	                    //付款成功 且 积分
	                    if($pay_status == 2 && $isjifen == 1){
	                        //获取买家信息
	                        $usermember = new IModel('member');
	                        $userId = $vr_order_data['buyer_id'];
	                        $userdata = $usermember->getObj('user_id='.$userId);
	                         
	                        //更新买家积分
	                        $duihuanpoint = $vr_order_data['duihuanpoint'];
	                        $data = array(
	                            "point"=>$userdata['point'] - $duihuanpoint
	                        );
	                        $usermember->setData($data);
	                        $usermember->update('user_id ='.$userId);
	                    }
	                    $this->redirect('/site/success/message/'.urlencode("支付成功").'/?callback=/ucenter/order');
	                    return;
	                }
	                	
	                //实物
	                if($order_id){
	                    	
	                    $order = new IModel('order');
	                    $order_data = $order->getObj('id='.$order_id);
	                    $pay_status = $order_data['status'];
	                    $isjifen = $order_data['isjifen'];
	                    	
	                    //付款成功 且 积分
	                    if($pay_status == 2 && $isjifen ==1){
	                        //获取买家信息
	                        $usermember = new IModel('member');
	                        $userId = $order_data['user_id'];
	                        $userdata = $usermember->getObj('user_id='.$userId);
	                         
	                         
	                        //更新买家积分
	                        $duihuanpoint = $order_data['duihuanpoint'];
	                        $data = array(
	                            "point"=>$userdata['point'] - $duihuanpoint
	                        );
	                        $usermember->setData($data);
	                        $usermember->update('user_id ='.$userId);
	                    }
	                    	
	                    $this->redirect('/site/success/message/'.urlencode("支付成功").'/?callback=/ucenter/order');
	                    return;
	                }
	                	
	                //供应商
	                if($supplier_order_id){
	
	                    $order = new IModel('supplier');
	                    $order_data = $order->getObj('order_no='.$orderNo);
	                    $pay_status = $order_data['status'];
	
	                    //付款成功 且 积分
	                    if($pay_status == 2 && $isjifen ==1){
	                        //获取买家信息
	                        $usermember = new IModel('member');
	                        $userId = $order_data['user_id'];
	                        $userdata = $usermember->getObj('user_id='.$userId);
	                    }
	
	                    $this->redirect('/site/success/message/'.urlencode("支付成功").'/?callback=/ucenter/order');
	                    return;
	                }
	            }
	            $message = '付款金额与订单金额不符合';
	        }
	    }
	    //支付失败
	    $message = $message ? $message : '支付失败';
	    IError::show(403,$message);
	}
	
	/**
	 * 生成兑换码
	 * 长度 =3位 + 4位 + 2位 + 3位  + 1位 + 5位随机  = 18位
	 * @param string $perfix 前缀
	 * @param int $store_id
	 * @param int $member_id
	 * @param unknown $num
	 * @return multitype:string
	 */
	private function _makeVrCode($perfix, $store_id, $member_id, $num) {
	    $perfix .= sprintf('%04d', (int) $store_id * $member_id % 10000)
	    . sprintf('%02d', (int) $member_id % 100)
	    . sprintf('%03d', (float) microtime() * 1000);
	    $code_list = array();
	    for ($i = 0; $i < $num; $i++) {
	        $suiji_num = $this->random(5,1);
	        $code_list[$i] = $perfix. sprintf('%01d', (int) $i % 10) . $suiji_num;
	    }
	    return $code_list;
	}
	
	/**
	 * 取得随机数
	 *
	 * @param int $length 生成随机数的长度
	 * @param int $numeric 是否只产生数字随机数 1是0否
	 * @return string
	 */
	function random($length, $numeric = 0) {
	    $seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	    $seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	    $hash = '';
	    $max = strlen($seed) - 1;
	    for($i = 0; $i < $length; $i++) {
	        $hash .= $seed{mt_rand(0, $max)};
	    }
	    return $hash;
	}
	
	/**
     * @brief 【重要】支付回调[异步]
	 */
	function server_callback()
	{
		//从URL中获取支付方式
		$payment_id      = IFilter::act(IReq::get('_id'),'int');
		$paymentInstance = Payment::createPaymentInstance($payment_id);

		if(!is_object($paymentInstance))
		{
			die('fail');
		}

		//初始化参数
		$money   = '';
		$message = '支付失败';
		$orderNo = '';

		//执行接口回调函数
		$callbackData = array_merge($_POST,$_GET);
		unset($callbackData['controller']);
		unset($callbackData['action']);
		unset($callbackData['_id']);
		$return = $paymentInstance->serverCallback($callbackData,$payment_id,$money,$message,$orderNo);

		//支付成功
		if($return == 1)
		{
			//充值方式
			if(stripos($orderNo,'recharge') !== false)
			{
				$tradenoArray = explode('recharge',$orderNo);
				$recharge_no  = isset($tradenoArray[1]) ? $tradenoArray[1] : 0;
				if(payment::updateRecharge($recharge_no))
				{
					$paymentInstance->notifyStop();
				}
			}
			else
			{
				//订单批量结算缓存机制
				$moreOrder = Order_Class::getBatch($orderNo);
				if($money >= array_sum($moreOrder))
				{
					foreach($moreOrder as $key => $item)
					{
						$order_id = Order_Class::updateOrderStatus($key);
						if(!$order_id)
						{
							throw new IException("异步支付回调修改状态错误，订单ID：".$order_id);
						}
					}
					$paymentInstance->notifyStop();
				}
			}
		}
		//支付失败
		else
		{
			die('fail');
		}
	}

	/**
     * @brief 【重要】支付中断处理
	 */
	public function merchant_callback()
	{
		$this->redirect('/site/index');
	}

	/**
    * @brief 根据省份名称查询相应的province
    */
	public function searchProvince()
	{
		$province = IFilter::act(IReq::get('province'));

		$tb_areas = new IModel('areas');
		$areas_info = $tb_areas->getObj('parent_id = 0 and area_name like "%'.$province.'%"','area_id');
		$result = array('flag' => 'fail','area_id' => 0);
		if($areas_info)
		{
			$result = array('flag' => 'success','area_id' => $areas_info['area_id']);
		}
		echo JSON::encode($result);
	}
    //添加实体代金券
    function add_download_ticket()
    {
    	$isError = true;

    	$ticket_num = IFilter::act(IReq::get('ticket_num'));
    	$ticket_pwd = IFilter::act(IReq::get('ticket_pwd'));

		//代金券状态是否正常
    	$propObj = new IModel('prop');
    	$propRow = $propObj->getObj('card_name = "'.$ticket_num.'" and card_pwd = "'.$ticket_pwd.'" and type = 0 and is_userd = 0 and is_send = 1 and is_close = 0 and NOW() between start_time and end_time');
    	if(!$propRow)
    	{
    		$message = '代金券不可用，请确认代金券的卡号密码并且此代金券从未被使用过';
	    	$result = array(
	    		'isError' => $isError,
	    		'message' => $message,
	    	);
	    	die(JSON::encode($result));
    	}

    	//代金券是否已经被领取
    	$memberObj = new IModel('member');
		$isRev     = $memberObj->query('FIND_IN_SET('.$propRow['id'].',prop)');
		if($isRev)
		{
    		$message = '代金券已经被领取';
	    	$result = array(
	    		'isError' => $isError,
	    		'message' => $message,
	    	);
	    	die(JSON::encode($result));
		}

		//登录用户
		$isError = false;
		$message = '添加成功';
		if($this->user['user_id'])
		{
    		$memberRow = $memberObj->getObj('user_id = '.$this->user['user_id'],'prop');
    		if($memberRow['prop'] == '')
    		{
    			$propUpdate = ','.$propRow['id'].',';
    		}
    		else
    		{
    			$propUpdate = $memberRow['prop'].$propRow['id'].',';
    		}

    		$dataArray = array('prop' => $propUpdate);
    		$memberObj->setData($dataArray);
    		$memberObj->update('user_id = '.$this->user['user_id']);
		}
		//游客方式
		else
		{
			ISafe::set("ticket_".$propRow['id'],$propRow['id']);
		}

    	$result = array(
    		'isError' => $isError,
    		'data'    => $propRow,
    		'message' => $message,
    	);

    	die(JSON::encode($result));
    }

	private function alert($msg)
	{
		header('Content-type: text/html; charset=UTF-8');
		echo JSON::encode(array('error' => 1, 'message' => $msg));
		exit;
	}
    /**
     * 筛选用户
     */
    public function filter_user()
    {
		$where   = array();
		$userIds = '';
    	$search  = IFilter::act(IReq::get('search'),'strict');
    	$search  = $search ? $search : array();

    	foreach($search as $key => $val)
    	{
    		if($val)
    		{
    			$where[] = $key.'"'.$val.'"';
    		}
    	}

    	//有筛选条件
    	if($where)
    	{
	    	$userDB = new IQuery('user as u');
	    	$userDB->join  = 'left join member as m on u.id = m.user_id';
	    	$userDB->fields= 'u.id';
	    	$userDB->where = join(" and ",$where);
	    	$userData      = $userDB->find();
	    	$tempArray     = array();
	    	foreach($userData as $key => $item)
	    	{
	    		$tempArray[] = $item['id'];
	    	}
	    	$userIds = join(',',$tempArray);

	    	if(!$userIds)
	    	{
	    		die('<script type="text/javascript">alert("没有找到用户信息,请重新输入条件");window.history.go(-1);</script>');
	    	}
    	}

    	die('<script type="text/javascript">parent.searchUserCallback("'.$userIds.'");</script>');
    }
	/*
	 * @breif ajax添加商品扩展属性
	 * */
	function attribute_init()
	{
		$id = IFilter::act(IReq::get('model_id'),'int');
		$tb_attribute = new IModel('attribute');
		$attribute_info = $tb_attribute->query('model_id='.$id);
		echo JSON::encode($attribute_info);
	}

	//获取商品数据
	public function getGoodsData()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		$productDB = new IQuery('products as p');
		$productDB->join  = 'left join goods as go on go.id = p.goods_id';
		$productDB->where = 'go.id = '.$id;
		$productDB->fields= 'p.*,go.name';
		$productData = $productDB->find();

		if(!$productData)
		{
			$goodsDB   = new IModel('goods');
			$productData = $goodsDB->query('id = '.$id);
		}
		echo JSON::encode($productData);
	}

	//获取商品的推荐标签数据
	public function goodsCommend()
	{
		//商品字符串的逗号间隔
		$id = IFilter::act(IReq::get('id'));
		if($id)
		{
			$idArray = explode(",",$id);
			$idArray = IFilter::act($idArray,'int');
			$id = join(',',$idArray);
		}

		$goodsDB = new IModel('goods');
		$goodsData = $goodsDB->query("id in (".$id.")","id,name");

		$goodsCommendDB = new IModel('commend_goods');
		foreach($goodsData as $key => $val)
		{
			$goodsCommendData = $goodsCommendDB->query("goods_id = ".$val['id']);
			foreach($goodsCommendData as $k => $v)
			{
				$goodsData[$key]['commend'][$v['commend_id']] = 1;
			}
		}
		die(JSON::encode($goodsData));
	}

	//获取自提点数据
	public function getTakeselfList()
	{
		$id   = IFilter::act(IReq::get('id'),'int');
		$type = IFilter::act(IReq::get('type'));
		$takeselfObj = new IQuery('takeself');

		switch($type)
		{
			case "province":
			{
				$where = "province = ".$id;
				$takeselfObj->group = 'city';
			}
			break;

			case "city":
			{
				$where = "city = ".$id;
				$takeselfObj->group = 'area';
			}
			break;

			case "area":
			{
				$where = "area = ".$id;
			}
			break;
		}

		$takeselfObj->where = $where;
		$takeselfList = $takeselfObj->find();

		foreach($takeselfList as $key => $val)
		{
			$temp = area::name($val['province'],$val['city'],$val['area']);
			$takeselfList[$key]['province_str'] = $temp[$val['province']];
			$takeselfList[$key]['city_str']     = $temp[$val['city']];
			$takeselfList[$key]['area_str']     = $temp[$val['area']];
		}
		die(JSON::encode($takeselfList));
	}

	//物流轨迹查询
	public function freight()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		if($id)
		{
			$tb_freight = new IQuery('delivery_doc as d');
			$tb_freight->join  = 'left join freight_company as f on f.id = d.freight_id';
			$tb_freight->where = 'd.id = '.$id;
			$tb_freight->fields= 'd.*,f.freight_type';
			$freightData = $tb_freight->find();

			if($freightData)
			{
				$freightData = current($freightData);
				if($freightData['freight_type'] && $freightData['delivery_code'])
				{
					$result = freight_facade::line($freightData['freight_type'],$freightData['delivery_code']);
					if($result['result'] == 'success')
					{
						$this->data = $result['data'];
						$this->redirect('freight');
						return;
					}
					else
					{
						die(isset($result['reason']) ? $result['reason'] : '物流接口发生错误');
					}
				}
				else
				{
					die('缺少物流信息');
				}
			}
		}
		die('发货单信息不存在');
	}
	/**
     * 筛选商户
     */
    public function filter_seller()
    {
		$where   = '';
		$sellerIds = '';
    	$search  = IFilter::act(IReq::get('search'),'strict');
    	$search  = $search ? $search : array();

    	foreach($search as $key => $val)
    	{
    		if($val)
    		{
    			$where .= $key.'"'.$val.'"';
    		}
    	}

    	//有筛选条件
    	if($where)
    	{
	    	$sellerDB = new IQuery('seller');
	    	$sellerDB->fields= 'id';
	    	$sellerDB->where = $where;
	    	$sellerData      = $sellerDB->find();
	    	$tempArray     = array();
	    	foreach($sellerData as $key => $item)
	    	{
	    		$tempArray[] = $item['id'];
	    	}
	    	$sellerIds = join(',',$tempArray);

	    	if(!$sellerIds)
	    	{
	    		die('<script type="text/javascript">alert("没有找到商户信息,请重新输入条件");window.history.go(-1);</script>');
	    	}
    	}

    	die('<script type="text/javascript">parent.searchSellerCallback("'.$sellerIds.'");</script>');
    }

	//收货地址弹出框
    public function address()
    {
    	$user_id = $this->user['user_id'];
    	$id      = IFilter::act(IReq::get('id'),'int');
    	if($user_id)
    	{
    		if($id)
    		{
	    		$addressDB        = new IModel('address');
	    		$this->addressRow = $addressDB->getObj('user_id = '.$user_id.' and id = '.$id);
    		}
    	}
    	else
    	{
			$this->addressRow = ISafe::get('address');
    	}
    	$this->redirect('address');
    }

    //代金券弹出框
    public function ticket()
    {
		$this->prop       = array();
		$this->sellerInfo = IFilter::act(IReq::get('sellerString'));
		$user_id          = $this->user['user_id'];

		//获取代金券
		if($user_id)
		{
			$memberObj = new IModel('member');
			$memberRow = $memberObj->getObj('user_id = '.$user_id,'prop');

			if(isset($memberRow['prop']) && ($propId = trim($memberRow['prop'],',')))
			{
				$porpObj = new IModel('prop');
				$this->prop = $porpObj->query('id in ('.$propId.') and NOW() between start_time and end_time and type = 0 and is_close = 0 and is_userd = 0 and is_send = 1');
			}
		}
		$this->redirect('ticket');
    }
}