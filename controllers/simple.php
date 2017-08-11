<?php
/**
 * @copyright Copyright(c) 2011 aircheng.com
 * @file Simple.php
 * @brief
 * @author webning
 * @date 2011-03-22
 * @version 0.6
 * @note
 *一样
 */
/**
 * @brief Simple
 * @class Simple
 * @note
 */
class Simple extends IController
{
    public $layout='site_mini';

	function init()
	{

	}

	function login()
	{
		//如果已经登录，就跳到ucenter页面
		if($this->user)
		{
			$this->redirect("/ucenter/index");
		}
		else
		{
			$this->redirect('login');
		}
	}

	//退出登录
    function logout()
    {
    	plugin::trigger('clearUser');
    	$this->redirect('login');
    }

    //用户注册
    function reg_act()
    {
    	//调用_userInfo注册插件
    	$result = plugin::trigger("userRegAct",$_POST);
    	if(is_array($result))
    	{
			//自定义跳转页面
			$this->redirect('/site/success?message='.urlencode("注册成功！"));
    	}
    	else
    	{
    		$this->setError($result);
    		$this->redirect('reg',false);
    		Util::showMessage($result);
    	}
    }

    //用户登录
    function login_act()
    {
    	//调用_userInfo登录插件
		$result = plugin::trigger('userLoginAct',$_POST);
		if(is_array($result))
		{
			//自定义跳转页面
			$callback = plugin::trigger('getCallback');
			if($callback)
			{
				$this->redirect($callback);
			}
			else
			{
				$this->redirect('/ucenter/index');
			}
		}
		else
		{
			$this->setError($result);
			$this->redirect('login',false);
			Util::showMessage($result);
		}
    }

    //商品加入购物车[ajax]
    function joinCart()
    {
    	$link      = IReq::get('link');
    	$goods_id  = IFilter::act(IReq::get('goods_id'),'int');
    	$goods_num = IReq::get('goods_num') === null ? 1 : intval(IReq::get('goods_num'));
		$type      = IFilter::act(IReq::get('type'));

		//加入购物车
    	$cartObj   = new Cart();
    	$addResult = $cartObj->add($goods_id,$goods_num,$type);

    	if($link != '')
    	{
    		if($addResult === false)
    		{
    			$this->cart(false);
    			Util::showMessage($cartObj->getError());
    		}
    		else
    		{
    			$this->redirect($link);
    		}
    	}
    	else
    	{
	    	if($addResult === false)
	    	{
		    	$result = array(
		    		'isError' => true,
		    		'message' => $cartObj->getError(),
		    	);
	    	}
	    	else
	    	{
		    	$result = array(
		    		'isError' => false,
		    		'message' => '添加成功',
		    	);
	    	}
	    	echo JSON::encode($result);
    	}
    }

    //根据goods_id获取货品
    function getProducts()
    {
    	$id           = IFilter::act(IReq::get('id'),'int');
    	$productObj   = new IModel('products');
    	$productsList = $productObj->query('goods_id = '.$id,'sell_price,id,spec_array,goods_id','store_nums desc',7);
		if($productsList)
		{
			foreach($productsList as $key => $val)
			{
				$productsList[$key]['specData'] = Block::show_spec($val['spec_array']);
			}
		}
		echo JSON::encode($productsList);
    }

    //删除购物车
    function removeCart()
    {
    	$link      = IReq::get('link');
    	$goods_id  = IFilter::act(IReq::get('goods_id'),'int');
    	$type      = IFilter::act(IReq::get('type'));

    	$cartObj   = new Cart();
    	$cartInfo  = $cartObj->getMyCart();
    	$delResult = $cartObj->del($goods_id,$type);

    	if($link != '')
    	{
    		if($delResult === false)
    		{
    			$this->cart(false);
    			Util::showMessage($cartObj->getError());
    		}
    		else
    		{
    			$this->redirect($link);
    		}
    	}
    	else
    	{
	    	if($delResult === false)
	    	{
	    		$result = array(
		    		'isError' => true,
		    		'message' => $cartObj->getError(),
	    		);
	    	}
	    	else
	    	{
		    	$goodsRow = $cartInfo[$type]['data'][$goods_id];
		    	$cartInfo['sum']   -= $goodsRow['sell_price'] * $goodsRow['count'];
		    	$cartInfo['count'] -= $goodsRow['count'];

		    	$result = array(
		    		'isError' => false,
		    		'data'    => $cartInfo,
		    	);
	    	}

	    	echo JSON::encode($result);
    	}
    }

    //清空购物车
    function clearCart()
    {
    	$cartObj = new Cart();
    	$cartObj->clear();
    	$this->redirect('cart');
    }

    //购物车div展示
    function showCart()
    {
    	$cartObj  = new Cart();
    	$cartList = $cartObj->getMyCart();
    	$data['data'] = array_merge($cartList['goods']['data'],$cartList['product']['data']);
    	$data['count']= $cartList['count'];
    	$data['sum']  = $cartList['sum'];
    	echo JSON::encode($data);
    }

    //购物车页面及商品价格计算[复杂]
    function cart($redirect = false)
    {
		
    	//防止页面刷新
    	header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);

		//开始计算购物车中的商品价格
    	$countObj = new CountSum();
    	$result   = $countObj->cart_count();

    	if(is_string($result))
    	{
    		IError::show($result,403);
    	}
        
    	//$this->pri($result);
    	
    	//die;
    	
    	//返回值
    	$this->final_sum = $result['final_sum'];
    	$this->promotion = $result['promotion'];
    	$this->proReduce = $result['proReduce'];
    	$this->sum       = $result['sum'];
    	$this->goodsList = $result['goodsList'];
    	$this->count     = $result['count'];
    	$this->reduce    = $result['reduce'];
    	$this->weight    = $result['weight'];
    	$this->jifenprice = 0;
    	$this->isjifen = 0;
    	$this->seller = $result['seller'];
    	
		//渲染视图
    	$this->redirect('cart',$redirect);
    }
    
    function pri($res){
        echo "<pre>";
        print_r($res);
        echo "</pre>";
    }
    
    //计算促销规则[ajax]
    function promotionRuleAjax()
    {
    	$goodsId   = IFilter::act(IReq::get("goodsId"),'int');
    	$productId = IFilter::act(IReq::get("productId"),'int');
    	$num       = IFilter::act(IReq::get("num"),'int');

    	if(!$goodsId || !$num)
    	{
			return;
    	}

		$goodsArray  = array();
		$productArray= array();

    	foreach($goodsId as $key => $goods_id)
    	{
    		$pid = $productId[$key];
    		$nVal= $num[$key];

    		if($pid > 0)
    		{
    			$productArray[$pid] = $nVal;
    		}
    		else
    		{
    			$goodsArray[$goods_id] = $nVal;
    		}
    	}

		$countSumObj    = new CountSum();
		$cartObj        = new Cart();
		$countSumResult = $countSumObj->goodsCount($cartObj->cartFormat(array("goods" => $goodsArray,"product" => $productArray)));
    	echo JSON::encode($countSumResult);
    }

    //填写订单信息cart2
    function cart2()
    {
        
       
        
		$id        = IFilter::act(IReq::get('id'),'int');
		$type      = IFilter::act(IReq::get('type'));//goods,product
		$promo     = IFilter::act(IReq::get('promo'));
		$active_id = IFilter::act(IReq::get('active_id'),'int');
		$buy_num   = IReq::get('num') ? IFilter::act(IReq::get('num'),'int') : 1;
		$tourist   = IReq::get('tourist');//游客方式购物
        
        $isjifen = IFilter::act(IReq::get('isjifen'),'int');
        $duihuanjifen = IFilter::act(IReq::get('jifen'),'int');
        $xiaoshou_type = IFilter::act(IReq::get('xiaoShouType'),'int');
        $seller_name = IFilter::act(IReq::get('seller_name'));
        
        
        $res = array();
    	//必须为登录用户(游客会进来)
    	if($tourist === null && $this->user['user_id'] == null)
    	{
    		if($id == 0 || $type == '')
    		{
    		    //die("in".$id);
    			$this->redirect('/simple/login?tourist&callback=/simple/cart2');
    		}
    		else
    		{
    			$url  = '/simple/login?tourist&callback=/simple/cart2/id/'.$id.'/type/'.$type.'/num/'.$buy_num.'/xiaoShouType/'.$xiaoshou_type.'/seller_name/'.$seller_name;
    			$url .= $promo     ? '/promo/'.$promo         : '';
    			$url .= $active_id ? '/active_id/'.$active_id : '';
    			$this->redirect($url);
    		}
    	}
        //有问题
    	if($xiaoshou_type == 1){
    	    $userDB = new IModel('user');
		    $userData = $userDB->getObj('id='.$this->user['user_id']); 
    	  if($userData['username'] != $seller_name){
    	      IError::show(403,"您暂不支持购买该商品");
    	      exit;
    	  }
    	}
    	
		//游客的user_id默认为0
    	$user_id = ($this->user['user_id'] == null) ? 0 : $this->user['user_id'];

		//计算商品
		$countSumObj = new CountSum($user_id);
		$result = $countSumObj->cart_count($id,$type,$buy_num,$promo,$active_id);
        
		//print_r($result);
		
		if($countSumObj->error)
		{
			IError::show(403,$countSumObj->error);
		}
        
		
		
		$tb_goods = new IModel('goods');
		$goods_info = $tb_goods->getObj('id='.$id." AND is_del=0");
		
		if($isjifen == 1){
		    $res = $this->deelJiFenOrder($goods_info, $duihuanjifen);
		    if($res['e']){
		        IError::show(403,"非法操作");
		        return 'err';
		    }
		    $this->duihuanpoint = $res['point'];
		    $this->jifenprice = $res['price'];
		    $this->isjifen = 1;
		   // print_r($result);
		   // echo $result['e'];
		   // die;
		}
		
		
    	//获取收货地址
    	$addressObj  = new IModel('address');
    	$addressList = $addressObj->query('user_id = '.$user_id,"*","is_default desc");

		//更新$addressList数据
    	foreach($addressList as $key => $val)
    	{
    		$temp = area::name($val['province'],$val['city'],$val['area']);
    		if(isset($temp[$val['province']]) && isset($temp[$val['city']]) && isset($temp[$val['area']]))
    		{
	    		$addressList[$key]['province_val'] = $temp[$val['province']];
	    		$addressList[$key]['city_val']     = $temp[$val['city']];
	    		$addressList[$key]['area_val']     = $temp[$val['area']];
    		}
    	}

		//获取习惯方式
		$memberObj = new IModel('member');
		$memberRow = $memberObj->getObj('user_id = '.$user_id,'custom');
		if(isset($memberRow['custom']) && $memberRow['custom'])
		{
			$this->custom = unserialize($memberRow['custom']);
		}
		else
		{
			$this->custom = array(
				'payment'  => '',
				'delivery' => '',
			);
		}
        
		$tb_goods = new IModel('goods');
		$goods_info = $tb_goods->getObj('id='.$id." AND is_del=0");
		
		
    	//返回值
		$this->gid       = $id;
		
		$this->type      = $type;
		$this->num       = $buy_num;
		$this->promo     = $promo;
		$this->active_id = $active_id;
		$this->freefreight = $goods_info['freefreight'];
    	$this->final_sum = $result['final_sum'];
    	$this->promotion = $result['promotion'];
    	$this->proReduce = $result['proReduce'];
    	$this->sum       = $result['sum'];
    	$this->goodsList = $result['goodsList'];
    	$this->count       = $result['count'];
    	$this->reduce      = $result['reduce'];
    	$this->weight      = $result['weight'];
    	$this->freeFreight = $result['freeFreight'];
    	$this->seller      = $result['seller'];
        $this->good_type  = $goods_info['good_type'];
		//收货地址列表
		$this->addressList = $addressList;
        $this->xiaoshou_type = $xiaoshou_type;
		//获取商品税金
		$this->goodsTax    = $result['tax'];

    	//渲染页面
    	$this->redirect('cart2');
    }

    /**
     * 积分订单计算
     */
    
    function deelJiFenOrder($goods_info, $duihuanjifen){
       // print_r($goods_info);
       // echo "兑换积分 is ". $duihuanjifen."<br/>";
        
        //获取配置文件
        $siteConfigObj = new Config("site_config");
        $site_config   = $siteConfigObj->getInfo();
       // print_r($site_config);
        
        //兑换比例
        $duihuanpoint = $goods_info['duihuanpoint'];
        //销售价格
        $sell_price = $goods_info['sell_price'];
        
        //最高积分
        $sum = $sell_price * $duihuanpoint * 0.01 * $site_config['jifencon'];
        if($duihuanjifen > $sum){
            $arr = array(
                "e"=>"er"
            );
            return $arr;
        }
        
        /*
         * 1.格式化传过来的积分
         * 2.计算价格
         * 
         */
        
        //格式化积分
        $newPoint = floor($duihuanjifen / ($site_config['jifencon'] / 100)) * floor($site_config['jifencon'] / 100);
        //价格计算
        $price = $sell_price - floor($newPoint) / floor($site_config['jifencon']);
        $arr = array(
            "point"=>$newPoint,
            "price"=>$price
        );
        
        return $arr;
        
    }
    
    
    /**
     * 购物车提交
     */
    
    function shoppcart(){
        
       // session_start();
        
       
        
        
        //全局商品id
        $gl_gid ;    //商品id
        
        
        $json_data = $_POST['data'] ? $_POST['data']:"";
        
        if(empty($json_data)){
            $goodsdata = ISession::get("goodssss");
            if(empty($goodsdata)){
                IError::show(403,"请重新提交购物车");
            }
        }else{
            
            $goodsdata = json_decode($json_data,true);
            ISession::set("goodssss",$goodsdata);
        }
        
        
        $user_id       = ($this->user['user_id'] == null) ? 0 : $this->user['user_id'];
        
        
        $arr = array();  //存放每个店铺的订单
        //循环根据逗号分隔
        foreach ($goodsdata as $key=>$val){
            
            $store_order = explode(',',$goodsdata[$key]); //每个店铺的订单
            $arr[$key] = $store_order;
        }
        
        /*echo "<pre>";
        print_r($arr);
        echo "</pre>";
       
       die;  */
        $freight = array();
        
        //查询每个商品下是否存在免运费
        foreach ($arr as $k=>$v){
            
            $seller_id = $k;
            $goodids_temp ="" ;
            
            foreach ($v as $key=>$val){
              //  echo $val."<br/>";
                $goodid = explode("*",$val)[0];
                $goodids_temp = $goodid.",".$goodids_temp;
                $gl_gid = $goodid.",".$gl_gid;
            }
            
            $goods_id = substr($goodids_temp,0,strlen($goodids_temp)-1);
            
            
            $goodsdb = new  IQuery("goods as g");
            $goodsdb->fields = "g.id,g.freefreight,g.seller_id";
            $goodsdb->where = "g.id in($goods_id)";  //商品的运费是否免
            $res = $goodsdb->find();
            
            array_push($freight,$res);
        }
        
         
         /* echo "<pre>";
         print_r($freight);
         echo "</pre>";
         
        die;*/
         
         /**
          * 每个店铺需要的运费
          */
         //获取用户的默认地址
         $addressDB = new IModel('address');
         $addressRow= $addressDB->getObj('is_default = 1 and user_id = '.$user_id);
         
		 
		 
         if(!$addressRow){
             //IError::show(403,"收货地址信息不存在");
             $address_res = $addressDB->getObj('user_id = '.$user_id);
             if(!$address_res){
                 IError::show(403,"收货地址信息不存在");
             }
             
             $addressRow = $address_res ;
             
         }
         
        /*  echo "<pre>";
         print_r($addressRow);
         echo "</pre>"; */
         
         
         
         $deliveryObj = new IModel('delivery');
         $deliveryRow = $deliveryObj->getObj('id = 1');
         
		
		 
         //计算运费类
         $delieve_class = new Delivery();
         $province = $addressRow['province'];   //省份id
         $delivery_id = 1;  //默认配送方式  1 是快递方式
         $delieve_arr = array();
         
		  
		 
         foreach ($arr as $k=>$v){
         
             $seller_id = $k;
             $goodids_temp ="" ;
             $goods_num_temp = "";
             
             foreach ($v as $key=>$val){
                 //  echo $val."<br/>";
                 $goodid = explode("*",$val)[0];
                 $nums = explode("*",$val)[1];
                 
                 
                 $goodids_temp = $goodid.",".$goodids_temp;
                 $goods_num_temp = $nums.",".$goods_num_temp;
                 
               //  $goods_num_temp = $nums.",".$goods_num_temp;
                 
                
                 
                 
             }
            
             //
             $gid = substr($goodids_temp,0,strlen($goodids_temp)-1);
             $g_num = substr($goods_num_temp,0,strlen($goods_num_temp)-1);
             
             $gid_arr= explode(",",$gid);
             $g_num_arr = explode(",",$g_num);
             
             
             
             $result_delieve = $delieve_class::getDelivery($province,$delivery_id,$gid_arr,$product_id = 0,$g_num_arr);  //传过去
             array_push($delieve_arr,$result_delieve);
            
             
            
             
            // $goods_id = substr($goodids_temp,0,strlen($goodids_temp)-1);
             
         }
         
                  
         /* echo "<pre>";
         print_r($delieve_arr);
         echo "</pre>";
         die; */
		 
		 
         
         //渲染页面
         $this->setRenderData(array('user_address'=>$addressRow));//收货人信息
         $this->setRenderData(array('car_goods'=>$arr));//购物车物品
         $this->setRenderData(array('freight'=>$freight));//免运费信息
         $this->setRenderData(array('result_delieve'=>$delieve_arr));//需要的运费
         
        //$test = ISession::get('cartdata');
        // print_r($test);
         //die;
		
			
         $this->redirect('buy',false);
         
         
         //die;
        
         /**
          * @brief 配送方式计算管理模块
          * @param $province    int 省份的ID
          * @param $delivery_id int 配送方式ID
          * @param $goods_id    array 商品ID
          * @param $product_id  array 货品ID
          * @param $num         array 商品数量
          * @return array(
          *  id => 配送方式ID,
          *  name => 配送方式NAME,
          *  description => 配送方式描述,
          *	if_delivery => 0:支持配送;1:不支持配送,
          *  price => 实际运费总额,
          *  protect_price => 商品保价总额,
          *  org_price => 原始运费总额,
          *	seller_price => array(商家ID => 实际运费),
          *	seller_protect_price => array(商家ID => 商品保价),
          *  seller_org_price => array(商家ID => 原始运费),
          *	)
          */
       // public static function getDelivery($province,$delivery_id,$goods_id,$product_id = 0,$num = 1)
        
        
    }
    
    

    /**
     * 购物车删除商品
     * 
     */
    function cart_del(){
        
        $good_id = $_GET['good_id'];
        if(empty($good_id)){
            echo "err";
            return ;
        }
        
        $cartObj   = new Cart();
        $res = $cartObj->del($good_id);
        echo $res;
    }
    
    
    /**
     * 购物车操作
     * 1.添加
     * 2.删除
     */
    
    function cart_crud(){
        
        $json_data = $_GET['data'];
        if(empty($json_data)){
            echo "err";
            return ;
        }
        
        $addcart_arr = json_decode($json_data,true);
        //遍历添加到购物车
        $cartObj   = new Cart();
        foreach ($addcart_arr as $key=>$val){
            $result = $cartObj->add($key,$val);
            print_r($result); 
        }
        
        
        //print_r($addcart_arr);
        
        
        
    }
    
    
    
    
	/**
	 * 生成订单
	 */
    function cart3()
    {
        
       
        
    	$address_id    = IFilter::act(IReq::get('radio_address'),'int');
    	$delivery_id   = IFilter::act(IReq::get('delivery_id'),'int');
    	$accept_time   = IFilter::act(IReq::get('accept_time'));
    	$payment       = IFilter::act(IReq::get('payment'),'int');
    	$order_message = IFilter::act(IReq::get('message'));
    	$ticket_id     = IFilter::act(IReq::get('ticket_id'),'int');
    	$taxes         = IFilter::act(IReq::get('taxes'),'float');
    	$tax_title     = IFilter::act(IReq::get('tax_title'));
    	$gid           = IFilter::act(IReq::get('direct_gid'),'int');
    	$num           = IFilter::act(IReq::get('direct_num'),'int');
    	$type          = IFilter::act(IReq::get('direct_type'));//商品或者货品
    	$promo         = IFilter::act(IReq::get('direct_promo'));
    	$active_id     = IFilter::act(IReq::get('direct_active_id'),'int');
    	$takeself      = IFilter::act(IReq::get('takeself'),'int');
    	$good_type    = IFilter::act(IReq::get('direct_good_type'),'int');
    	
    	
    	
    	
    	$order_type    = 0;
    	$dataArray     = array();
    	$user_id       = ($this->user['user_id'] == null) ? 0 : $this->user['user_id'];
        $isjifen = IFilter::act(IReq::get('isjifen'),'int');
        $duihuanpoint = IFilter::act(IReq::get('duihuanpoint'),'int');
    	$jifenprice = IFilter::act(IReq::get('jifenprice'));
    	$seller_id = IFilter::act(IReq::get('seller_id'));
		//获取商品数据信息
    	$countSumObj = new CountSum($user_id);
		$goodsResult = $countSumObj->cart_count($gid,$type,$num,$promo,$active_id);
		$xiaoshou_type = IFilter::act(IReq::get('xiaoshou_type'),'int');
		$o_type = 0;
		
	    file_put_contents('car2.txt',$order_type." ",FILE_APPEND );
		
		if($countSumObj->error)
		{
			IError::show(403,$countSumObj->error);
		}
        
		
		//处理收件地址
		//1,访客; 2,注册用户
		if($user_id == 0)
		{
			$addressRow = ISafe::get('address');
		}
		else
		{
			$addressDB = new IModel('address');
			$addressRow= $addressDB->getObj('id = '.$address_id.' and user_id = '.$user_id);
		}
		
		if(!$addressRow && $good_type == 1)
		{
			IError::show(403,"收货地址信息不存在");
		}
		
    	$accept_name   = IFilter::act($addressRow['accept_name'],'name');
    	
    	//echo "accept name is ".$accept_name;
    	//die;
    	
    	$province      = $addressRow['province'];
    	$city          = $addressRow['city'];
    	$area          = $addressRow['area'];
    	$address       = IFilter::act($addressRow['address']);
    	$mobile        = IFilter::act($addressRow['mobile'],'mobile');
    	$telphone      = IFilter::act($addressRow['telphone'],'phone');
    	$zip           = IFilter::act($addressRow['zip'],'zip');
        
		//检查订单重复
    	$checkData = array(
    		//"accept_name" => $accept_name,   收件人姓名 （相同收件人姓名不能提交订单）
    		"address"     => $address,
    		"mobile"      => $mobile,
    		"distribution"=> $delivery_id,
    	);
    	if($good_type == 1){
    	$result = order_class::checkRepeat($checkData,$goodsResult['goodsList']);
    	}
    	if( is_string($result) )
    	{
			IError::show(403,$result);
    	}
    	
		//配送方式,判断是否为货到付款
		$deliveryObj = new IModel('delivery');
		$deliveryRow = $deliveryObj->getObj('id = '.$delivery_id);
		
		if(!$deliveryRow && $good_type == 1)
		{
			IError::show(403,'配送方式不存在');
		}

		if($deliveryRow['type'] == 0)
		{
			if($payment == 0)
			{
				IError::show(403,'请选择正确的支付方式');
			}
		}
		else if($deliveryRow['type'] == 1)
		{
			$payment = 0;
		}
		else if($deliveryRow['type'] == 2)
		{
			if($takeself == 0)
			{
				IError::show(403,'请选择正确的自提点');
			}
		}
		//如果不是自提方式自动清空自提点
		if($deliveryRow['type'] != 2)
		{
			$takeself = 0;
		}

		if(!$gid)
		{
			//清空购物车
			IInterceptor::reg("cart@onFinishAction");
		}

    	//判断商品是否存在
    	if(is_string($goodsResult) || empty($goodsResult['goodsList']))
    	{
    		IError::show(403,'商品数据错误');
    	}
    	//加入促销活动
    	if($promo && $active_id)
    	{
    		$activeObject = new Active($promo,$active_id,$user_id,$gid,$type,$num);
    		$order_type = $activeObject->getOrderType();
    	}
    	
		$paymentObj = new IModel('payment');
		$paymentRow = $paymentObj->getObj('id = '.$payment,'type,name');
		if(!$paymentRow)
		{
			IError::show(403,'支付方式不存在');
		}
		$paymentName= $paymentRow['name'];
		$paymentType= $paymentRow['type'];

		//最终订单金额计算
		$orderData = $countSumObj->countOrderFee($goodsResult,$province,$delivery_id,$payment,$taxes,0,$promo,$active_id,$good_type);
		if(is_string($orderData))
		{
			IError::show(403,$orderData);
			exit;
		}
    
		//根据商品所属商家不同批量生成订单
		$orderIdArray  = array();
		$orderNumArray = array();
		$final_sum     = 0;
		
		//获取订单号
		if($good_type==2){
		    $order_no = $this->_makeOrderSn($user_id);
		    
		}else{
		    $order_no = Order_Class::createOrderNum();
		   
		} 
		foreach($orderData as $seller_id => $goodsResult)
		{
		    //三级提成：三个等级的用户都可以分到这个钱
		    //one、two、three：上线id
		    //销售id：用户进第一家店的id
		    //销售提成
		    //归属：在哪个店买东西
		    //手续费：写死
		    //成本
		    
		    /*
		     * 1.goods：销售提成、归属、手续费、成本、归属id：sell_id
		     * 2.user：one、two、three
		     * 3.session：销售id
		     *
		     */
		     
		    $goods = new IModel("goods");
		    $g_r = $goods->query("id='$gid'");
		    
		    $user = new IModel("member");
		    $u = $user->query("user_id='$user_id'");
		    
		    //总价
		    $sell_price = $g_r[0]['sell_price'];
		    
		    //三级提成
		    $sanjiticheng = $g_r[0]['sanjiticheng'];
		    $sanjiticheng = $sell_price * $sanjiticheng/100 ;
		    $sanjiticheng = substr(sprintf("%.3f",$sanjiticheng),0,-1);//三级提成保留2位小数
		    
		    //三个上级
		    $one_id = $u[0]['invite_one'];
		    $two_id = $u[0]['invite_two'];
		    $three_id = $u[0]['invite_three'];
		    
		    $xiaoshouid = IFilter::act(ISession::get('xiaoshouid'));
		    if(empty($xiaoshouid)){
		        $xiaoshouid = 0;
		    }
		    
		    //归属
		    $guishu = $g_r[0]['guishu'] * $sell_price / 100;
		    
		    $guishu = substr(sprintf("%.3f",$guishu),0,-1);//归属保留2位小数
		     
		    //手续费
		    $shouxufei = $g_r[0]['shouxufei'] * $sell_price / 100;
		    $shouxufei = substr(sprintf("%.3f",$shouxufei),0,-1);//手续费保留2位小数
		    
		    //销售提成
		    $xiaoshouticheng = $g_r[0]['xiaoshouticheng'] * $sell_price / 100;
		    $xiaoshouticheng = substr(sprintf("%.3f",$xiaoshouticheng),0,-1);//手续费保留2位小数
		    
		    //成本
		    $chenben = $g_r[0]['chengben'] * $sell_price / 100;
		    $chenben = substr(sprintf("%.3f",$chenben),0,-1);  //成本保留2位小数
		    
		    $sellId = $g_r[0]['seller_id'];
		    
		    $good_name = $g_r[0]['name'];
		   $tichen = $g_r[0]['tichen'];
		   
		   //实物
		    if($xiaoshou_type == 0){
		     if($isjifen == 1){
		         
		         /**
		          * 
		          * 1.获取用户兑换的积分
		          * 2.计算积分的价值
		          * 3.和传过来的价格比较，一致才通过
		          * 4.
		          * 
		          * */
		         
		         //自定义的    积分/元
		         $siteConfigObj = new Config("site_config");
		         $site_config   = $siteConfigObj->getInfo();
		         //$minpoint积分/元
		         $minpoint = $site_config['jifencon'];
		         
		         $discountprice = $duihuanpoint / $minpoint ;
		         $realplace = $sell_price - $discountprice ;
		         $ordersum = $realplace + $goodsResult['deliveryOrigPrice'] + $goodsResult['taxPrice'];
		         
		         if($realplace - $jifenprice != 0){
		             echo "err";die;
		         }
		         
		         //生成的实物订单数据
		         $dataArray = array(
		             'order_no'            => $order_no,
		             'user_id'             => $user_id,
		             'accept_name'         => $accept_name,
		             'pay_type'            => $payment,
		             'distribution'        => $delivery_id,
		             'postcode'            => $zip,
		             'telphone'            => $telphone,
		             'province'            => $province,
		             'city'                => $city,
		             'area'                => $area,
		             'address'             => $address,
		             'mobile'              => $mobile,
		             'create_time'         => ITime::getDateTime(),
		             'postscript'          => $order_message,
		             'accept_time'         => $accept_time,
		             'exp'                 => $goodsResult['exp'],
		             'point'               => $goodsResult['point'],
		             'type'                => $order_type,
		         
		             //商品价格
		             'payable_amount'      => $realplace,//应付
		             'real_amount'         => $realplace,//实付
		         
		             //运费价格
		             'payable_freight'     => $goodsResult['deliveryOrigPrice'],
		             'real_freight'        => $goodsResult['deliveryPrice'],
		         
		             //手续费
		             'pay_fee'             => $goodsResult['paymentPrice'],
		         
		             //税金
		             'invoice'             => $tax_title ? 1 : 0,
		             'invoice_title'       => $tax_title,
		             'taxes'               => $goodsResult['taxPrice'],
		         
		             //优惠价格
		             'promotions'          => $goodsResult['proReduce'] + $goodsResult['reduce'],
		         
		             //订单应付总额
		             'order_amount'        => $ordersum,
		         
		             //订单保价
		             'insured'             => $goodsResult['insuredPrice'],
		         
		             //自提点ID
		             'takeself'            => $takeself,
		         
		             //促销活动ID
		             'active_id'           => $active_id,
		         
		             //商家ID
		             'seller_id'           => $seller_id,
		             
		             //用户兑换的积分数量
		             'duihuanpoint'        =>$duihuanpoint,
		            'isjifen'         =>1,
		             'good_id'         =>$gid,
		              
		         );
		     }else{
		         
		         if($good_type == 2){
		             $gtype = "vr";
		         }else if($good_type == 1){
		             $gtype = "r";
		         }
		         
		         //生成的实物订单数据
		         $dataArray = array(
		             'order_no'            => $order_no,
		             'user_id'             => $user_id,
		             'accept_name'         => $accept_name,
		             'pay_type'            => $payment,
		             'distribution'        => $delivery_id,
		             'postcode'            => $zip,
		             'telphone'            => $telphone,
		             'province'            => $province,
		             'city'                => $city,
		             'area'                => $area,
		             'address'             => $address,
		             'mobile'              => $mobile,
		             'create_time'         => ITime::getDateTime(),
		             'postscript'          => $order_message,
		             'accept_time'         => $accept_time,
		             'exp'                 => $goodsResult['exp'],
		             'point'               => $goodsResult['point'],
		             'type'                => $order_type,
		              'order_type'         => $gtype,
		             //商品价格
		             'payable_amount'      => $goodsResult['sum'],
		             'real_amount'         => $goodsResult['final_sum'],
		         
		             //运费价格
		             'payable_freight'     => $goodsResult['deliveryOrigPrice'],
		             'real_freight'        => $goodsResult['deliveryPrice'],
		         
		             //手续费
		             'pay_fee'             => $goodsResult['paymentPrice'],
		         
		             //税金
		             'invoice'             => $tax_title ? 1 : 0,
		             'invoice_title'       => $tax_title,
		             'taxes'               => $goodsResult['taxPrice'],
		         
		             //优惠价格
		             'promotions'          => $goodsResult['proReduce'] + $goodsResult['reduce'],
		         
		             //订单应付总额
		             'order_amount'        => $goodsResult['orderAmountPrice'],
		         
		             //订单保价
		             'insured'             => $goodsResult['insuredPrice'],
		         
		             //自提点ID
		             'takeself'            => $takeself,
		         
		             //促销活动ID
		             'active_id'           => $active_id,
		         
		             //商家ID
		             'seller_id'           => $seller_id,
		         
		             //备注信息
		             'note'                => '',
		             'sanjiticheng'        =>$sanjiticheng,
		             'one'                =>$one_id,
		             'two'                =>$two_id,
		             'three'              =>$three_id,
		             'xiaoshouid'         =>$xiaoshouid,
		             'guishu'             =>$guishu,
		             'guishuid'           =>$sellId,
		             'shouxufei'          =>$shouxufei,
		             'xiaoshouticheng'    =>$xiaoshouticheng,
		             'chengben'           =>$chenben,
		             'tichen'             =>$tichen,
		             'good_id'         =>$gid,
		              
		         );
		     }
			
            
			//获取红包减免金额
			if($ticket_id)
			{
				$memberObj = new IModel('member');
				
				$memberRow = $memberObj->getObj('user_id = '.$user_id,'prop,custom');
				foreach($ticket_id as $tk => $tid)
				{
					//游客手动添加或注册用户道具中已有的代金券
					if(ISafe::get('ticket_'.$tid) == $tid || stripos(','.trim($memberRow['prop'],',').',',','.$tid.',') !== false)
					{
						$propObj   = new IModel('prop');
						$ticketRow = $propObj->getObj('id = '.$tid.' and NOW() between start_time and end_time and type = 0 and is_close = 0 and is_userd = 0 and is_send = 1');
						if(!$ticketRow)
						{
							IError::show(403,'代金券不可用');
						}

						if($ticketRow['seller_id'] == 0 || $ticketRow['seller_id'] == $seller_id)
						{
							$ticketRow['value']         = $ticketRow['value'] >= $goodsResult['final_sum'] ? $goodsResult['final_sum'] : $ticketRow['value'];
							$dataArray['prop']          = $tid;
							$dataArray['promotions']   += $ticketRow['value'];
							$dataArray['order_amount'] -= $ticketRow['value'];
							$goodsResult['promotion'][] = array("plan" => "代金券","info" => "使用了￥".$ticketRow['value']."代金券");

							//锁定红包状态
							$propObj->setData(array('is_close' => 2));
							$propObj->update('id = '.$tid);

							unset($ticket_id[$tk]);
							break;
						}
					}
				}
			}

			//促销规则
			if(isset($goodsResult['promotion']) && $goodsResult['promotion'])
			{
				foreach($goodsResult['promotion'] as $key => $val)
				{
					$dataArray['note'] .= join("，",$val)."。";
				}
			}

			$dataArray['order_amount'] = $dataArray['order_amount'] <= 0 ? 0 : $dataArray['order_amount'];

			//生成订单插入order表中
			$orderObj  = new IModel('order');
			$orderObj->setData($dataArray);
			$order_id = $orderObj->add();
			
		    }elseif($good_type==2 && $xiaoshou_type == 0){
		        
		        
		         
		       //-------------------- 
		    }else if ($xiaoshou_type == 1){
		       //生成的实物订单数据
		         $dataArray = array(
		             'order_no'            => $order_no,
		             'user_id'             => $user_id,
		             'accept_name'         => $accept_name,
		             'pay_type'            => $payment,
		             'distribution'        => $delivery_id,
		             'postcode'            => $zip,
		             'telphone'            => $telphone,
		             'province'            => $province,
		             'city'                => $city,
		             'area'                => $area,
		             'address'             => $address,
		             'mobile'              => $mobile,
		             'create_time'         => ITime::getDateTime(),
		             'postscript'          => $order_message,
		             'accept_time'         => $accept_time,
		             'exp'                 => $goodsResult['exp'],
		             'point'               => $goodsResult['point'],
		         
		             //商品价格
		             'payable_amount'      => $goodsResult['sum'],
		             'real_amount'         => $goodsResult['final_sum'],
		             //运费价格
		             'payable_freight'     => $goodsResult['deliveryOrigPrice'],
		             'real_freight'        => $goodsResult['deliveryPrice'],
		             //订单应付总额
		             'order_amount'        => $goodsResult['orderAmountPrice'],
		             //商家ID
		             'seller_id'           => $seller_id,
		         
		             //备注信息
		             'note'                => '',
		         );
		         //生成订单插入supplier表中(供应商)
		         $orderObj  = new IModel('supplier');
		         $orderObj->setData($dataArray);
		         $order_id = $orderObj->add();
		    }
		    
			if($order_id == false)
			{
				IError::show(403,'订单生成错误');
			}
			
			
			/*将订单中的商品插入到order_goods表*/
			    $orderInstance = new Order_Class();
			    if($isjifen == 1){
			        $orderInstance->insertjifenOrderGoods($order_id,$goodsResult['goodsResult'],$dataArray);
			    }else{
			         if ($xiaoshou_type == 1){
			             $orderInstance->insertSupplierOrderGoods($order_id,$goodsResult['goodsResult']);
			         }else{
			             $orderInstance->insertOrderGoods($order_id,$goodsResult['goodsResult']);
			         }
			    }
			
			
			//订单金额小于等于0直接免单
			if($dataArray['order_amount'] <= 0)
			{
				Order_Class::updateOrderStatus($dataArray['order_no']);
			}
			else
			{
				$orderIdArray[]  = $order_id;
				$orderNumArray[] = $dataArray['order_no'];
				$final_sum      += $dataArray['order_amount'];
			}
		}
		
		//记录用户默认习惯的数据
		if(!isset($memberRow['custom']))
		{
			$memberObj = new IModel('member');
			$memberRow = $memberObj->getObj('user_id = '.$user_id,'custom');
		}
		
		$memberData = array(
			'custom' => serialize(
				array(
					'payment'  => $payment,
					'delivery' => $delivery_id,
				)
			),
		);
		
		
		
		$memberObj->setData($memberData);
		$memberObj->update('user_id = '.$user_id);

		//收货地址的处理
		if($user_id)
		{
			$addressDefRow = $addressDB->getObj('user_id = '.$user_id.' and is_default = 1');
			if(!$addressDefRow)
			{
				$addressDB->setData(array('is_default' => 1));
				$addressDB->update('user_id = '.$user_id.' and id = '.$address_id);
			}
		}
    
		//获取备货时间
		$this->stockup_time = $this->_siteConfig->stockup_time ? $this->_siteConfig->stockup_time : 2;
		
		//数据渲染    
		$this->order_id    = join("_",$orderIdArray);
		$this->final_sum   = $final_sum;
		$this->order_num   = join(",",$orderNumArray);
		$this->payment     = $paymentName;
		$this->paymentType = $paymentType;
		$this->delivery    = $deliveryRow['name'];
		$this->tax_title   = $tax_title;
		$this->deliveryType= $deliveryRow['type'];
		$this->payment_id  = $payment;
		//是否为虚拟商品
		$this->good_type  = $good_type;
		
		plugin::trigger('setCallback','/ucenter/order');
		
		$this->xiaoshou_type = $xiaoshou_type;
		
		//echo $this->final_sum;
		//die("go cart3");
		
		
		//订单金额为0时，订单自动完成
		if($this->final_sum <= 0)
		{
			$this->redirect('/site/success/message/'.urlencode("订单确认成功，等待发货"));
		}
		else
		{
		    
			$this->setRenderData($dataArray);
			$this->redirect('cart3');    
		}
    }
    
//     function wechatPay(){
//         $this->redirect('wechatPay');
//     }
    
    private function _makeOrderSn($user_id) {
        return mt_rand(10,99)
        . sprintf('%010d',time() - 946656000)
        . sprintf('%03d', (float) microtime() * 1000)
        . sprintf('%03d', (int) $member_id % 1000);
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
    
    //到货通知处理动作
	function arrival_notice()
	{
		$user_id  = $this->user['user_id'];
		$email    = IFilter::act(IReq::get('email'));
		$mobile   = IFilter::act(IReq::get('mobile'));
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$register_time = ITime::getDateTime();

		if(!$goods_id)
		{
			IError::show(403,'商品ID不存在');
		}

		$model = new IModel('notify_registry');
		$obj = $model->getObj("email = '{$email}' and user_id = '{$user_id}' and goods_id = '$goods_id'");
		if(empty($obj))
		{
			$model->setData(array('email'=>$email,'user_id'=>$user_id,'mobile'=>$mobile,'goods_id'=>$goods_id,'register_time'=>$register_time));
			$model->add();
		}
		else
		{
			$model->setData(array('email'=>$email,'user_id'=>$user_id,'mobile'=>$mobile,'goods_id'=>$goods_id,'register_time'=>$register_time,'notify_status'=>0));
			$model->update('id = '.$obj['id']);
		}
		$this->redirect('/site/success',true);
	}

	/**
	 * @brief 邮箱找回密码进行
	 */
    function find_password_email()
	{
		$username = IReq::get('username');
		if($username === null || !IValidate::name($username)  )
		{
			IError::show(403,"请输入正确的用户名");
		}

		$email = IReq::get("email");
		if($email === null || !IValidate::email($email ))
		{
			IError::show(403,"请输入正确的邮箱地址");
		}

		$tb_user  = new IModel("user as u,member as m");
		$username = IFilter::act($username);
		$email    = IFilter::act($email);
		$user     = $tb_user->getObj(" u.id = m.user_id and u.username='{$username}' AND m.email='{$email}' ");
		if(!$user)
		{
			IError::show(403,"对不起，用户不存在");
		}
		$hash = IHash::md5( microtime(true) .mt_rand());

		//重新找回密码的数据
		$tb_find_password = new IModel("find_password");
		$tb_find_password->setData( array( 'hash' => $hash ,'user_id' => $user['id'] , 'addtime' => time() ) );

		if($tb_find_password->query("`hash` = '{$hash}'") || $tb_find_password->add())
		{
			$url     = IUrl::getHost().IUrl::creatUrl("/simple/restore_password/hash/{$hash}/user_id/".$user['id']);
			$content = mailTemplate::findPassword(array("{url}" => $url));

			$smtp   = new SendMail();
			$result = $smtp->send($user['email'],"您的密码找回",$content);

			if($result===false)
			{
				IError::show(403,"发信失败,请重试！或者联系管理员查看邮件服务是否开启");
			}
		}
		else
		{
			IError::show(403,"生成HASH重复，请重试");
		}
		$message = "恭喜您，密码重置邮件已经发送！请到您的邮箱中去激活";
		$this->redirect("/site/success/message/".urlencode($message));
	}

	//手机短信找回密码
	function find_password_mobile()
	{
		$username = IReq::get('username');
		if($username === null || !IValidate::name($username))
		{
			IError::show(403,"请输入正确的用户名");
		}

		$mobile = IReq::get("mobile");
		if($mobile === null || !IValidate::mobi($mobile))
		{
			IError::show(403,"请输入正确的电话号码");
		}

		$mobile_code = IFilter::act(IReq::get('mobile_code'));
		if($mobile_code === null)
		{
			IError::show(403,"请输入短信校验码");
		}

		$userDB = new IModel('user as u , member as m');
		$userRow = $userDB->getObj('u.username = "'.$username.'" and m.mobile = "'.$mobile.'" and u.id = m.user_id');
		if($userRow)
		{
			$findPasswordDB = new IModel('find_password');
			$dataRow = $findPasswordDB->getObj('user_id = '.$userRow['user_id'].' and hash = "'.$mobile_code.'"');
			if($dataRow)
			{
				//短信验证码已经过期
				if(time() - $dataRow['addtime'] > 3600)
				{
					$findPasswordDB->del("user_id = ".$userRow['user_id']);
					IError::show(403,"您的短信校验码已经过期了，请重新找回密码");
				}
				else
				{
					$this->redirect('/simple/restore_password/hash/'.$mobile_code.'/user_id/'.$userRow['user_id']);
				}
			}
			else
			{
				IError::show(403,"您输入的短信校验码错误");
			}
		}
		else
		{
			IError::show(403,"用户名与手机号码不匹配");
		}
	}

	//找回密码发送手机验证码短信
	function send_message_mobile()
	{
		$username = IFilter::act(IReq::get('username'));
		$mobile = IFilter::act(IReq::get('mobile'));

		if($username === null || !IValidate::name($username))
		{
			die("请输入正确的用户名");
		}

		if($mobile === null || !IValidate::mobi($mobile))
		{
			die("请输入正确的手机号码");
		}

		$userDB = new IModel('user as u , member as m');
		$userRow = $userDB->getObj('u.username = "'.$username.'" and m.mobile = "'.$mobile.'" and u.id = m.user_id');

		if($userRow)
		{
			$findPasswordDB = new IModel('find_password');
			$dataRow = $findPasswordDB->query('user_id = '.$userRow['user_id'],'*','addtime desc');
			$dataRow = current($dataRow);

			//120秒是短信发送的间隔
			if( isset($dataRow['addtime']) && (time() - $dataRow['addtime'] <= 120) )
			{
				die("申请验证码的时间间隔过短，请稍候再试");
			}
			$mobile_code = rand(10000,99999);
			$findPasswordDB->setData(array(
				'user_id' => $userRow['user_id'],
				'hash'    => $mobile_code,
				'addtime' => time(),
			));
			if($findPasswordDB->add())
			{
				$content = smsTemplate::findPassword(array('{mobile_code}' => $mobile_code));
				$result = Hsms::send($mobile,$content);
				if($result == 'success')
				{
					die('success');
				}
				die($result);
			}
		}
		else
		{
			die('手机号码与用户名不符合');
		}
	}

	/**
	 * @brief 重置密码验证
	 */
	function restore_password()
	{
		$hash = IFilter::act(IReq::get("hash"));
		$user_id = IFilter::act(IReq::get("user_id"),'int');

		if(!$hash)
		{
			IError::show(403,"找不到校验码");
		}
		$tb = new IModel("find_password");
		$addtime = time() - 3600*72;
		$where  = " `hash`='$hash' AND addtime > $addtime ";
		$where .= $this->user['user_id'] ? " and user_id = ".$this->user['user_id'] : "";

		$row = $tb->getObj($where);
		if(!$row)
		{
			IError::show(403,"校验码已经超时");
		}

		if($row['user_id'] != $user_id)
		{
			IError::show(403,"验证码不属于此用户");
		}

		$this->formAction = IUrl::creatUrl("/simple/do_restore_password/hash/$hash/user_id/".$user_id);
		$this->redirect("restore_password");
	}

	/**
	 * @brief 执行密码修改重置操作
	 */
	function do_restore_password()
	{
		$hash = IFilter::act(IReq::get("hash"));
		$user_id = IFilter::act(IReq::get("user_id"),'int');

		if(!$hash)
		{
			IError::show(403,"找不到校验码");
		}
		$tb = new IModel("find_password");
		$addtime = time() - 3600*72;
		$where  = " `hash`='$hash' AND addtime > $addtime ";
		$where .= $this->user['user_id'] ? " and user_id = ".$this->user['user_id'] : "";

		$row = $tb->getObj($where);
		if(!$row)
		{
			IError::show(403,"校验码已经超时");
		}

		if($row['user_id'] != $user_id)
		{
			IError::show(403,"验证码不属于此用户");
		}

		//开始修改密码
		$pwd   = IReq::get("password");
		$repwd = IReq::get("repassword");
		if($pwd == null || strlen($pwd) < 6 || $repwd!=$pwd)
		{
			IError::show(403,"新密码至少六位，且两次输入的密码应该一致。");
		}
		$pwd = md5($pwd);
		$tb_user = new IModel("user");
		$tb_user->setData(array("password" => $pwd));
		$re = $tb_user->update("id='{$row['user_id']}'");
		if($re !== false)
		{
			$message = "修改密码成功";
			$tb->del("`hash`='{$hash}'");
			$this->redirect("/site/success/message/".urlencode($message));
			return;
		}
		IError::show(403,"密码修改失败，请重试");
	}

    //添加收藏夹
    function favorite_add()
    {
    	$goods_id = IFilter::act(IReq::get('goods_id'),'int');
    	$message  = '';

    	if($goods_id == 0)
    	{
    		$message = '商品id值不能为空';
    	}
    	else if(!isset($this->user['user_id']) || !$this->user['user_id'])
    	{
    		$message = '请先登录';
    	}
    	else
    	{
    		$favoriteObj = new IModel('favorite');
    		$goodsRow    = $favoriteObj->getObj('user_id = '.$this->user['user_id'].' and rid = '.$goods_id);
    		if($goodsRow)
    		{
    			$message = '您已经收藏过此件商品';
    		}
    		else
    		{
    			$catObj = new IModel('category_extend');
    			$catRow = $catObj->getObj('goods_id = '.$goods_id);
    			$cat_id = $catRow ? $catRow['category_id'] : 0;

	    		$dataArray   = array(
	    			'user_id' => $this->user['user_id'],
	    			'rid'     => $goods_id,
	    			'time'    => ITime::getDateTime(),
	    			'cat_id'  => $cat_id,
	    		);
	    		$favoriteObj->setData($dataArray);
	    		$favoriteObj->add();
	    		$message = '收藏成功';

	    		//商品收藏信息更新
	    		$goodsDB = new IModel('goods');
	    		$goodsDB->setData(array("favorite" => "favorite + 1"));
	    		$goodsDB->update("id = ".$goods_id,'favorite');
    		}
    	}
		$result = array(
			'isError' => true,
			'message' => $message,
		);

    	echo JSON::encode($result);
    }

    //获取oauth登录地址
    public function oauth_login()
    {
    	$id = IFilter::act(IReq::get('id'),'int');
    	if($id)
    	{
    		$oauthObj = new Oauth($id);
			$result   = array(
				'isError' => false,
				'url'     => $oauthObj->getLoginUrl(),
			);
    	}
    	else
    	{
			$result   = array(
				'isError' => true,
				'message' => '请选择要登录的平台',
			);
    	}
    	echo JSON::encode($result);
    }

    //第三方登录回调
    public function oauth_callback()
    {
    	$oauth_name = IFilter::act(IReq::get('oauth_name'));
    	$oauthObj   = new IModel('oauth');
    	$oauthRow   = $oauthObj->getObj('file = "'.$oauth_name.'"');

    	if(!$oauth_name && !$oauthRow)
    	{
    		IError::show(403,"{$oauth_name} 第三方平台信息不存在");
    	}
		$id       = $oauthRow['id'];
    	$oauthObj = new Oauth($id);
    	$result   = $oauthObj->checkStatus($_GET);

    	if($result === true)
    	{
    		$oauthObj->getAccessToken($_GET);
	    	$userInfo = $oauthObj->getUserInfo();

	    	if(isset($userInfo['id']) && isset($userInfo['name']) && $userInfo['id'] && $userInfo['name'])
	    	{
	    		$this->bindUser($userInfo,$id);
	    		return;
	    	}
    	}
    	else
    	{
    		IError::show("回调URL参数错误");
    	}
    }

    //同步绑定用户数据
    public function bindUser($userInfo,$oauthId)
    {
    	$userObj      = new IModel('user');
    	$oauthUserObj = new IModel('oauth_user');
    	$oauthUserRow = $oauthUserObj->getObj("oauth_user_id = '{$userInfo['id']}' and oauth_id = '{$oauthId}' ",'user_id');
		if($oauthUserRow)
		{
			//清理oauth_user和user表不同步匹配的问题
			$tempRow = $userObj->getObj("id = '{$oauthUserRow['user_id']}'");
			if(!$tempRow)
			{
				$oauthUserObj->del("oauth_user_id = '{$userInfo['id']}' and oauth_id = '{$oauthId}' ");
			}
		}

    	//存在绑定账号oauth_user与user表同步正常！
    	if(isset($tempRow) && $tempRow)
    	{
    		$userRow = plugin::trigger("isValidUser",array($tempRow['username'],$tempRow['password']));
    		plugin::trigger("userLoginCallback",$userRow);
    		$callback = plugin::trigger('getCallback');
    		$callback = $callback ? $callback : "/ucenter/index";
			$this->redirect($callback);
    	}
    	//没有绑定账号
    	else
    	{
	    	$userCount = $userObj->getObj("username = '{$userInfo['name']}'",'count(*) as num');

	    	//没有重复的用户名
	    	if($userCount['num'] == 0)
	    	{
	    		$username = $userInfo['name'];
	    	}
	    	else
	    	{
	    		//随即分配一个用户名
	    		$username = $userInfo['name'].$userCount['num'];
	    	}
			$userInfo['name'] = $username;
	    	ISession::set('oauth_id',$oauthId);
	    	ISession::set('oauth_userInfo',$userInfo);
	    	$this->setRenderData($userInfo);
	    	$this->redirect('bind_user',false);
    	}
    }

	//执行绑定已存在用户
    public function bind_exists_user()
    {
    	$login_info     = IReq::get('login_info');
    	$password       = IReq::get('password');
    	$oauth_id       = IFilter::act(ISession::get('oauth_id'));
    	$oauth_userInfo = IFilter::act(ISession::get('oauth_userInfo'));

    	if(!$oauth_id || !$oauth_userInfo || !isset($oauth_userInfo['id']))
    	{
    		IError::show("缺少oauth信息");
    	}

    	if($userRow = plugin::trigger("isValidUser",array($login_info,md5($password))))
    	{
    		$oauthUserObj = new IModel('oauth_user');

    		//插入关系表
    		$oauthUserData = array(
    			'oauth_user_id' => $oauth_userInfo['id'],
    			'oauth_id'      => $oauth_id,
    			'user_id'       => $userRow['user_id'],
    			'datetime'      => ITime::getDateTime(),
    		);
    		$oauthUserObj->setData($oauthUserData);
    		$oauthUserObj->add();

    		plugin::trigger("userLoginCallback",$userRow);

			//自定义跳转页面
			$this->redirect('/site/success?message='.urlencode("登录成功！"));
    	}
    	else
    	{
    		$this->setError("用户名和密码不匹配");
    		$_GET['bind_type'] = 'exists';
    		$this->redirect('bind_user',false);
    		Util::showMessage("用户名和密码不匹配");
    	}
    }

	//执行绑定注册新用户用户
    public function bind_not_exists_user()
    {
    	$oauth_id       = IFilter::act(ISession::get('oauth_id'));
    	$oauth_userInfo = IFilter::act(ISession::get('oauth_userInfo'));

    	if(!$oauth_id || !$oauth_userInfo || !isset($oauth_userInfo['id']))
    	{
    		IError::show("缺少oauth信息");
    	}

    	//调用_userInfo注册插件
		$result = plugin::trigger('userRegAct',$_POST);
		if(is_array($result))
		{
			//插入关系表
			$oauthUserObj = new IModel('oauth_user');
			$oauthUserData = array(
				'oauth_user_id' => $oauth_userInfo['id'],
				'oauth_id'      => $oauth_id,
				'user_id'       => $result['id'],
				'datetime'      => ITime::getDateTime(),
			);
			$oauthUserObj->setData($oauthUserData);
			$oauthUserObj->add();
			$this->redirect('/site/success?message='.urlencode("注册成功！"));
		}
		else
		{
    		$this->setError($result);
    		$this->redirect('bind_user',false);
    		Util::showMessage($result);
		}
    }

	/**
	 * @brief 商户的增加动作
	 */
	public function seller_reg()
	{
		$seller_name = IValidate::name(IReq::get('seller_name')) ? IReq::get('seller_name') : "";
		$email       = IValidate::email(IReq::get('email'))      ? IReq::get('email')       : "";
		$truename    = IValidate::name(IReq::get('true_name'))   ? IReq::get('true_name')   : "";
		$phone       = IValidate::phone(IReq::get('phone'))      ? IReq::get('phone')       : "";
		$mobile      = IValidate::mobi(IReq::get('mobile'))      ? IReq::get('mobile')      : "";
		$home_url    = IValidate::url(IReq::get('home_url'))     ? IReq::get('home_url')    : "";
		//$service_num = IValidate::url(IReq::get('service_num'))  ? IReq::get('service_num')    : "";
		//$account     = IValidate::url(IReq::get('account'))      ? IReq::get('account')    : "";
        
		$password    = IFilter::act(IReq::get('password'));
		$repassword  = IFilter::act(IReq::get('repassword'));
		$province    = IFilter::act(IReq::get('province'),'int');
		$city        = IFilter::act(IReq::get('city'),'int');
		$area        = IFilter::act(IReq::get('area'),'int');
		$address     = IFilter::act(IReq::get('address'));
		$plate_id    = IFilter::act(IReq::get('plate_id'),'int');
		$store_name  = IFilter::act(IReq::get('store_name'));
		$server_num = IFilter::act(IReq::get('server_num'));
		$account = IFilter::act(IReq::get('account'));
		$seller_type = IFilter::act(IReq::get('seller_type'));
		//$user_id = IFilter::act(IReq::get('user_id'));
		if($password == '')
		{
			$errorMsg = '请输入密码！';
		}

		if($password != $repassword)
		{
			$errorMsg = '两次输入的密码不一致！';
		}

		if(!$seller_name)
		{
			$errorMsg = '填写正确的登陆用户名';
		}

		if(!$truename)
		{
			$errorMsg = '填写正确的商户真实全称';
		}

		//创建商家操作类
		$sellerDB = new IModel("seller");
		if($seller_name && $sellerDB->getObj("seller_name = '{$seller_name}'"))
		{
			$errorMsg = "登录用户名重复";
		}
		else if($truename && $sellerDB->getObj("true_name = '{$truename}'"))
		{
			$errorMsg = "商户真实全称重复";
		}else if($store_name && $sellerDB->getObj("store_name = '{$store_name}'"))
		{
			$errorMsg = "商户店铺名重复";
		}
		
		//操作失败表单回填
		if(isset($errorMsg))
		{
			$this->sellerRow = IFilter::act($_POST,'text');
			$this->redirect('seller',false);
			Util::showMessage($errorMsg);
		}
        
		//$userDB = new IModel('user');
		//$userRow = $userDB->getObj('id = '.$user_id);
		
		//自动注册用户
		
		//插入user表
		$userDB = new IModel('user');
		$auto_passwd = 123456;
		$userArray = array(
		    'username' => $seller_name,
		    'password' => md5($auto_passwd),
		);
		$userDB->setData($userArray);
		$user_id = $userDB->add();
		if(!$user_id)
		{
		    $userObj->rollback();
		    return "用户创建失败";
		}
		
		//插入member表
		$memberArray = array(
		    'user_id' => $user_id,
		    'time'    => ITime::getDateTime(),
		    'status'  => $reg_option == 1 ? 3 : 1,
		    'mobile'  => $mobile,
		    'email'   => $email,
		    'invite_one' => $userInvite_one ? $userInvite_one:"",
		    'invite_two' => $userInvite_two ? $userInvite_two:"",
		    'invite_three' => $userInvite_three ? $userInvite_three:"",
		);
		$memberObj = new IModel('member');
		$memberObj->setData($memberArray);
		$memberObj->add();
		
		$plateid = array();
		array_push($plateid, $plate_id);
		$plate_json = json_encode($plateid,JSON_UNESCAPED_UNICODE);
		
		/* print_r($plateid);
		print_r($plate_json);
		$dd = json_decode($plate_json,true);
		print_r($dd); */
		
		
		//待更新的数据
		$sellerRow = array(
			'true_name' => $truename,
			'phone'     => $phone,
			'mobile'    => $mobile,
			'email'     => $email,
			'address'   => $address, 
			'province'  => $province,
			'city'      => $city,
			'area'      => $area,
			'home_url'  => $home_url,
			'is_lock'   => 1,
		    'plates'  => $plate_json,
		    'store_name'=> $store_name,
		    'server_num'=>$server_num,
		    'account'   =>$account,
		    'seller_type'=>$seller_type,
		    'member_id' =>$user_id,
		    'member_name'=>$userRow['username'],
		);
       // print_r($sellerRow);
        //die;
		//商户资质上传
		if(isset($_FILES['paper_img']['name']) && $_FILES['paper_img']['name'])
		{
			$uploadObj = new PhotoUpload();
			$uploadObj->setIterance(false);
			$photoInfo = $uploadObj->run();
			if(isset($photoInfo['paper_img']['img']) && file_exists($photoInfo['paper_img']['img']))
			{
				$sellerRow['paper_img'] = $photoInfo['paper_img']['img'];
			}
		}

		$sellerRow['seller_name'] = $seller_name;
		$sellerRow['password']    = md5($password);
		$sellerRow['create_time'] = ITime::getDateTime();

		$sellerDB->setData($sellerRow);
		$sellerDB->add();

		//短信通知商城平台
		if($this->_siteConfig->mobile)
		{
			$content = smsTemplate::sellerReg(array('{true_name}' => $truename));
			$result = Hsms::send($this->_siteConfig->mobile,$content,0);
		}

		$this->redirect('/site/success?message='.urlencode("申请成功！请耐心等待管理员的审核"));
	}

	//添加地址ajax
	function address_add()
	{
		$id          = IFilter::act(IReq::get('id'),'int');
		$accept_name = IFilter::act(IReq::get('accept_name'));
		
		
		$province    = IFilter::act(IReq::get('province'),'int');
		$city        = IFilter::act(IReq::get('city'),'int');
		$area        = IFilter::act(IReq::get('area'),'int');
		$address     = IFilter::act(IReq::get('address'));
		$zip         = IFilter::act(IReq::get('zip'));
		$telphone    = IFilter::act(IReq::get('telphone'));
		$mobile      = IFilter::act(IReq::get('mobile'));
        $user_id     = $this->user['user_id'];

		//整合的数据
        $sqlData = array(
        	'user_id'     => $user_id,
        	'accept_name' => $accept_name,
        	'zip'         => $zip,
        	'telphone'    => $telphone,
        	'province'    => $province,
        	'city'        => $city,
        	'area'        => $area,
        	'address'     => $address,
        	'mobile'      => $mobile,
        );

        $checkArray = $sqlData;
        unset($checkArray['telphone'],$checkArray['zip'],$checkArray['user_id']);
        foreach($checkArray as $key => $val)
        {
        	if(!$val)
        	{
        		$result = array('result' => false,'msg' => '请仔细填写收货地址');
				die(JSON::encode($result));
        	}
        }

        if($user_id)
        {
        	$model = new IModel('address');
        	$model->setData($sqlData);
        	if($id)
        	{
        		$model->update("id = ".$id." and user_id = ".$user_id);
        	}
        	else
        	{
        		$id = $model->add();
        	}
        	$sqlData['id'] = $id;
        }
        //访客地址保存
        else
        {
        	ISafe::set("address",$sqlData);
        }

        $areaList = area::name($province,$city,$area);
		$sqlData['province_val'] = $areaList[$province];
		$sqlData['city_val']     = $areaList[$city];
		$sqlData['area_val']     = $areaList[$area];
		$result = array('data' => $sqlData);
		die(JSON::encode($result));
	}
	
	
	//地址选择
	function choose_address(){
	    
	    //$this->setRenderData(array('user_address'=>$addressRow));//收货人信息
	    $user_id       = ($this->user['user_id'] == null) ? 0 : $this->user['user_id'];
	    $address = new IQuery("address");
	    $address->fields = "*";
	    $address->where = "user_id = {$user_id} ORDER BY is_default desc ,update_time desc  ";
	    $res = $address->find();
	    
	    $this->setRenderData(array('address'=>$res));//免运费信息
	    $this->redirect(choose_address);
	
	}
	
	
	
	
}














