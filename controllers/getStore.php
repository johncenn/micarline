<?php
class GetStore extends IController{
    
    function get(){
        $store = new IModel("seller");
        $area = new area();
        $res = $store->query("gps!=''" );
        $temp_arr = array();
        
        
        foreach ($res as $val){
            
            $address = $area->name($val['province'],$val['city'],$val['area']);
            
            array_push($val,$address);
            array_push($temp_arr,$val);
            
           
            
        }
        
        $j_store = json_encode($temp_arr);
        print_r($j_store);
    }
    
    function save_temp_map(){
        echo("ok");
    }
}