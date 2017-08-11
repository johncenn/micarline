<?php
namespace Home\Controller;
use Think\Controller;
require 'WeCharTool.class.php';


class IndexController extends Controller {
    
    public function index(){
         
        $user = session("u_user");
        //print_r($user);
        if(!isset($user) || empty($user)){
            //去授权
            $this->sendWechar();
            return ;
        }
        
        $this->display("index");
    }
    
    private function sendWechar(){
		
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".APPID."&redirect_uri=".RED_URL."&response_type=code&scope=snsapi_userinfo";
		
        header("Location:$url");
    }
    
    
    public function deelCode(){
		
		//die("deelcode");
		
		
        $code = $_GET['code'];
      
        //通过code换取用户的access_tooken 和 openid
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".APPID."&secret=".APPSECRET."&code=$code&grant_type=authorization_code";
        $tool = new \Tool();
        $json_result = $tool->httpRequest($url);
        $result = json_decode($json_result,true);
        
       
        if(!empty($result['errcode']) || isset($result['errcode'])){
            $this->sendWechar();
            return ;
        }
    
        
        $access_tooken = $result['access_token'];
        $openid = $result['openid'];
    
        //根据access_tooken 和 openid 获取用户信息
        $getUserUrl = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_tooken&openid=$openid";
        $json_user = $tool->httpRequest($getUserUrl);
        $user = json_decode($json_user,true);
        
        $selectres = D("micarshop_usergrade")->where("openId='$openid'")->select();
        
        if(empty($selectres)){
            //插入抽奖表
            $arr = array(
                "openId"=>$openid,
                "name"=>$user['nickname'],
                "time"=>date("Y-m-d H:i:s")
            );
            D("micarshop_usergrade")->add($arr);
            
            
           /* //插入micar官网user表
            $userarr = array(
                "username"=>  $user['nickname'],
                "password"  =>md5("123456"),
                "head_ico"  =>$user['headimgurl']
            );
            $userid = D("micarshop_user")->add($userarr);
            
            
            //插入micar官网member表
            $memberarr = array(
                "true_name"=>  $user['nickname'],
                "sex"   =>$user['sex'],
            );
            D("micarshop_member")->add($memberarr);
            
            //插入micar官网oauth_user表
            $oauth_user_arr = array(
                "oauth_user_id"=>$openid,
                "oauth_id"  =>5,
                "user_id"   =>$userid,
                "datetime"  =>date("Y-m-d H:i:s")
            );
            D("micarshop_oauth_user")->add($oauth_user_arr);*/
            $user['grade_num'] = 1;
            session("u_user",$user);
            $this->index();
            
            return ;
            
        }
        $user['grade_num'] = $selectres[0]['grade_num'];
        session("u_user",$user);
        $this->index();
    }
    
    public function getNum(){
          $user = session("u_user");
        //print_r($user);
        if(!isset($user) || empty($user)){
            //去授权
            $this->sendWechar();
            return ;
        } 
        
        $grades = D("micarshop_grade")->select();
        $grades = $grades[0];
       /*  echo "<pre>";
        print_r($grades);
        echo "</pre>"; */
        
        $rn = rand(100, 999);
        
        $res = $rn % 9 ;
        
        if($res == 2){
            
            //已被人抽到了
            if($grades['gradeone_nums'] <= 0){
                $this->getNum();
                return ;
            }
            
            $users = D("micarshop_usergrade")->select();
            $user_count = count($users);
            
            if($user_count >=15){
                echo $res;
                return ;
            }else{
                $this->getNum();
                return ;
            }
            
        }else if($res == 4){
            
            //已被人抽到了
            if($grades['gradethree_nums'] <= 0){
                $this->getNum();
                return ;
            }
            
            $users = D("micarshop_usergrade")->select();
            $user_count = count($users);
            
            if($user_count >=5){
                echo $res;
                return ;
            }else{
                $this->getNum();
                return ;
            }
            
        }else if($res == 5){
            
            //已被人抽到了
            if($grades['gradete_nums'] <= 0){
                $this->getNum();
                return ;
            }
            
            $users = D("micarshop_usergrade")->select();
            $user_count = count($users);
            
            if($user_count >=20){
                echo $res;
                return ;
            }else{
                $this->getNum();
                return ;
            }
            
        }else if($res == 8){
            
            //已被人抽到了
            if($grades['gradetwo_nums'] <= 0){
                $this->getNum();
                return ;
            }
            
            $users = D("micarshop_usergrade")->select();
            $user_count = count($users);
            
            if($user_count >=10){
                echo $res;
                return ;
            }else{
                $this->getNum();
                return ;
            }
            
        }
        
        if($res == 0){
             $this->getNum();
             return ;
        }
        
        
        
        //
        $update_grade_arr = array(
        
        );
        
        //修改奖品表的数据
        if($res == 1 || $res == 3 || $res == 7){
            
           $user_grate_arr = array(
                
               "time"=>date("Y-m-d H:i:s"),
               "grade_type"=>$res,
               "grade_num"=>0
           );
           
           $openid = $user['openid'];
           D("micarshop_usergrade")->where("openId='$openid'")->setField($user_grate_arr);
           
        }else if($res == 2){
            
            $user_grate_arr = array(
            
                "time"=>date("Y-m-d H:i:s"),
                "grade_type"=>$res,
                "grade_num"=>0
            );
             
            $openid = $user['openid'];
            $is = D("micarshop_usergrade")->where("openId='$openid'")->setField($user_grate_arr);
            
            if(is){
                
                $d_sql = "update micarshop_grade set gradeone_nums = gradeone_nums -1";
                D("micarshop_grade")->execute($d_sql);
            }
            
        }else if($res == 4){
            $user_grate_arr = array(
            
                "time"=>date("Y-m-d H:i:s"),
                "grade_type"=>$res,
                "grade_num"=>0
            );
             
            $openid = $user['openid'];
            $is = D("micarshop_usergrade")->where("openId='$openid'")->setField($user_grate_arr);
            
            if(is){
            
                $d_sql = "update micarshop_grade set gradethree_nums = gradethree_nums -1";
                D("micarshop_grade")->execute($d_sql);
            }
        }else if($res == 5){
            $user_grate_arr = array(
            
                "time"=>date("Y-m-d H:i:s"),
                "grade_type"=>$res,
                "grade_num"=>0
            );
             
            $openid = $user['openid'];
            $is = D("micarshop_usergrade")->where("openId='$openid'")->setField($user_grate_arr);
            
            if(is){
            
                $d_sql = "update micarshop_grade set gradete_nums = gradete_nums -1";
                D("micarshop_grade")->execute($d_sql);
            }
        }else if($res == 6){
            $user_grate_arr = array(
            
                "time"=>date("Y-m-d H:i:s"),
                "grade_type"=>$res,
                "grade_num"=>0
            );
             
            $openid = $user['openid'];
            $is = D("micarshop_usergrade")->where("openId='$openid'")->setField($user_grate_arr);
            
            if(is){
            
                $d_sql = "update micarshop_grade set gradelucky_nums = gradelucky_nums -1";
                D("micarshop_grade")->execute($d_sql);
            }
        }else if($res == 8){
            
            $user_grate_arr = array(
            
                "time"=>date("Y-m-d H:i:s"),
                "grade_type"=>$res,
                "grade_num"=>0
            );
             
            $openid = $user['openid'];
            $is = D("micarshop_usergrade")->where("openId='$openid'")->setField($user_grate_arr);
            
            if(is){
            
                $d_sql = "update micarshop_grade set gradetwo_nums = gradetwo_nums -1";
                D("micarshop_grade")->execute($d_sql);
            }
        }
        
        
        echo $res;    
            
    }
    
    public function show(){
        
        $user = session("u_user");
        //print_r($user);
        if(!isset($user) || empty($user)){
            //去授权
            $this->sendWechar();
            return ;
        }
        
        
        $openid = $user['openid'];
        
        $result = D("micarshop_usergrade")->where("openId='$openid'")->select();
        
        $grate = $result[0]['grade_type'];
        if($grate == 1 || $grate == 3 || $grate ==7){
            $res = array(
              "type"=>"很遗憾",
                "result"=>"没有中奖"
            );
            
            session("result",$res);
            $this->display();
            return ;
        }else if($grate == 6){
            $res = array(
                "type"=>"恭喜你，获得了",
                "result"=>"幸运奖"
            );
            session("result",$res);
            $this->display();
            return  ;
        }else if($grate == 2){
            $res = array(
                "type"=>"恭喜你，获得了",
                "result"=>"一等奖"
            );
            session("result",$res);
            $this->display();
            return ;
        }else if($grate == 4){
            $res = array(
                "type"=>"恭喜你，获得了",
                "result"=>"三等奖"
            );
            session("result",$res);
            $this->display();
            return ;
        }else if($grate == 5){
            $res = array(
                "type"=>"很遗憾",
                //"result"=>"特等奖"
				"result"=>"很遗憾"
            );
            session("result",$res);
            $this->display();
            return ;
        }else if($grate == 8){
            $res = array(
                "type"=>"恭喜你，获得了",
                "result"=>"二等奖"
            );
            session("result",$res);
            $this->display();
            return ;
        }
        
    }
    
}


