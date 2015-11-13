<?php
/*
 * 实现多个单文件上传
 */
header('content-type:text/html;charset=utf-8');
require_once 'upload.class.php';
/**
 * 构建上传文件信息
 * @return unknown
 */
function getFiles(){
	$i=0;
	foreach($_FILES as $file){
		if(is_string($file['name'])){
			$files[$i]=$file;
			$i++;
		}elseif(is_array($file['name'])){
			foreach($file['name'] as $key=>$val){
				$files[$i]['name']=$file['name'][$key];
				$files[$i]['type']=$file['type'][$key];
				$files[$i]['tmp_name']=$file['tmp_name'][$key];
				$files[$i]['error']=$file['error'][$key];
				$files[$i]['size']=$file['size'][$key];
				$i++;
			}
		}
	}
	return $files;

}
/**
 * 上传单个或多个文件
 * @param int $type
 * $type为0：上传图片
 * $type为1：上传视频
 * $type为2：上传缩略图
 */
function uploadmulti($fileName,$type){
// 	print_r($_FILES);die;
	$files=getFiles();
	if ($type==0){
		$uploadPath='../upload_image';
	}elseif ($type==1) {
		$uploadPath='../upload_video';
	}else {
		$uploadPath='../upload_thumb';
	}
	foreach($files as $fileInfo){
		$upload=new upload($fileName,$fileInfo,$uploadPath,false);
		$dest=$upload->uploadFile();
		$uploadFiles[]=$dest;
	}
	$uploadFiles=array_values(array_filter($uploadFiles));
	return $uploadFiles;
}
