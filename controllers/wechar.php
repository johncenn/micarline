<?php
class Wechar extends IController{
    
    private $appId;
    private $appSecret;
    private static $accessTokenTime = 5000;
    //private $url;
    
    public function __construct() {
        
        $access = new IModel('plugin');
        $date = $access->getObj("class_name = 'wechat' ");
        $config_param = $date['config_param'];
        
        $config = json_decode($config_param,true);
        
        //print_r($config);
        
        $this->appId = $config['wechat_AppID'];
        $this->appSecret = $config['wechat_AppSecret'];
        
        
    }
    
    
    function getJSSDK(){
        
       // print_r($_GET);
       // die;
        $openId = ISession::get('wechat_openid');
        $oauth_user = new IModel('oauth_user');
        $user = $oauth_user->getObj("oauth_user_id = '$openId' ");
        $mid = $user['user_id'];
        
        
        $jsapiTicket = $this->getJsApiTicket();
        //$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $url = $_GET['url'];
        $url = str_replace('|','&',$url);
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        //加密
        $signature = sha1($string);
        
        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
            "mid"    => $mid
        );
        
       echo json_encode($signPackage,JSON_UNESCAPED_UNICODE);
       
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
    
    
    //获取access_tooken 接口
    public function getAccessToken2(){
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
                echo $accessToken;
                return ;
            }
            else
            {
                //如果为空
                $cacheObj->del('accessTokenTime');
                echo  $this->getAccessToken();
                return ;
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
                echo $accesstoken['access_token'];
                return ;
            }else{
                die("fail to get accessToken");
            }
    
            //print_r($accesstoken);
    
        }
    }
    
    //创建随机数
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    
    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $accessToken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
        $res = json_decode($this->httpRequest($url));
        $ticket = $res->ticket;
    
    
        return $ticket;
    }
    
    //发送请求
    private function httpRequest($url,$menu_data = null){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
        if(!empty($menu_data)){
            //post发送
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $menu_data);
        }
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
    
    
    //获取临时验证码ticket
    function getcodeTicket(){
        $user= ISession::get('wechatuser');
        if(empty($user)){
            echo "no id";
            return ;
        }
        
        //临时二维码
        $rcord = '{
            "expire_seconds": 1800,
            "action_name": "QR_SCENE",
            "action_info": {
                "scene": {
                    "scene_id": '.$user['id'].'
                }
            }
        }';
        
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$token";
        $result = $this->httpRequest($url,$rcord);
        $result = json_decode($result,true);
        echo $result['ticket'];
        //print_r($result);
        
    }
    
    
}










