<?php

class Chat extends IController{
    
    function add(){
        
       // print_r($_POST);
       // die;
        $u_id = $_POST['u_id'];
        $u_name = $_POST['u_name'];
        $t_id = $_POST['t_id'];
        $t_name = $_POST['t_name'];
        
        $model = new IModel('friends');
        
        $fquery = new IQuery('friends as f');
        $where = ('f.f_id = '.$u_id .' AND '.'f.t_id = '.$t_id).' OR '.('f.f_id = '.$t_id.' AND '.'f.t_id = '.$u_id);
        $fquery->where = $where;
        $result = $fquery->find();
        //print_r( $result );
        //存在数据
        if($result){
            $arr = array("res"=>1);
            echo json_encode($arr);
            return ;
        }
        
        $data = array(
            "f_id"      => $u_id,
            'f_name'    =>$u_name,
            't_id'      =>$t_id,
            't_name'    =>$t_name,
            'addtime'   =>date("Y-m-d H:i:s")
        );
        
        $model->setData($data);
        $addresult = $model->add();
       /*  echo json_encode($addresult);
        return ; */
        //print_r($addresult);
        
    }
    
    function talkAdd_db(){
        //print_r($_POST);
        $u_id = $_POST['u_id'];
        $u_name = $_POST['u_name'];
        $t_id = $_POST['t_id'];
        $t_name = $_POST['t_name'];
        $content = $_POST['content'];
        $type = $_POST['type'];
		
        $chat_log = new IModel('chat_log');
        $chat_msg = new IModel('chat_msg');
        //die($u_id." ".$u_name." ".$t_id." ".$t_name." ".$content);
        $data = array(
            "f_id"      => $u_id,
            'f_name'    =>$u_name,
            'f_ip'      =>null,
            't_id'      =>$t_id,
            't_name'    =>$t_name,
            't_msg'       =>$content,
			'type'		=>$type,
            'add_time'   =>date("Y-m-d H:i:s")
        );
        
        $data_msg = array(
            "f_id"      => $u_id,
            'f_name'    =>$u_name,
            'f_ip'      =>null,
            't_id'      =>$t_id,
            't_name'    =>$t_name,
            't_msg'       =>$content,
			'type'		=>$type,
            'add_time'   =>date("Y-m-d H:i:s")
        );
        
        $chat_log->setData($data);
        $chat_msg->setData($data);
        
        $log = $chat_log->add();
        $msg = $chat_msg->add();
        if(!empty($log) && !empty($msg)){
            echo $msg;
        }else{
            if($log || $msg){
                echo "oneSuccess";
            }else{
                echo "dies";
            }
        }
    }
    
    
    function getTalkLog(){
        //print_r($_POST);
        $f_id = $_POST['t_id'];
        $t_id = $_POST['f_id'];
        
        
        $chat_msg = new IQuery('chat_msg');
        $chat_msg->fields = "*";
        $chat_msg->where = "(f_id = '$f_id' and t_id = '$t_id') or (f_id = '$t_id' and t_id = '$f_id' ) ORDER BY add_time ASC";
        $chat_msg->limit = 15;
        $res = $chat_msg->find();
       // print_r($res);die;
        //$chat_msg->where = " ((f_id = '$f_id' and t_id = '$t_id') or (f_id = '$t_id' and t_id = '$f_id' ))  ORDER BY add_time ASC  ";
       // $chat_msg->limit = 5;
        //$res = $chat_msg->find();
        
        //print_r($res);
        $arr = array();
        if(!empty($res)){
            foreach($res as $k=>$v){
                $arr[] = $v['m_id'];
            }
            
            $mid = implode(",",$arr);
            $data = array(
                "r_state"=>1
            );
            $c = new IModel('chat_msg');
            $c->setData($data);
            $c->update('m_id in ('.$mid.') and t_id='.$t_id);
        }
        
        
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
    }
    
    //改变状态
    function change(){
        $id = $_GET['mid'];
        
        $chat_msg = new IModel('chat_msg');
        
        $data = array(
            "r_state"=>1
        );
        $chat_msg->setData($data);
        echo $chat_msg->update('m_id ='.$id);
    }
    
    //读取未读信息
    function weidu(){
        //SELECT COUNT(*) AS num, f_id,f_name,r_state FROM micarshop_chat_msg WHERE r_state = 2 AND t_id = 6 GROUP BY(f_id) ORDER BY num DESC 
        $u_id = $_GET['f_id'];
        if(empty($u_id)){
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
    
    function photo_upload(){
       // print_r($_FILES);
        if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != ''){
            
            $photoObj = new PhotoUpload();
            $photo    = $photoObj->run_chat();
            
            if(isset($photo['file']['img']) && $photo['file']['img'])
            {
                echo $photo['file']['img'];
                
            }else{
                echo "fai";
            }
        }
    }
    
    function test(){
        echo "ok_selelre";
    }
}














