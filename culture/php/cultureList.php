<?php
header("content-type:text/json;charset=utf-8");
require_once '../../../common/php/dbaccess.php';
$db=new DB();
//自身连接查询
$menuId=$_GET['id'];//菜单ID
$type=$_GET['type'];
if($type=="showList"){
    $sql_second="select  id as moduleId, name, urlWechat from wx_articlelist_module  where parentId='{$menuId}' ";
    $res_second=$db->execsql($sql_second);
    // var_dump($res_second);	
    echo json_encode($res_second);
} 
?>