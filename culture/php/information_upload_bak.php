<?php
/*
 * 实现信息上传
 */
header ( 'content-type:text/json;charset=utf-8' );
require_once '../../../common/php/dbaccess.php';
require_once '../../../common/php/uploadFiles.php';
session_start ();
$db = new DB (); // 实例化
$openid=$_SESSION['openid'];
/**
 * **判断标题内容是否为空****
 */
$title= $_POST ['title'];
$content= $_POST ['content'];
$moduleId= $_POST ['moduleId'];
$subtype=$_GET['subtype'];
// echo $subtype;die;
// $mediatype=$_GET['mediatype'];//0：图片；1：视频；2：缩略图
	
if($subtype=='upvideo'){
		/**
		 * ***视频上传功能***
		 */
// 	echo "82";die;
		$dest=uploadmulti('video',1);
		$_SESSION['upvideo']=$dest[0];
// 		var_dump($dest);
		echo $dest[0];
	}
else if (  empty( $title ) || empty ( $content)) {
// 	echo "<script>alert('标题和内容不能为空');window.location.href='upload.html';</script>";
	$error = 2; // 标题和内容不能为空
} else {
	/**
	 * ***获取上传图片的url***
	 */
	/* $dest = array ();
	$dest = uploadmulti ( 0 );
	// var_dump($dest);
	$num = count ( $dest );
	$dest_db = $dest [0];
	for($i = 1; $i < $num; $i ++) {
		$dest_db .= ';' . $dest [$i];
	} */
	// echo $dest_db;
if ($subtype=='thumb'){
		/**
		 * ***缩略图上传功能***
		 */
		$dest=uploadmulti('thumbpic',2);
		$_SESSION['thumb']=$dest[0];
		echo $dest[0];
	}
	else {
		/**
		 * ***判断是否评论*****
		 */
		$leaveword=$_POST['leaveword'];
		//$leaveword = 1;
		if ($leaveword) {
			$is_leaveWord = 1;
		} else {
			$is_leaveWord = 0;
		}
		
		/**
		 * ***判断是够点赞******
		 */
		$zan=$_POST ['zan'];
		//$zan = 1;
		if ($zan) {
			$is_zan = 1;
		} else {
			$is_zan = 0;
		}
		$thumb=$_POST['thumb'];
		 if(!empty($thumb)){
			$a= base64_decode(str_replace('data:image/png;base64,', '', $thumb)); 
			$thumb="../upload_thumb/".md5(uniqid(microtime(true),true)).".png";
			file_put_contents($thumb,$a);
			$_SESSION['thumb']=$thumb;
		 }
		/**
		 * ***构造wx_info数据库表的数据结构***
		 */
		 $data ['userId'] = $openid; // 用户ID
// 		$data ['userId'] = 1; // 用户ID
		$data ['thumb'] = $_SESSION['thumb']; // 图片地址
		$data['media'] = $_SESSION['upvideo'];
		$data ['moduleId'] = $moduleId; // 模块信息
		//$data ['moduleId'] = 1; // 模块信息
		$data ['date'] = date ( 'Y-m-d H-i-s', time () ); // 日期
		$data ['title'] = $title; // 标题
		$data ['content'] = $content; // 内容
		$data ['is_leaveWord'] = $is_leaveWord; // 是否评论
		$data ['is_zan'] = $is_zan; // 是否赞
		
		/**
		 * *****存入wx_info数据库***********
		 */
		$insert = $db->insert ( 'wx_info', $data ); // 插入语句
		
		if ( $insert ) {
			unset($_SESSION['thumb']);
			//$_SESSION['thumb'] = '';
			$error = 1; // 保存成功
		} else {
			$error = 0; // 保存失败
		}
	}
	
}
echo $error; 









