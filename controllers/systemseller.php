<?php
/**
 * @brief 商家登录控制器
 * @class Seller
 * @author chendeshan
 * @datetime 2014/7/19 15:18:56
 */
class SystemSeller extends IController
{
	public $layout = '';

	/**
	 * @brief 商家登录动作
	 */
	public function login()
	{
		$seller_name = IFilter::act(IReq::get('username'));
		$password    = IReq::get('password');
		$message     = '';

		if($seller_name == '')
		{
			$message = '登录名不能为空';
		}
		else if($password == '')
		{
			$message = '密码不能为空';
		}
        else
		{
			$sellerObj = new IModel('seller');
			$sellerRow = $sellerObj->getObj('seller_name = "'.$seller_name.'" and is_del = 0 and is_lock = 0');
			if($sellerRow && ($sellerRow['password'] == md5($password)))
			{
				$dataArray = array(
					'login_time' => ITime::getDateTime(),
				);
				$sellerObj->setData($dataArray);
				$where = 'id = '.$sellerRow["id"];
				$sellerObj->update($where);
                
				
				//存入私密数据
				ISafe::set('seller_id',$sellerRow['id']);
				ISafe::set('seller_name',$sellerRow['seller_name']);
				ISafe::set('seller_pwd',$sellerRow['password']);
				
				
				//查出对应的user
				$user = new IQuery('user as u');
				$user->join = 'left join member as m on u.id = m.user_id';
				$user->fields = '*';
				$user->where = 'u.id = '.$sellerRow['member_id'];
				$resulr = $user->find();
				
				$userRow = array(
				    "id"  => $resulr[0]['id'],
				    "username" => $resulr[0]['username'],
				    "password"  =>$resulr[0]['password'],
				    "head_ico" =>$resulr[0]['head_ico'],
				    "last_login"=>$resulr[0]['last_login']
				    
				);
				$this->userLoginCallback($userRow);
				//print_r($userRow);die;
				$this->redirect('/seller/index');
			}
			else
			{
				$message = '用户名与密码不匹配';
			}
		}

		if($message != '')
		{
			$this->redirect('index',false);
			Util::showMessage($message);
		}
	}
    
	//自动登录
	public function userLoginCallback($userRow)
	{
	    //用户私密数据
	    ISafe::set('user_id',$userRow['id']);
	    ISafe::set('username',$userRow['username']);
	    ISafe::set('user_pwd',$userRow['password']);
	    ISafe::set('head_ico',isset($userRow['head_ico']) ? $userRow['head_ico'] : '');
	    ISafe::set('last_login',isset($userRow['last_login']) ? $userRow['last_login'] : '');
	
	    //更新最后一次登录时间
	    $memberObj = new IModel('member');
	    $dataArray = array(
	        'last_login' => ITime::getDateTime(),
	    );
	    $memberObj->setData($dataArray);
	    $where     = 'user_id = '.$userRow["id"];
	    $memberObj->update($where);
	
	    //根据经验值分会员组
	    $memberRow = $memberObj->getObj($where,'exp');
	    $groupObj  = new IModel('user_group');
	    $groupRow  = $groupObj->getObj($memberRow['exp'].' between minexp and maxexp and minexp > 0 and maxexp > 0','id','discount desc');
	    if($groupRow)
	    {
	        $dataArray = array('group_id' => $groupRow['id']);
	        $memberObj->setData($dataArray);
	        $memberObj->update($where);
	    }
	}
	
	//后台登出
	function logout()
	{
		plugin::trigger('clearSeller');
    	$this->redirect('index');
	}
}