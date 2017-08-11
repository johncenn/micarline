<?php
class JSSDK {
  private $appId;
  private $appSecret;
  private $url;
  
  public function __construct($appId, $appSecret,$url) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
    $this->url = $url;
  }
    
  //
  public function getSignPackage() {
  
   $jsapiTicket = $this->getJsApiTicket();
   //$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url = $this->url;
    file_put_contents("aaaa.txt", $url);
    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
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

  
  //获取access_tooken
  /*private function getAccessToken(){

     //基础access_tooken
   $access_tooken_json = file_get_contents("http://www.micarline.net/access_token.json");
   $access_arr = json_decode($access_tooken_json,true);
   $access_tooken = $access_arr['access_token'];
	file_put_contents("./accessitoken.txt",$access_tooken);
    return $access_tooken;
}*/

  
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
}

// $jssdk = new JSSDK($appId, $appSecret);
// $jssdk->getSignPackage();
