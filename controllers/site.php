<?php
/**
 * @copyright Copyright(c) 2011 aircheng.com
 * @file site.php
 * @brief
 * @author webning
 * @date 2011-03-22
 * @version 0.6
 * @note
 */
/**
 * @brief Site
 * @class Site
 * @note
 */
class Site extends IController
{
    public $layout='site';

	function init()
	{

	}

	function index()
	{
	    
	   
	    $this->index_slide = Api::run('getBannerList');
		$this->redirect('index');
	}
	
	//[首页]商品搜索
	function search_list()
	{
	    
		$this->word = IFilter::act(IReq::get('word'),'text');
		$cat_id     = IFilter::act(IReq::get('cat'),'int');
		if(preg_match("|^[\w\x7f\s*-\xff*]+$|",$this->word))
		{
			//搜索关键字
			$tb_sear     = new IModel('search');
			
			$search_info = $tb_sear->getObj('keyword = "'.$this->word.'"','id');
			
			//如果是第一页，相应关键词的被搜索数量才加1
			if($search_info && intval(IReq::get('page')) < 2 )
			{
			   
				//禁止刷新+1
				$allow_sep = "30";
				$flag = false;
				$time = ICookie::get('step');
				if(isset($time))
				{
					if (time() - $time > $allow_sep)
					{
						ICookie::set('step',time());
						$flag = true;
					}
				}
				else
				{
					ICookie::set('step',time());
					$flag = true;
				}
				if($flag)
				{
					$tb_sear->setData(array('num'=>'num + 1'));
					$tb_sear->update('id='.$search_info['id'],'num');
				}
			}
			elseif( !$search_info )
			{
				//如果数据库中没有这个词的信息，则新添
				$tb_sear->setData(array('keyword'=>$this->word,'num'=>1));
				$tb_sear->add();
			}
		}
		else
		{
			IError::show(403,'请输入正确的查询关键词');
		}
		$this->cat_id = $cat_id;
		$this->redirect('search_list');
	}

	//[site,ucenter头部分]自动完成
	function autoComplete()
	{
		$word = IFilter::act(IReq::get('word'));
		$isError = true;
		$data    = array();

		if($word != '' && $word != '%' && $word != '_')
		{
			$wordObj  = new IModel('keyword');
			$wordList = $wordObj->query('word like "'.$word.'%" and word != "'.$word.'"','word, goods_nums','',10);

			if(!empty($wordList))
			{
				$isError = false;
				$data = $wordList;
			}
		}

		//json数据
		$result = array(
			'isError' => $isError,
			'data'    => $data,
		);

		echo JSON::encode($result);
	}

	//[首页]邮箱订阅
	function email_registry()
	{
		$email  = IReq::get('email');
		$result = array('isError' => true);

		if(!IValidate::email($email))
		{
			$result['message'] = '请填写正确的email地址';
		}
		else
		{
			$emailRegObj = new IModel('email_registry');
			$emailRow    = $emailRegObj->getObj('email = "'.$email.'"');

			if(!empty($emailRow))
			{
				$result['message'] = '此email已经订阅过了';
			}
			else
			{
				$dataArray = array(
					'email' => $email,
				);
				$emailRegObj->setData($dataArray);
				$status = $emailRegObj->add();
				if($status == true)
				{
					$result = array(
						'isError' => false,
						'message' => '订阅成功',
					);
				}
				else
				{
					$result['message'] = '订阅失败';
				}
			}
		}
		echo JSON::encode($result);
	}

	//[列表页]商品
	function pro_list()
	{
		$this->catId = IFilter::act(IReq::get('cat'),'int');//分类id

		if($this->catId == 0)
		{
			IError::show(403,'缺少分类ID');
		}

		//查找分类信息
		$catObj       = new IModel('category');
		$this->catRow = $catObj->getObj('id = '.$this->catId);

		if($this->catRow == null)
		{
			IError::show(403,'此分类不存在');
		}

		//获取子分类
		$this->childId = goods_class::catChild($this->catId);
		$this->redirect('pro_list');
	}
	//咨询
	function consult()
	{
		$this->goods_id = IFilter::act(IReq::get('id'),'int');
		if($this->goods_id == 0)
		{
			IError::show(403,'缺少商品ID参数');
		}

		$goodsObj   = new IModel('goods');
		$goodsRow   = $goodsObj->getObj('id = '.$this->goods_id);
		if(!$goodsRow)
		{
			IError::show(403,'商品数据不存在');
		}

		//获取次商品的评论数和平均分
		$goodsRow['apoint'] = $goodsRow['comments'] ? round($goodsRow['grade']/$goodsRow['comments']) : 0;

		$this->goodsRow = $goodsRow;
		$this->redirect('consult');
	}

	//咨询动作
	function consult_act()
	{
		$goods_id   = IFilter::act(IReq::get('goods_id','post'),'int');
		$captcha    = IFilter::act(IReq::get('captcha','post'));
		$question   = IFilter::act(IReq::get('question','post'));
		$_captcha   = ISafe::get('captcha');
		$message    = '';

    	if(!$captcha || !$_captcha || $captcha != $_captcha)
    	{
    		$message = '验证码输入不正确';
    	}
    	else if(!$question)
    	{
    		$message = '咨询内容不能为空';
    	}
    	else if(!$goods_id)
    	{
    		$message = '商品ID不能为空';
    	}
    	else
    	{
    		$goodsObj = new IModel('goods');
    		$goodsRow = $goodsObj->getObj('id = '.$goods_id);
    		if(!$goodsRow)
    		{
    			$message = '不存在此商品';
    		}
    	}

		//有错误情况
    	if($message)
    	{
    		IError::show(403,$message);
    	}
    	else
    	{
			$dataArray = array(
				'question' => $question,
				'goods_id' => $goods_id,
				'user_id'  => isset($this->user['user_id']) ? $this->user['user_id'] : 0,
				'time'     => ITime::getDateTime(),
			);
			$referObj = new IModel('refer');
			$referObj->setData($dataArray);
			$referObj->add();
			plugin::trigger('setCallback','/site/products/id/'.$goods_id);
			$this->redirect('/site/success');
    	}
	}

	//公告详情页面
	function notice_detail()
	{
		$this->notice_id = IFilter::act(IReq::get('id'),'int');
		if($this->notice_id == '')
		{
			IError::show(403,'缺少公告ID参数');
		}
		else
		{
			$noObj           = new IModel('announcement');
			$this->noticeRow = $noObj->getObj('id = '.$this->notice_id);
			if(empty($this->noticeRow))
			{
				IError::show(403,'公告信息不存在');
			}
			$this->redirect('notice_detail');
		}
	}

	//文章列表页面
	function article()
	{
		$catId  = IFilter::act(IReq::get('id'),'int');
		$catRow = Api::run('getArticleCategoryInfo',$catId);
		$queryArticle = $catRow ? Api::run('getArticleListByCatid',$catRow['id']) : Api::run('getArticleList');
		$this->setRenderData(array("catRow" => $catRow,'queryArticle' => $queryArticle));
		$this->redirect('article');
	}

	//文章详情页面
	function article_detail()
	{
		$this->article_id = IFilter::act(IReq::get('id'),'int');
		if($this->article_id == '')
		{
			IError::show(403,'缺少咨询ID参数');
		}
		else
		{
			$articleObj       = new IModel('article');
			$this->articleRow = $articleObj->getObj('id = '.$this->article_id);
			if(empty($this->articleRow))
			{
				IError::show(403,'资讯文章不存在');
				exit;
			}

			//关联商品
			$this->relationList = Api::run('getArticleGoods',array("#article_id#",$this->article_id));
			$this->redirect('article_detail');
		}
	}

	//商品展示
	function products()
	{
		$goods_id = IFilter::act(IReq::get('id'),'int');
		$seller_id = IFilter::act(IReq::get('seller_id'),'int');
		
	   
		
		$seller_name = IFilter::act(IReq::get('seller_name'));
		if(!$goods_id)
		{
			IError::show(403,"传递的参数不正确");
			exit;
		}

		//使用商品id获得商品信息
		$tb_goods = new IModel('goods');
		$goods_info = $tb_goods->getObj('id='.$goods_id." AND is_del=0");
		if(!$goods_info)
		{
			IError::show(403,"这件商品不存在");
			exit;
		}

		if($goods_info['xiaoshou_type'] == 1){
		    if(empty($seller_id) && $seller_name!="micar"){
		        IError::show(403,"该商品不存在");
		        exit;
		    }
		    $goods_info['seller_name'] = $seller_name;
		}
		
		//品牌名称
		if($goods_info['brand_id'])
		{
			$tb_brand = new IModel('brand');
			$brand_info = $tb_brand->getObj('id='.$goods_info['brand_id']);
			if($brand_info)
			{
				$goods_info['brand'] = $brand_info['name'];
			}
		}
		
		//获取商品分类
		$categoryObj = new IModel('category_extend as ca,category as c');
		$categoryList= $categoryObj->query('ca.goods_id = '.$goods_id.' and ca.category_id = c.id','c.id,c.name','ca.id desc',1);
		$categoryRow = null;
		if($categoryList)
		{
			$categoryRow = current($categoryList);
		}
		$goods_info['category'] = $categoryRow ? $categoryRow['id'] : 0;

		//商品图片
		$tb_goods_photo = new IQuery('goods_photo_relation as g');
		$tb_goods_photo->fields = 'p.id AS photo_id,p.img ';
		$tb_goods_photo->join = 'left join goods_photo as p on p.id=g.photo_id ';
		$tb_goods_photo->where =' g.goods_id='.$goods_id;
		$goods_info['photo'] = $tb_goods_photo->find();

		//商品是否参加促销活动(团购，抢购)
		$goods_info['promo']     = IReq::get('promo')     ? IReq::get('promo') : '';
		$goods_info['active_id'] = IReq::get('active_id') ? IFilter::act(IReq::get('active_id'),'int') : 0;
		if($goods_info['promo'])
		{
			$activeObj    = new Active($goods_info['promo'],$goods_info['active_id'],$this->user['user_id'],$goods_id);
			$activeResult = $activeObj->data();
			if(is_string($activeResult))
			{
				IError::show(403,$activeResult);
			}
			else
			{
				$goods_info[$goods_info['promo']] = $activeResult;
			}
		}

		//获得扩展属性
		$tb_attribute_goods = new IQuery('goods_attribute as g');
		$tb_attribute_goods->join  = 'left join attribute as a on a.id=g.attribute_id ';
		$tb_attribute_goods->fields=' a.name,g.attribute_value ';
		$tb_attribute_goods->where = "goods_id='".$goods_id."' and attribute_id!=''";
		$goods_info['attribute'] = $tb_attribute_goods->find();

		//购买记录
		$tb_shop = new IQuery('order_goods as og');
		$tb_shop->join = 'left join order as o on o.id=og.order_id';
		$tb_shop->fields = 'count(*) as totalNum';
		$tb_shop->where = 'og.goods_id='.$goods_id.' and o.status = 5';
		$shop_info = $tb_shop->find();
		$goods_info['buy_num'] = 0;
		if($shop_info)
		{
			$goods_info['buy_num'] = $shop_info[0]['totalNum'];
		}

		//购买前咨询
		$tb_refer    = new IModel('refer');
		$refeer_info = $tb_refer->getObj('goods_id='.$goods_id,'count(*) as totalNum');
		$goods_info['refer'] = 0;
		if($refeer_info)
		{
			$goods_info['refer'] = $refeer_info['totalNum'];
		}

		//网友讨论
		$tb_discussion = new IModel('discussion');
		$discussion_info = $tb_discussion->getObj('goods_id='.$goods_id,'count(*) as totalNum');
		$goods_info['discussion'] = 0;
		if($discussion_info)
		{
			$goods_info['discussion'] = $discussion_info['totalNum'];
		}

		//获得商品的价格区间
		$tb_product = new IModel('products');
		$product_info = $tb_product->getObj('goods_id='.$goods_id,'max(sell_price) as maxSellPrice ,max(market_price) as maxMarketPrice');
		if(isset($product_info['maxSellPrice']) && $goods_info['sell_price'] != $product_info['maxSellPrice'])
		{
			$goods_info['sell_price']   .= "-".$product_info['maxSellPrice'];
			$goods_info['market_price'] .= "-".$product_info['maxMarketPrice'];
		}

		//获得会员价
		$countsumInstance = new countsum();
		$goods_info['group_price'] = $countsumInstance->getGroupPrice($goods_id,'goods');
        
		//获取商家信息
		if($goods_info['seller_id'] || $goods_info['seller_id'] == 0)
		{
		    
			$sellerDB = new IModel('seller');
			$goods_info['seller'] = $sellerDB->getObj('id = '.$goods_info['seller_id']);
		}
		
		//增加浏览次数
		$visit    = ISafe::get('visit');
		$checkStr = "#".$goods_id."#";
		if($visit && strpos($visit,$checkStr) !== false)
		{
		}
		else
		{
			$tb_goods->setData(array('visit' => 'visit + 1'));
			$tb_goods->update('id = '.$goods_id,'visit');
			$visit = $visit === null ? $checkStr : $visit.$checkStr;
			ISafe::set('visit',$visit);
		}
		
		
		$this->setRenderData($goods_info);
		$this->redirect('products');
	}
	//商品讨论更新
	function discussUpdate()
	{
		$goods_id = IFilter::act(IReq::get('id'),'int');
		$content  = IFilter::act(IReq::get('content'),'text');
		$captcha  = IReq::get('captcha');
		$_captcha = ISafe::get('captcha');
		$return   = array('isError' => true , 'message' => '');

		if(!$this->user['user_id'])
		{
			$return['message'] = '请先登录系统';
		}
    	else if(!$captcha || !$_captcha || $captcha != $_captcha)
    	{
    		$return['message'] = '验证码输入不正确';
    	}
    	else if(trim($content) == '')
    	{
    		$return['message'] = '内容不能为空';
    	}
    	else
    	{
    		$return['isError'] = false;

			//插入讨论表
			$tb_discussion = new IModel('discussion');
			$dataArray     = array(
				'goods_id' => $goods_id,
				'user_id'  => $this->user['user_id'],
				'time'     => ITime::getDateTime(),
				'contents' => $content,
			);
			$tb_discussion->setData($dataArray);
			$tb_discussion->add();

			$return['time']     = $dataArray['time'];
			$return['contents'] = $content;
			$return['username'] = $this->user['username'];
    	}
    	echo JSON::encode($return);
	}

	//获取货品数据
	function getProduct()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$specJSON = IReq::get('specJSON');
		if(!$specJSON || !is_array($specJSON))
		{
			echo JSON::encode(array('flag' => 'fail','message' => '规格值不符合标准'));
			exit;
		}

		//获取货品数据
		$tb_products = new IModel('products');
		$procducts_info = $tb_products->getObj("goods_id = ".$goods_id." and spec_array = '".IFilter::act(JSON::encode($specJSON))."'");

		//匹配到货品数据
		if(!$procducts_info)
		{
			echo JSON::encode(array('flag' => 'fail','message' => '没有找到相关货品'));
			exit;
		}

		//获得会员价
		$countsumInstance = new countsum();
		$group_price = $countsumInstance->getGroupPrice($procducts_info['id'],'product');

		//会员价格
		if($group_price !== null)
		{
			$procducts_info['group_price'] = $group_price;
		}

		echo JSON::encode(array('flag' => 'success','data' => $procducts_info));
	}

	//顾客评论ajax获取
	function comment_ajax()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$page     = IFilter::act(IReq::get('page'),'int') ? IReq::get('page') : 1;

		$commentDB = new IQuery('comment as c');
		$commentDB->join   = 'left join goods as go on c.goods_id = go.id AND go.is_del = 0 left join user as u on u.id = c.user_id';
		$commentDB->fields = 'u.head_ico,u.username,c.*';
		$commentDB->where  = 'c.goods_id = '.$goods_id.' and c.status = 1';
		$commentDB->order  = 'c.id desc';
		$commentDB->page   = $page;
		$data     = $commentDB->find();
		$pageHtml = $commentDB->getPageBar("javascript:void(0);",'onclick="comment_ajax([page])"');

		echo JSON::encode(array('data' => $data,'pageHtml' => $pageHtml));
	}

	//购买记录ajax获取
	function history_ajax()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$page     = IFilter::act(IReq::get('page'),'int') ? IReq::get('page') : 1;

		$orderGoodsDB = new IQuery('order_goods as og');
		$orderGoodsDB->join   = 'left join order as o on og.order_id = o.id left join user as u on o.user_id = u.id';
		$orderGoodsDB->fields = 'o.user_id,og.goods_price,og.goods_nums,o.create_time as completion_time,u.username';
		$orderGoodsDB->where  = 'og.goods_id = '.$goods_id.' and o.status = 5';
		$orderGoodsDB->order  = 'o.create_time desc';
		$orderGoodsDB->page   = $page;

		$data = $orderGoodsDB->find();
		$pageHtml = $orderGoodsDB->getPageBar("javascript:void(0);",'onclick="history_ajax([page])"');

		echo JSON::encode(array('data' => $data,'pageHtml' => $pageHtml));
	}

	//讨论数据ajax获取
	function discuss_ajax()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$page     = IFilter::act(IReq::get('page'),'int') ? IReq::get('page') : 1;

		$discussDB = new IQuery('discussion as d');
		$discussDB->join = 'left join user as u on d.user_id = u.id';
		$discussDB->where = 'd.goods_id = '.$goods_id;
		$discussDB->order = 'd.id desc';
		$discussDB->fields = 'u.username,d.time,d.contents';
		$discussDB->page = $page;

		$data = $discussDB->find();
		$pageHtml = $discussDB->getPageBar("javascript:void(0);",'onclick="discuss_ajax([page])"');

		echo JSON::encode(array('data' => $data,'pageHtml' => $pageHtml));
	}

	//买前咨询数据ajax获取
	function refer_ajax()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$page     = IFilter::act(IReq::get('page'),'int') ? IReq::get('page') : 1;

		$referDB = new IQuery('refer as r');
		$referDB->join = 'left join user as u on r.user_id = u.id';
		$referDB->where = 'r.goods_id = '.$goods_id;
		$referDB->order = 'r.id desc';
		$referDB->fields = 'u.username,u.head_ico,r.time,r.question,r.reply_time,r.answer';
		$referDB->page = $page;

		$data = $referDB->find();
		$pageHtml = $referDB->getPageBar("javascript:void(0);",'onclick="refer_ajax([page])"');

		echo JSON::encode(array('data' => $data,'pageHtml' => $pageHtml));
	}

	//评论列表页
	function comments_list()
	{
		$id   = IFilter::act(IReq::get("id"),'int');
		$type = IFilter::act(IReq::get("type"));
		$data = array();

		//评分级别
		$type_config = array('bad'=>'1','middle'=>'2,3,4','good'=>'5');
		$point       = isset($type_config[$type]) ? $type_config[$type] : "";

		//查询评价数据
		$this->commentQuery = Api::run('getListByGoods',$id,$point);
		$this->commentCount = Comment_Class::get_comment_info($id);
		$this->goods        = Api::run('getGoodsInfo',array("#id#",$id));
		if(!$this->goods)
		{
			IError::show("商品信息不存在");
		}
		$this->redirect('comments_list');
	}

	//提交评论页
	function comments()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		if(!$id)
		{
			IError::show(403,"传递的参数不完整");
		}

		if(!isset($this->user['user_id']) || $this->user['user_id']==null )
		{
			IError::show(403,"登录后才允许评论");
		}

		$result = Comment_Class::can_comment($id,$this->user['user_id']);
		if(is_string($result))
		{
			IError::show(403,$result);
		}

		$this->comment      = $result;
		$this->commentCount = Comment_Class::get_comment_info($result['goods_id']);
		$this->goods        = Api::run('getGoodsInfo',array("#id#",$result['goods_id']));
		$this->redirect("comments");
	}

	/**
	 * @brief 进行商品评论 ajax操作
	 */
	public function comment_add()
	{
		$id      = IFilter::act(IReq::get('id'),'int');
		$content = IFilter::act(IReq::get("contents"));
		if(!$id || !$content)
		{
			IError::show(403,"填写完整的评论内容");
		}

		if(!isset($this->user['user_id']) || !$this->user['user_id'])
		{
			IError::show(403,"未登录用户不能评论");
		}

		$data = array(
			'point'        => IFilter::act(IReq::get('point'),'float'),
			'contents'     => $content,
			'status'       => 1,
			'comment_time' => ITime::getNow("Y-m-d"),
		);

		if($data['point']==0)
		{
			IError::show(403,"请选择分数");
		}

		$result = Comment_Class::can_comment($id,$this->user['user_id']);
		if(is_string($result))
		{
			IError::show(403,$result);
		}

		$tb_comment = new IModel("comment");
		$tb_comment->setData($data);
		$re         = $tb_comment->update("id={$id}");

		if($re)
		{
			$commentRow = $tb_comment->getObj('id = '.$id);

			//同步更新goods表,comments,grade
			$goodsDB = new IModel('goods');
			$goodsDB->setData(array(
				'comments' => 'comments + 1',
				'grade'    => 'grade + '.$commentRow['point'],
			));
			$goodsDB->update('id = '.$commentRow['goods_id'],array('grade','comments'));

			//同步更新seller表,comments,grade
			$sellerDB = new IModel('seller');
			$sellerDB->setData(array(
				'comments' => 'comments + 1',
				'grade'    => 'grade + '.$commentRow['point'],
			));
			$sellerDB->update('id = '.$commentRow['seller_id'],array('grade','comments'));
			$this->redirect("/site/comments_list/id/".$commentRow['goods_id']);
		}
		else
		{
			IError::show(403,"评论失败");
		}
	}

	function pic_show()
	{
		$this->layout="";

		$id   = IFilter::act(IReq::get('id'),'int');
		$item = Api::run('getGoodsInfo',array('#id#',$id));
		if(!$item)
		{
			IError::show(403,'商品信息不存在');
		}
		$photo = Api::run('getGoodsPhotoRelationList',array('#id#',$id));
		$this->setRenderData(array("id" => $id,"item" => $item,"photo" => $photo));
		$this->redirect("pic_show");
	}

	function help()
	{
		$id       = IFilter::act(IReq::get("id"),'int');
		$tb_help  = new IModel("help");
		$help_row = $tb_help->getObj("id={$id}");
		if(!$help_row)
		{
			IError::show(404,"您查找的页面已经不存在了");
		}
		$tb_help_cat    = new IModel("help_category");
		$this->cat_row  = $tb_help_cat->getObj("id={$help_row['cat_id']}");
		$this->help_row = $help_row;
		$this->redirect("help");
	}

	function help_list()
	{
		$id          = IFilter::act(IReq::get("id"),'int');
		$tb_help_cat = new IModel("help_category");
		$cat_row     = $tb_help_cat->getObj("id={$id}");

		//帮助分类数据存在
		if($cat_row)
		{
			$this->helpQuery = Api::run('getHelpListByCatId',$id);
			$this->cat_row   = $cat_row;
		}
		else
		{
			$this->helpQuery = Api::run('getHelpList');
			$this->cat_row   = array('id' => 0,'name' => '站点帮助');
		}
		$this->redirect("help_list");
	}

	//团购页面
	function groupon()
	{
		$id = IFilter::act(IReq::get("id"),'int');

		//指定某个团购
		if($id)
		{
			$this->regiment_list = Api::run('getRegimentRowById',array('#id#',$id));
			$this->regiment_list = $this->regiment_list ? array($this->regiment_list) : array();
		}
		else
		{
			$this->regiment_list = Api::run('getRegimentList');
		}

		if(!$this->regiment_list)
		{
			IError::show('当前没有可以参加的团购活动');
		}

		//往期团购
		$this->ever_list = Api::run('getEverRegimentList');
		$this->redirect("groupon");
	}

	//品牌列表页面
	function brand()
	{
		$id   = IFilter::act(IReq::get('id'),'int');
		$name = IFilter::act(IReq::get('name'));
		$this->setRenderData(array('id' => $id,'name' => $name));
		$this->redirect('brand');
	}

	//品牌专区页面
	function brand_zone()
	{
		$brandId  = IFilter::act(IReq::get('id'),'int');
		$brandRow = Api::run('getBrandInfo',$brandId);
		if(!$brandRow)
		{
			IError::show(403,'品牌信息不存在');
		}
		$this->setRenderData(array('brandId' => $brandId,'brandRow' => $brandRow));
		$this->redirect('brand_zone');
	}

	//商家主页
	function home()
	{
		$seller_id = IFilter::act(IReq::get('id'),'int');
		
		
		//保存销售id到session
		$xiaoshouid  = IFilter::act(ISession::get('xiaoshouid'));
		if(empty($xiaoshouid)){
		    ISession::set('xiaoshouid',$seller_id);
		}
		
		$sellerRow = Api::run('getSellerInfo',$seller_id);
		if(!$sellerRow)
		{
			IError::show(403,'商户信息不存在');
		}
		$this->home_slide =explode(',',$sellerRow['store_slide']);
		$this->setRenderData(array('sellerRow' => $sellerRow,'seller_id' => $seller_id));
		$this->redirect('home');
	}
	
	
	//手机版聊天处理
	function mobil_chat(){
	    //die("ok");
	    //print_r($_GET);
	   //die; 
	    /*
	     * 聊天处理
	     * 1.获取商家的member id
	     * 2.获取用户的opendi ，用于 换取member id
	     * 3.查询商家的member id 
	     * 
	     * */
	    
	    $openId = ISession::get('wechat_openid');
	    //$openId = "o7WLlwS6UmVJ2nPgYrqBf7eP7EFg";
	    if(empty($openId)){
	        $f_id = $_GET['f_id'];
	        if(empty($f_id)){
	            echo "请重新登陆";
	            return ;
	        }
	    }
	    
	    //用openid获取member id 
	    $oauth_user = new IModel('oauth_user');
	    $wechatuser = $oauth_user->getObj("oauth_user_id = '$openId' ");
	    $f_id = $wechatuser['user_id'];
	    $user_model = new IModel('user');
	    
	    $user = $user_model->getObj("id = '$f_id' ");
	    $f_name = $user['username'];
	    $f_ava = $user['head_ico'];
	    if(empty($f_ava)){
	        $f_ava = "http://www.micarline.net/upload/car.jpg";
	    }
	    
	    $seller_id = $_GET['t_id'];
	    //$seller_id =45;
	    $seller_model = new IModel('seller');
	    $seller = $seller_model->getObj("id = '$seller_id' ");
	    
	    $t_id = $seller['member_id'];
	    $t_name = $seller['store_name'];
	    $t_user = $user_model->getObj("id = '$t_id' ");
	    $t_ava = $t_user['head_ico'];
	    if(empty($f_ava)){
	        
    	    $t_ava = "http://www.micarline.net/upload/car.jpg";
	    }
	    //添加临时好友
	    $model = new IModel('friends');
	    
	    $fquery = new IQuery('friends as f');
	    $where = ('f.f_id = '.$f_id .' AND '.'f.t_id = '.$t_id).' OR '.('f.f_id = '.$t_id.' AND '.'f.t_id = '.$f_id);
	    $fquery->where = $where;
	    $result = $fquery->find();
	    //不存在数据
	    if(!$result){
	        $data = array(
	            "f_id"      => $f_id,
	            'f_name'    =>$f_name,
	            't_id'      =>$t_id,
	            't_name'    =>$t_name,
	            'addtime'   =>date("Y-m-d H:i:s")
	        );
	        
	        $model->setData($data);
	        $addresult = $model->add();
	    }
	    
	    $arr = array(
	        "t_id" =>$t_id,
	        "t_name"=>$t_name,
	        "t_ava" =>$t_ava,
	        'f_id' =>$f_id,
	        'f_name'   =>$f_name,
	        "f_ava"    =>$f_ava
	    );
	    
	    $date = array(
	        "item"=> $arr
	    );
	    
	    //die("ok");
	    
	    $this->setRenderData($date);
	    //$this->setRenderData(array("bbb"=>"aaaaa"));
	    $this->redirect('chat',false);
	    
	}
	
	//获取微信用户对应的member_id
	function getWechatId(){
	    $openId = ISession::get('wechat_openid');
	    
	    //$openId = "o7WLlwS6UmVJ2nPgYrqBf7eP7EFg";
	    if(empty($openId)){
	        $f_id = $_GET['f_id'];
	        
	        if(empty($f_id)){
	            echo "请重新登陆";
	            return ;
	        }
	        
	        return $f_id;
	    }
	    
	    //用openid获取member id
	    $oauth_user = new IModel('oauth_user');
	    $wechatuser = $oauth_user->getObj("oauth_user_id = '$openId' ");
	    $f_id = $wechatuser['user_id'];
	    $user_model = new IModel('user');
	     
	    $user = $user_model->getObj("id = '$f_id' ");
	    
	    return $user['id'];
	}
	
	
	
	
	
	//店铺搜索
	function store_seach(){
	    
	    
	    $world = $_GET['word'];
	   // echo $world;
	    $store = new IQuery("seller");
	    $store->where = "store_name like '%$world%' and seller_type = '0'";
	    $res = $store->find();
	    //print_r($res);
	    //$page = $store->getPageBar();
	    //print_r($page);die;
	    $this->setRenderData(array("dates"=>$res,"seach_type"=>"store","world"=>$world));
	    $this->redirect('search_list',false);
	}
	
	//手机店铺搜索
	function moble_store_seach(){
	    $world = $_GET['word'];
	    $store = new IQuery("seller");
	    $store->where = "store_name like '%$world%' and seller_type = '0'";
	    $res = $store->find();
	    $res = json_encode($res,JSON_UNESCAPED_UNICODE);
	    echo $res;
	}
	
	//微信获取未读消息
	function weidu(){
	   
	    $u_id = $this->getWechatId();
	    
	    if($u_id == "请重新登陆" || $u_id == ""){
	        echo "";
	        return ;
	    }
	    
	    $chat_msg = new IQuery("chat_msg");
	    $chat_msg->fields = "COUNT(*) AS num,f_id,f_name,r_state";
	    $chat_msg->where  = "r_state = 2 AND t_id = ".$u_id;
	    $chat_msg->group  = "f_id";
	    $date = $chat_msg->find();
	    
	    if(empty($date)){
	        echo "";
	        return ;
	    }
	    echo json_encode($date,JSON_UNESCAPED_UNICODE);
	    
	}
	
	function test(){
	    echo "ok_test";
	}
	
	function stores_seach(){
	    $this->redirect('stores_seach');
	}
	
	//微信获取未读消息 且跳转
	function userchatlist(){
	    $u_id = $this->getWechatId();
	     
	    if($u_id == "请重新登陆" || $u_id == ""){
	        echo "请重新登陆";
	        return ;
	    }
	    
	    $chat_msg = new IQuery("chat_msg");
	    $chat_msg->fields = "COUNT(*) AS num,f_id,f_name,r_state";
	    $chat_msg->where  = "r_state = 2 AND t_id = ".$u_id;
	    $chat_msg->group  = "f_id";
	    $date = $chat_msg->find();
	   
	    /*if(empty($date)){
	        echo "";
	        return ;
	    }*/
	    //$res = json_encode($date,JSON_UNESCAPED_UNICODE);
	    $this->setRenderData(array("dates"=>$date));
	    $this->redirect('userchatlist',false);
	}
	
	//筛选积分商品
	function point_goods(){
	    //$plate = ISession::get('plate_type');
	    
	    $point_model = new IQuery("goods");
	    
	    //排序字段
	    $order = "sale";
	    $by = "desc";
	    if(!empty($_GET['order'])){
	        $order = $_GET['order'];
	    }
	    
	    if(!empty($_GET['by'])){
	        $by = $_GET['by'];
	    }
	    
	    $point_model->where = "isjifen=1 ORDER BY $order $by";
	    //$point_model->limit = 15;
	    $result = $point_model->find();
	    
	    if(!empty($_GET['aj'])){
	        echo json_encode($result,JSON_UNESCAPED_UNICODE);
	        return ;
	    }
	    
	    $this->setRenderData(array("dates"=>$result));
	    $this->redirect('point_goods');
	}
	
	//分页获取数据
	function getGoodsByPage(){
	    $page = $_GET['page'];
	    $order = $_GET['order'];
	    $by = $_GET['by'];
	    
	    
	    //$plate = ISession::get('plate_type');
	    $point_model = new IQuery("goods");
	    $maxdate = 10;
	    
	    $point_model->where = "isjifen=1 ORDER BY $order $by";
	    //$point_model->limit = ($page-1)*$maxdate .','.$page*$maxdate;
	    $point_model->limit = ($page-1)*$maxdate.",$maxdate";
	    $result = $point_model->find();
	    //echo ($page-1)*$maxdate .','.$page*$maxdate;
	    
	    echo json_encode($result,JSON_UNESCAPED_UNICODE);
	   // echo $page." ".$order." ".$by;
	}
	
	//电脑版积分商品
	function points(){
	    
	    //$plate = ISession::get('plate_type');
	    $point_model = new IQuery("goods");
	    //排序字段
	    $order = "sale";
	    $by = "desc";
	    
	    if(!empty($_GET['order'])){
	        //echo '非空';
	        $order = $_GET['order'];
	        if($order == 'cpoint'){
	            $order = 'grade';
	        }else if($order == 'price'){
	            $order = 'sell_price';
	        }else if($order == 'new'){
	            $order == 'up_time';
	        }else{
	            $order = "sale";
	        }
	    }
	     
	    if(!empty($_GET['by'])){
	        $by = $_GET['by'];
	    }
	    
// 	    echo "order is ".$order;
	    $point_model->where = "isjifen=1 ORDER BY $order $by";
	    $point_model->page = 1;
	    $result = $point_model->find();
	    $this->queryobj = $point_model ;
	    
// 	    echo "<pre>";
// 	    print_r($result);
// 	    echo "</pre>";
	    
	    $this->setRenderData(array("resultData"=>$result));
	    $this->redirect('point_goods',false);
	}
	
	
	//去搜索
	function goseachpage(){
	    //die("ok");
	    $this->redirect('goseachpage');
	}
	
	//商品搜索
	function myseach(){
	    
	    
	    $type = $_GET['type'];
	 
	    //$order = $_GET['order'] ;
	    
	  
	    
	  
        
	    
	    
	    if($type == 'goods'){
	        
	        $page = $_GET['page'] == "" ? 1:$_GET['page'];
	        $by = $_GET['by'] == "" ? 'desc' :$_GET['by'];
	        $order = 'sale';
	        if(!empty($_GET['order'])){
	            //echo '非空';
	            $order = $_GET['order'];
	            if($order == 'cpoint'){
	                $order = 'grade';
	            }else if($order == 'price'){
	                $order = 'sell_price';
	            }else if($order == 'new'){
	                $order  = 'up_time';
	            }else{
	                $order = "sale";
	            }
	        }
	        
	        
	        $world = $_GET['content'];
	        $seach_model = new IQuery('goods');
	        $seach_model->where = "name like '%$world%' and isjifen = '0' AND xiaoshou_type = '0' ORDER BY $order $by ";
	        $seach_model->page = $page;
	        $result = $seach_model->find();
	        
	        $this->setRenderData(array("resultData"=>$result,"goodsObj"=>$seach_model));
	        $this->redirect('myseach');
	    }else if($type == 'store'){
	        
	        
	        $world = $_GET['content'];
	        $seach_model = new IQuery('seller');
	        $seach_model->where = "store_name like '%$world%' and seller_type = '0'";
	        $res = $seach_model->find();
	        
	        $this->setRenderData(array("resultData"=>$res));
	        $this->redirect('myseach_store',false);
	        //$res = json_encode($res,JSON_UNESCAPED_UNICODE);
	        //echo $res;
	    }
	   
	   
	}
	
	//微信用户用户绑定商家
	function banguser(){
	    //wecharbang
	    //print_r($_POST);
	    $openId = ISession::get('wechat_openid');
	    //$openId =  "123456777";
	    $sellName = $_POST['user'];
	    $password = $_POST['pwd'];
	    
	    $db_seller     = new IModel('seller');
	    	
	    $seller = $db_seller->getObj("seller_name = '$sellName'");
	    if(!empty($seller['password'])){
	        $md_pwd = md5($password) ;
	        //绑定成功
	        if($md_pwd == $seller['password']){
	            
	            $db_seller->setData(array('wecharbang'=>$openId));
	            $db_seller->update('id='.$seller['id']);
	            
	            //绑定成功
	            echo 1;
	            return ;
	        }else{
	            //账号或密码错误
	            echo 0;
	            return ;
	        }
	    }
	    
	    //用户不存在
	    echo 2;
	    return ;
	}
	
	
	//地图搜索
	function map_seach(){
	    $this->redirect('map_seach');
	}
	
	//地图搜索店铺
	function mapStore_seach(){
	    
	    $world = $_GET['content'];
	    $seach_model = new IQuery('seller');
	    $seach_model->where = "store_name like '%$world%' and seller_type = '0' and gps!='' ";
	    $res = $seach_model->find();
	    echo json_encode($res,JSON_UNESCAPED_UNICODE);
	}
	
	function getstores(){
	    $seller_obj= new IModel('seller');
	    $res = $seller_obj->query('is_del = 0 and gps!="" ');
	    
	    $json_data = json_encode($res,JSON_UNESCAPED_UNICODE);
	    
	    $this->setRenderData(array("resultData"=>$json_data));
	    $this->redirect('gomap',false);
	}
	
	function change_map(){
	    
	    $seller_obj= new IModel('seller');
	    $res = $seller_obj->query('is_del = 0 and gps!="" ');
	     
	    $json_data = json_encode($res,JSON_UNESCAPED_UNICODE);
	     
	    $this->setRenderData(array("resultData"=>$json_data));
	    $this->redirect('change_map');
	    
	    
	}
	
	
	
}



