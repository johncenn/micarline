<?php
class respon_wechar extends IController{
    
    private $appId;
    private $appSecret;
    private static $accessTokenTime = 5000;

    public function __construct() {
         $access = new IModel('plugin');
        $date = $access->getObj("class_name = 'wechat' ");
        $config_param = $date['config_param'];
        
        $config = json_decode($config_param,true);
        
        //print_r($config);
        
        $this->appId = $config['wechat_AppID'];
        $this->appSecret = $config['wechat_AppSecret'];
        
        
    }

    public function index() {
        
        
        $yz = $_GET['echostr'];
        if(isset($yz)){
            echo $yz;
            exit;
        }
        
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $result = "test";
        $postObj = '';
        
        file_put_contents("./we.txt", var_export( $postStr,true));
        
        if(!empty($postStr)){
            $postObj = simplexml_load_string($postStr);
            $RX_TYPE = trim($postObj->MsgType);
            
            switch ($RX_TYPE){
                case "event":
                    $result = $this->receiveEvent($postObj);
                break;
                case "text":
                    $result = $this->receiveText($postObj);
                break;
                case "image":
                    $result = $this->receiveImage($postObj);
                    break;
                case "location":
                    //地图处理
                    $result = $this->testLocation();
                    die;
                    break;
            }
        }
        echo $result;
    }
    
    /*
     * 响应地图
     * */
    private function receiveMap($object){
       
       //获取发送用户信息
       $tooken = $this->getAccessToken();
       $openId = $object->FromUserName;
       $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$tooken}&openid={$openId}&lang=zh_CN ";
       $jsondata = $this->httpRequest($url);
       $jasonName = json_decode($jsondata,true);
       $UserName = $jasonName['nickname'];
       
       $content = "发送的用户是：".$UserName."\r\n";
       $local = "坐标是经纬度：".$object->Location_X.",".$object->Location_Y."\r\n";
       $address = "详细地址是：".$object->Label."\r\n";
       $cont = $content.$local.$address;
        $this->auto_reply('o7WLlwUyUvqIkeQMz4uq6bcOBVfc',$cont);
      // $this->auto_mapreply($object);
       die();
       
       
    }
    
    
    //(sys)位置推送到客服
    private function transmitMapText($object , $content){
        
        $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";
        $result = sprintf(
            $textTpl,
            $object->FromUserName,
            $object->ToUserName,
            time(),
            $content
        );
        
        return $result;
    }
    
    /*
     * 响应Image
     */
    private function receiveImage($object){
        $contentStr = "欢迎参加\"金秋有爱，幸福瞬间\"随手拍相片评选活动！/:share /:,@-D祝你好运！/:rose";
        $resultStr = $this->transmitText($object, $contentStr);
        return $resultStr;
    }
    
    /*
     * 响应Text
     */
    private function receiveText($object){
		
		
		
        $keyword = trim($object->Content);
		
		
		
        $contentStr = "米喀智慧汽车联盟服务平台欢迎您！
如需帮助请留言，米喀客服将竭诚为您服务！
服务热线： 4008-0757-80
        0757-82305590";
        switch ($keyword){
            case "佛建":
                $contentStr = "佛建汽修张槎店
http://www.micarline.net/wap/tmpl/store.html?store_id=27
固定电话：0757-82202680
地址：佛山市张槎二路大埔顶（张槎高速出口转右直行古生公寓村公交站旁）
24小时服务电话：13380220722 \n
佛建汽修（季华营业部）
佛建，汽修，三类维修厂，保险定点，品牌专修，紧急救援，保养，喷漆，洗车，禅城区，季华，禅城华远西路，免费拖车";
                break;
            case "佛建汽修张槎店":
                $contentStr = "http://www.micarline.net/wap/tmpl/store.html?store_id=27
固定电话：0757-82202680
地址：佛山市张槎二路大埔顶（张槎高速出口转右直行古生公寓村公交站旁）
24小时服务电话：13380220722";
                break;
            case "保险":
                $contentStr = "车险，车险报价，中保车险，平安车险，太平洋车险，中国人寿车险，紫金车险，家庭自用车，货车，交强险，商业险，车船税";
                break;
            case "众享汇":
            case "众享汇汽车服务有限公司":
                $contentStr = "众享汇汽车服务有限公司
http://www.micarline.net/wap/tmpl/store.html?store_id=9 
服务热线：0757-82661689
地址：佛山市禅城区佛山大道中国际车城南区9座1楼9-11号
            ";
                break;
            case "香车有约":
            case "香车有约汽车服务中心":
                $contentStr = "香车有约汽车服务中心
http://www.micarline.net/wap/tmpl/store.html?store_id=6 
服务热线：0757-88020238
地址：佛山市禅城区江湾一路弼唐东二街12号之北1-2号铺
            ";
                break;
            case "小拇指":
            case "小拇指汽车微修":
                $contentStr = "小拇指汽车微修（陈村店）
http://www.micarline.net/wap/tmpl/store.html?store_id=46 
服务热线：0757-23812768
地址：佛山市顺德区陈村镇赤花工业区工业三路1号
            ";
                break;
            case "景胜":
            case "景胜汽车维修中心":
                $contentStr = "景胜汽车维修中心
http://www.micarline.net/wap/tmpl/store.html?store_id=26 
服务热线：0757-82810393
地址：佛山市快捷汽配城A区（郊边会稽工业区）三排一号
            ";
                break;
            case "V10012_GOOD":
                $contentStr = "米喀智慧汽车联盟服务平台欢迎您！
如需帮助请留言或致电服务热线，米喀客服将竭诚为您服务！
服务热线：4008-0757-80
        0757-82305590
            ";
                break;
            case "V1001_GOOD":
                $contentStr = "亲，您只需在公众号输入栏提供以下信息，即可轻松进行详细的保险询价哦！/::)
1，行驶证的正副证图片
2，身份证正反面的图片
3，保险项目或去年商业险保单
4，车辆登记本的图片
            ";
                break;
            case "V10011_GOOD":
                $contentStr = "暂未开放，敬请期待！
            ";
                    break;
                    case "测试":
                        $contentStr = $this->getOpenId($object);
                        break;
                    
             //接入客服响应
             default:
                
            //时间（只要小时）
            $time = time();
            $date = date('G',$time);
//             $date = 11;
            //工作时间
            if($date >=9 && $date<18){
                //自动回复
                $contentStr = "客服接入中，请稍后。如长时间未能接入，请拨打服务热线： 4008-0757-80
        0757-82305590";
                $user = $object->FromUserName;
                $this->auto_reply($user,$contentStr);
                //接入客服
                return $this->conn_customer($object);
              }  
            
            
            $contentStr = "客服在线时间是9点到18点，请选择自助服务\r\n".'米喀智慧汽车联盟服务平台欢迎您！
如需帮助请留言，米喀客服将竭诚为您服务！
紧急救缓服务热线：18316963188（17:00~次日09:00）';
            
        }
		
		
        $resultStr = $this->transmitText($object, $contentStr);
        return $resultStr;
    }
    
    /*
     * 响应Event
     */
    private function receiveEvent($object){
        $contentStr = "";
        switch ($object->Event){
            case "subscribe":
                $contentStr = "欢迎关注";
                if(isset($object->EventKey)){
                    $userId = substr($object->EventKey,8);
                    $guanzhu_openId = $object->FromUserName;
                    $contentStr = ' 感谢关注，米喀智慧汽车联盟服务平台欢迎您！
                                                            请进入“商城”里的“我的商城”找到“用户设置”进行登录密码设置！
                                                            如需帮助请留言，米喀客服将竭诚为您服务！
                    ';
                                                           
                    
                    $this->gz_reg($userId,$object);
                }
                break;
            case "location_select":
		      $contentStr = "in4";
               $this->selectMap($object);
                die;
                break;
            case "SCAN":
                $contentStr = "米喀智慧汽车联盟服务平台欢迎您！
如需帮助请留言，米喀客服将竭诚为您服务！
服务热线： 4008-0757-80
        0757-82305590";
                break;
           
            case "CLICK":
                $contentStr = $this->receiveCLICK($object->EventKey);
                if($contentStr == 'kefu'){
                   //自动回复
                    $contentStr = "客服接入中，请稍后。如长时间未能接入，请拨打服务热线： 4008-0757-80
                    0757-82305590";
                    $user = $object->FromUserName;
                    $this->auto_reply($user,$contentStr);
                    return $this->conn_customer($object);
                    //die;
                    //$contentStr = $object->FromUserName;
                }
                break;
            default:
                break;
        }
        $resultStr = $this->transmitText($object, $contentStr);
        
        return $resultStr;
    }
    
    /*
     * 响应CLICK
     */
    private function receiveCLICK($EventKey){
        $contentStr = "服务暂未开放，敬请期待！";
        switch($EventKey){
            case "V10013_GOOD":
                $contentStr = "亲，您只需在公众号输入栏提供以下信息，即可轻松进行详细的保险询价哦！/::)
1，行驶证的正副证图片
2，身份证正反面的图片
3，保险项目或去年商业险保单
4，车辆登记本的图片
            ";
                break;
            case "V10012_GOOD":
                $contentStr = "米喀智慧汽车联盟服务平台欢迎您！
如需帮助请留言或致电服务热线，米喀客服将竭诚为您服务！
服务热线：4008-0757-80
        0757-82305590
            ";
                break;
            case "V1001_GOOD":
                $contentStr = "亲，您只需在公众号输入栏提供以下信息，即可轻松查询违章哦！
如需代办违章报价请回复“代办违章报价”。
1，行驶证的正副证图片
2，留下您的电话号码。
服务热线：0757-82305590
客服电话：13336413830
客服服务时间： （工作日9：00~17：00）
            ";
                break;
            
            case "KE":
                $contentStr = 'kefu';
                break;
            case "V10014_GOOD":
                $contentStr = "您好！米喀客服将竭诚为您服务！道路救援求助可拨打服务热线：4008-0757-80、0757-82305590（工作日9：00~17：00） 或者节假日及业余时间服务热线：18316963188（工作日17:00~次日09:00和节假日全天）。
            		";
                break;
        }
        return $contentStr;
    }
    
    
    
    private function getAccessToken1(){
        //com
        $appid = "wx7b97c4669afca34b";
        $appsecret = "548139dd48fc754fd300b792bdd2f2c0";
        
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $data = curl_exec($ch);
        curl_close($ch);
        
        $access_data = json_decode($data,true);
        
        return $access_data["access_token"];
    }
    
    
    
//获取access_tooken
    private function getAccessToken(){
        //从cookie中取
        $cacheObj = new ICache();
        $accessTokenTime = $cacheObj->get('accessTokenTime');
         
        //判断是否存在且可以使用
        if($accessTokenTime && time() - $accessTokenTime < self::$accessTokenTime)
        {
            $accessToken = $cacheObj->get('accessToken');
            //echo "有";
            if($accessToken)
            {
                //echo "可以用<br/>";
                return $accessToken;
            }
            else
            {
                //如果为空
                $cacheObj->del('accessTokenTime');
				return $this->getAccessToken();
            }
        }else{
            //没有accessToken，就获取
            $getAccessTokenUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appId.'&secret='.$this->appSecret;
            $accesstoken_json = $this->httpRequest($getAccessTokenUrl);
            $accesstoken = json_decode($accesstoken_json,true);
            
            if($accesstoken && isset($accesstoken['access_token']) && isset($accesstoken['expires_in'])){
                //保存到缓存中
                $cacheObj->set('accessTokenTime',time());
                $cacheObj->set('accessToken',$accesstoken['access_token']);
                return $accesstoken['access_token'];
            }else{
                die("fail to get accessToken");
            }
            
            //print_r($accesstoken);
        
        }
    }
    
    //测试获取openid
    public function getOpenId($object){
        $openid = $object->FromUserName;
        return $openid;
    }
    
    public function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }
    
    /**
     * 文本消息
     */
    private function transmitText($object , $content){
        $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";
        $result = sprintf(
            $textTpl,
            $object->FromUserName,
            $object->ToUserName,
            time(),
            $content
        );
        return $result;
    }
    
    //（sys）向微信发送请求，当data不为空则发送post请求，为空则发get请求
    public function httpRequest($url,$data = null){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if(!empty($data)){
            //post发送
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
    
    //(sys) 接入客服前自动回复用户
    private function auto_reply($user,$contentStr){
        $tooken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$tooken}";
        
        $data = '{
                "touser":"'.$user.'",
                "msgtype":"text",
                "text":{
                    "content":"'.$contentStr.'"
                }
            }';
        $this->httpRequest($url,$data);
    }
    
    private function testLocation(){
        file_put_contents("./weixin6.txt", "用户位置   ");
    }
    
    //(sys)自动回复文本【解决进入客服状态无法接受地图】
    private function auto_mapreply($object,$Customer_openId){
        $tooken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$tooken}";
        $Location_X = $object->SendLocationInfo->Location_X;
        $Location_Y = $object->SendLocationInfo->Location_Y;
        $coord = $Location_X.",".$Location_Y;
        $address = $object->SendLocationInfo->Label;
       // file_put_contents("./weixin7.txt", "用户位置   ".$object->SendLocationInfo->Location_X." ".$object->FromUserName);
        
        /*
         * "url":"http://apis.map.qq.com/tools/poimarker?type=0&marker=coord:'.$coord.'
                                ;title:车主地点;addr:'.$address.'&key=GWEBZ-TN635-YIEI5-QV4LJ-RXJ2H-7KFXC&referer=testMap",
         * 
         * */
        file_put_contents("./weixin7.txt", $Customer_openId);
        $data = '{
            "touser":"'.$Customer_openId.'",
            "msgtype":"news",
            "news":{
                "articles": [
                     {
                         "title":"用户位置",
                         "description":"用户所在的位置，点开查看地图",
                         "url":"http://apis.map.qq.com/tools/poimarker?type=0&marker=coord:'.$coord.';
                title:车主地点;addr:'.$address.'&key=GWEBZ-TN635-YIEI5-QV4LJ-RXJ2H-7KFXC&referer=testMap",
                         "picurl":"http://www.micarline.net/shop/map.jpg"
                     }
                 ]
            }
        }';
        $this->httpRequest($url,$data);
    }
    
    //(sys) 接入客服
    private function conn_customer($object){
        $xmlTpl = "<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
    
			<MsgType><![CDATA[transfer_customer_service]]></MsgType>
			</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }
    
    //响应用户选择地图
    private function selectMap($object){
        //获取用户昵称
        $jsondata = $this->getUser($object);
        $jasonName = json_decode($jsondata,true);
        $UserName = $jasonName['nickname'];
       
        //获取会话状态
        $states = $this->account($object);
        $Customer_nick = $states['kf_account'];//客服昵称
        //默认的属性
        $Customer_wx = "mklsw2015";
        $Customer_openId = "o7WLlwVL8_Ge15rSVGbI9Vm-nmQs";
        
        //没有进入客服处理 
        if(empty($Customer_nick)){
           
            //提示准备接入客服
            $contentStr = "请输入你的问题，等待客服接入";
            $user = $object->FromUserName;
            $this->auto_reply($user, $contentStr);
            die;
        }
        
        /*
         * 查找客服对应的wx 及 openid
         */
        $customers = $this->get_Customer_state();//获得所有在线客服
        
        //查找客服wx
        foreach ($customers as $key =>$val){
           
            if($Customer_nick == $val['kf_account']){
                $Customer_wx = $val['kf_wx'];
                break;
            }
        }
        
        //获取对应的openid
        $openids = $this->getOpenIdByWx();
        foreach ($openids as $key=>$val){
            if($Customer_wx == $key){
                $Customer_openId = $val;
                break;
            }
        }
        
        //回复地图位置图文
        $this->auto_mapreply($object,$Customer_openId);
        
    }
    
    //(sys)获取用户的会话状态
    private function account($object){
        $token =  $this->getAccessToken();
       // $openId = 'o7WLlwUyUvqIkeQMz4uq6bcOBVfc';
        $openId = $object->FromUserName;;
        $url = "https://api.weixin.qq.com/customservice/kfsession/getsession?access_token={$token}&openid={$openId}";
    
        $res = $this->httpRequest($url);
        $res = json_decode($res,true);
        return $res;
    }
    
    //(sys)获取所有客服信息
    private function get_Customer_state(){
        $tooken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token={$tooken}";  
        $json_data = $this->httpRequest($url);
        $data = json_decode($json_data,true);
        $customers = $data['kf_list'];
        return $customers;
        
    }
    
    //(sys) 获取用户信息
    private function getUser($object){
        //获取发送用户信息
        $tooken = $this->getAccessToken();
        $openId = $object->FromUserName;
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$tooken}&openid={$openId}&lang=zh_CN ";
        $jsondata = $this->httpRequest($url);
        
        return $jsondata;
    }
    
    //（sys）手动配置对应的opneid对应的微信号
    private function getOpenIdByWx(){
        //kim openid ：o7WLlwej7wVC6jCrBNctzZL7o9oU
        $data = array(
            "mklsw2015"=>"o7WLlwVL8_Ge15rSVGbI9Vm-nmQs",
            "wangdonghui_4788"=>"o7WLlwa7MPsdLsLzo2_kM-x-FH7g",
            "Chinaidhaha"=>"o7WLlwUyUvqIkeQMz4uq6bcOBVfc",
            "wxid_e83vob5qnp6222"=>"o7WLlwY-e7sv3ooxA6pj_eAsrflI"
        );
        
        return $data;
    }
    
    
    //关注公众号，自动成为公众号的会员
    private function gz_reg($yaoqing,$object){
        
        //检查用户信息是否存在
        $f_openid = $object->FromUserName;
        
        
        $tempDB   = new IModel('oauth_user as ou,user as u');
        $oauthRow = $tempDB->getObj("ou.oauth_user_id = '".$f_openid."' and ou.oauth_id = 5 and ou.user_id = u.id");
        
        //用户不存在
        if(!$oauthRow){
            //注册成为网站会员 且 成为邀请者的下线
            //通过openid去获取用户信息
            $json_user = $this->getUser($object);
            $user_msg = json_decode($json_user,true);
            $username = $user_msg['nickname'];
            $ico = $user_msg['headimgurl'];
            $sex = $user_msg['sex'];
            $unId = $object->FromUserName;
            
            $userDB    = new IModel('user');
            $userCount = $userDB->getObj("username = '{$username}' ",'count(*) as num');
            
            //没有重复的用户名
            if($userCount['num'] == 0)
            {
            
            }
            else
            {
                //随即分配一个用户名
                $username = $username.rand(1000,9999);
            }
            
            
            //插入user表
            $userDB->setData(array(
                'username' => $username,
                'password' => md5(time()),
                'head_ico' => $ico,
            ));
            $user_id = $userDB->add();
            
            //插入member表
            $memberDB = new IModel('member');
            
            //查出邀请者的信息
            $yaoMessages = $memberDB->getObj('user_id = "'.$yaoqing.'"');
            $yaoInvite_one = $yaoMessages['invite_one'];
            $yaoInvite_two = $yaoMessages['invite_two'];
            
            //用户的一级邀请会员
            $userInvite_one = $yaoqing;
            $userInvite_two = $yaoInvite_one;
            $userInvite_three = $yaoInvite_two;
            
            $memberDB->setData(array(
                'user_id' => $user_id,
                'time'    => ITime::getDateTime(),
                'sex'     => $sex,
                'invite_one' => $userInvite_one,
                'invite_two' => $userInvite_two,
                'invite_three' => $userInvite_three,
            ));
            
            $memberDB->add();
            
            //插入oauth_user关系表
            $oauthUserDB = new IModel('oauth_user');
            $oauthUserDB->del("oauth_user_id = '".$unId."'");
            $oauthUserData = array(
                'oauth_user_id' => $unId,
                'oauth_id'      => 5,
                'user_id'       => $user_id,
                'datetime'      => ITime::getDateTime(),
            );
            $oauthUserDB->setData($oauthUserData);
            $oauthUserDB->add();
            
        }
        
        
        //$r = var_export($oauthRow,true);
        //file_put_contents("./respon.txt", $r);
    }
    
    
    
    
    
    
    
    
    
}