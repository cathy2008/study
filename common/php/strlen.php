<?php 
//统计字符串长度，适用中文，字母，数字混编
function get_strlength($str) {
    //强字符串统一转换为utf-8格式
    $encode = mb_detect_encoding( $str, array('ASCII','UTF-8','GB2312','GBK'));
    if (!$encode =='UTF-8'){
        $str = iconv('UTF-8',$encode,$str);
    }
    //初始化字符串长度    
    $count = 0;
    
    //循环统计
    for($i = 0; $i < strlen($str); $i++){
        //获取字符串首字母对应的ASCII值
        $value = ord($str[$i]);
        
        if($value > 127) {
            $count++;
            if($value >= 192 && $value <= 223){
                $i++;
            }elseif($value >= 224 && $value <= 239){
                $i = $i + 2;
            }elseif($value >= 240 && $value <= 247){
                $i = $i + 3;
            }else{
                return "字符串异常";
            } 
        }
        $count++;
    }
    return $count;
} 
?>