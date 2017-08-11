<?php
class Tool{
    
    
    
    
    //curl发送请求
    function httpRequest($url,$menu_data = null){
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
    
    function getIp(){
        
        $address="";
    
        //获取客户端ip
        if (getenv("HTTP_CLIENT_IP"))
            $address = getenv("HTTP_CLIENT_IP");
        //获取代理ip
        else if(getenv("HTTP_X_FORWARDED_FOR"))
            $address = getenv("HTTP_X_FORWARDED_FOR");
        //获取本地电脑ip
        else if(getenv("REMOTE_ADDR"))
            $address = getenv("REMOTE_ADDR");
        else
            $address = "Unknow";
    
        return $address;
        
        
    }
    
    function test(){
        echo "hello";
    }
    
}























