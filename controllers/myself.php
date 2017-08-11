<?php
class Myself extends IController{
    
    private $openId ;
    private $type = 1;
    
    //初始化
    public function __construct(){
        $this->openId = ISession::get('wechat_openid');
        if(!empty($_GET['type'])){
            
            $this->type = $_GET['type'];
        }
        
    }
    
    
    function test(){
        echo "echo ok";
    }
    
    
    /*
     * 获取用户的 $type 级会员 及 购买数量 及 获取的提成
     * 
     * $type 查询的下线级别
     * 
     * 
     * */
    function fenxiao(){
        if(empty($this->openId)){
            echo "noid";
            return ;
        }
        
        $members = array();
       /* "member_name"=>"",
        "member_id"  =>"",
        "order_sum"  =>0,
        "commission" =>0*/
        
        
        //等级初始化
        $invite = "invite_one";
        if($this->type == 1){
            $invite = "invite_one";
        }else if($this->type == 2){
            $invite = "invite_two";
        }else{
            $invite = "invite_three";
        }
        
        //获取用户的member id
        $oauth_user = new IModel('oauth_user');
        $user = $oauth_user->getObj("oauth_user_id = '$this->openId' ");
        $mid = $user['user_id'];
        
        //$mid = 46;
        //查询所有 $type 级下线
        $member_user = new IModel('member');
        $xiaxian = $member_user->query(" $invite = '$mid' ");
        //print_r($xiaxian);
        foreach($xiaxian as $k => $v){
            //获取用户名
            $umodel = new IModel('user');
            $u = $umodel->getObj("id =".$v['user_id']);
            $name = $u['username'];
            
            //print_r($u);
            //echo "\\";
            //下线的id 且 查出下线的订单总数 和 $invite提成
            $x_id = $v['user_id'];
            $order = new IModel('order');
            $x_orders = $order->query(" user_id = '$x_id' and status=5 ");
            $orser_sum = count($x_orders);
            $or_commision = 0;
            //如果订单有数据，则遍历上线的提成
            if(!empty($x_orders)){
                foreach ($x_orders as $kk=>$item){
                    //print_r($item['sanjiticheng']." ");
                    $or_commision += $item['sanjiticheng'];
                }
            }
            
            //虚拟订单
            $order = new IModel('vr_order');
            $x_vr_orders = $order->query(" buyer_id = '$x_id' and status=5 ");
            $vr_orser_sum = count($x_vr_orders);
            $vr_or_commision = 0;
            
            //如果订单有数据，则遍历上线的提成
            if(!empty($x_vr_orders)){
                foreach ($x_vr_orders as $kk=>$item){
                    //print_r($item['sanjiticheng']." ");
                    $vr_or_commision += $item['sanjiticheng'];
                }
            }
            
            //提成总价
            $commision_sum = $or_commision + $vr_or_commision ;
            //订单总数
            $o_sum = $orser_sum + $vr_orser_sum ;
            //print_r($x_vr_orders);
            $members[] = array(
                "member_name" => $name,
                "member_id"   => $x_id,
                "order_sum"   => $o_sum,
                "commission"  => $commision_sum
            );
            
        }
        
        //print_r($members);
        //echo "<br/>";
        echo json_encode($members,JSON_UNESCAPED_UNICODE);
    }
}











