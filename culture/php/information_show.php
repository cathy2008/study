<?php
/*
 * 文章信息展示:列表显示+内容显示
 * 
 * 根据传入的moduleId，进行某模块的文章信息展示
 * $module=1:文章信息
 * $module=2:故事集
 * $module=3:视频集
 * $module=4:公司新闻
 * $module=5:行业动态
 * 
 * 根据传入的$type确定执行的操作
 * $type='list': 显示文章信息列表
 * $type='details': 显示某篇文章信息的具体内容
 * $type='leaveword': 用户评论
 * $type='zan': 用户点赞 
 */
header ( "content-type:text/json;charset=utf-8" ); 
require_once '../../common/php/dbaccess.php';
require_once '../../common/php/uploadFiles.php';
require_once '../../common/php/regexTool.class.php';
require_once '../../common/php/leaveword.class.php';
require_once '../../common/php/zan.class.php';
$db = new DB (); 
$lwd=new LWD('wx_leaveword');
$zan=new ZAN('wx_zan');
$regex=new regexTool(); 
 session_start();
$openid=$_SESSION['openid'];  
$type=$_REQUEST['type'];//list:列表显示；details:具体内容显示 
if ($type == 'list') {
	/**
	 * ************显示文章信息列表***************
	 */
	$page=$_REQUEST['page'];
	$moduleId=$_REQUEST['moduleId'];
	$list = array ();
	$num=10;//每页显示10条
	$start=($page-1)*$num;//本页显示的起始位置
	// 从wx_info中查询出文章信息的基本文章信息
	$sql_info_num = "select id from wx_info where moduleId='{$moduleId}' ";
	$res_info_num=$db->execsql($sql_info_num);
	$list['PageNum']=ceil(count($res_info_num)/$num);
	//查找是否有置顶项
	$sql_top_select="select id,title,thumb,abstract,content,date,is_leaveword,is_zan,importance from wx_info where moduleId='{$moduleId}' and importance=1";
	$res_top_select=$db->getrow($sql_top_select);
	if(!empty($res_top_select)){
	    //有置顶项
	    if($page==1){
	        //第一条
	        $res_info[0]=$res_top_select;
	        //剩余9条
	        $sql_remain = "select id,title,thumb,abstract,content,date,is_leaveword,is_zan,importance from wx_info  where moduleId='{$moduleId}' and importance=0 order by date desc limit ".$start.",".($num-1);
        	$res_remain = $db->execsql ( $sql_remain );
        	foreach($res_remain as $key_remain=>$val_remain){
        	    $res_info[$key_remain+1]=$val_remain;
        	}
    	}else{
    	    //剩余页
    	   $sql_info = "select id,title,thumb,abstract,content,date,importance from wx_info  where moduleId='{$moduleId}' and importance=0 order by date desc limit ".($start-1).",".$num;
           $res_info = $db->execsql ( $sql_info ); 
    	} 
	    
	}else{
	    //无置顶项
	    $sql_info = "select id,title,thumb,abstract,content,date,importance from wx_info  where moduleId='{$moduleId}' order by date desc limit ".$start.",".$num;
    	$res_info = $db->execsql ( $sql_info );
	}
	 
	foreach ( $res_info as $key_list => $val_list ) {
		
		// 根据文章信息ID在wx_leaveword表中查询出该文章信息的评论次数，在wx_zan表中查询出该文章信息的点赞次数
		$list [$key_list] ['num_leaveword'] = $lwd->lwdNum($val_list ['id']);//评论次数
		$list [$key_list] ['num_zan']= $zan->zanNum($val_list ['id']);//点赞次数
		$list [$key_list] ['id'] = $val_list ['id'];
		$list [$key_list] ['thumb'] = $val_list ['thumb'];
		$list [$key_list] ['title'] = $val_list ['title'];
		$list [$key_list] ['date'] = $val_list ['date'];
		$list [$key_list] ['importance'] = $val_list ['importance'];
		$list [$key_list] ['abstract'] = $val_list ['abstract'];
		$list [$key_list] ['content'] = $val_list ['content'];
	} 
	echo json_encode ( $list );
}  elseif ($type == 'details') {
    	/**
    	 * ************显示某篇文章信息的具体内容***************
    	 */
    $infoId=$_REQUEST['infoId'];//获取显示具体内容的文章信息ID 
    	
    if ($regex->isNumber($infoId)){
    	$sql_info_details = "select media,title,date,content,abstract,is_leaveword,is_zan from wx_info where id='{$infoId}'";
    	$res_info_details = $db->getrow ( $sql_info_details ); 
    	// 根据文章信息ID在wx_leaveword表中查询出该文章信息的评论次数和详情，在wx_zan表中查询出该文章信息的点赞次数
    	$page=$_REQUEST['page']; 
    	$num=10;//每页显示10条评论
    	$details['is_leaveword']=$lwd->isLwd($infoId);
    	$details['is_zan']=$zan->isZan($infoId);
    	$details['comment']=$lwd->showLwd($page, $num, $infoId);//分页显示具体的评论信息
    
    	$details  ['num_zan']= $zan->zanNum($infoId);//点赞次数 
    	//将图片的多个url分离
    	$detail_media=explode(';', $res_info_details['media']);
    	foreach ($detail_media as $val_detail_media){
    		$details ['media'][] = $val_detail_media;
    	} 
    	$details ['title'] = $res_info_details ['title'];
    	$details ['date'] = $res_info_details ['date'];
    	$details ['content'] = $res_info_details ['content']; 
    	$details ['abstract'] = $res_info_details ['abstract']; 
    	 echo json_encode ( $details );
    }else{
        echo 0;//参数错误
    }
    	
} elseif ($type == 'leaveword') {
    /**
     * *****************用户评论**********************
     */ 
    $infoId=$_REQUEST['infoId'];//获取显示具体内容的文章信息ID 
    $content=$_REQUEST['content']; 
    $leaveword=$lwd->lwdAdd($infoId, $content,$openid);
    echo json_encode($leaveword);
} elseif ($type == 'zan') {
    /**
     * *****************用户点赞**********************
     */
    $infoId=$_REQUEST['infoId'];//获取显示具体内容的文章信息ID 
    echo $zan->zanAdd($infoId,$openid);
}  elseif ($type == 'deleteLeaveword') {
	/**
	 * *****************删除某篇文章的评论**********************
	 */ 
	$id=$_REQUEST['id'];//评论ID 
	echo $lwd->delLwd($id);
}
